<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cubby;
use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\OrderCubby;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Models\WorkshopDispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $orders = Order::with('customer')
            ->where(function($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%"));
            })
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'order_number', 'status', 'total', 'customer_id', 'created_at']);

        return response()->json($orders->map(fn($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'status'       => $o->status,
            'status_label' => str_replace('_', ' ', ucfirst($o->status)),
            'total'        => number_format($o->total, 2),
            'customer'     => $o->customer?->name ?? 'Walk-in',
            'url'          => route('orders.show', $o->id),
        ]));
    }

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q');

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop'])
            ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($cq) use ($q) {
                        $cq->where('name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        $orders = $query->get();
        $cubbies = Cubby::where('branch_id', $branchId ?? 1)->orderBy('name')->get();
        $services = ServiceType::orderBy('name')->get();
        $workshops = Branch::workshops()->get();

        return view('orders.index', compact('orders', 'cubbies', 'services', 'workshops'));
    }

    public function updateOrder(Request $request, Order $order)
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot edit an order that is already delivered or cancelled.']);
        }

        $cart = $request->cart;
        Log::info('Order Update Request Payload: ', $request->all());
        if (! $cart || ! is_array($cart) || count($cart) === 0) {
            return response()->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order, $cart) {
                // Delete old items
                OrderItem::where('order_id', $order->id)->delete();

                $subtotal = 0;
                $processedCart = [];

                // Process new items
                foreach ($cart as $item) {
                    $qty = (float) ($item['qty'] ?? 1);
                    if ($qty <= 0) {
                        continue;
                    }

                    $basePrice = (float) ($item['base_price'] ?? 0);
                    $itemTotal = $qty * $basePrice;
                    $subtotal += $itemTotal;

                    $processedCart[] = [
                        'service_type_id' => $item['service_type_id'] ?? null,
                        'item_description' => $item['name'] ?? 'Custom Service',
                        'quantity' => $qty,
                        'unit_price' => $basePrice,
                        'total_price' => $itemTotal,
                    ];
                }

                // Calculate discount and total
                $discountPercentage = max(0, min(100, (float) ($request->discount_percentage ?? 0)));
                $discountAmount = $subtotal * ($discountPercentage / 100);
                $total = $subtotal - $discountAmount;

                // Enforce minimum order value for deliveries
                $isDelivery = \App\Models\DeliveryAppointment::where('order_id', $order->id)->exists();
                if ($isDelivery && $total < 3000) {
                    $total = 3000;
                    // We can adjust subtotal or just keep total at 3000 and log it
                    // Or add a special item for the difference. Let's just adjust the total and clear discount.
                    $discountAmount = 0;
                }

                // Adjust paid_amount if it exceeds the new total (optional, but good practice)
                $paidAmount = $order->paid_amount;
                if ($paidAmount > $total) {
                    $paidAmount = $total; // Cap paid amount to new total to avoid negative balance
                }

                $order->update([
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'total' => $total,
                    'paid_amount' => $paidAmount,
                ]);

                // Insert new order items
                foreach ($processedCart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'service_type_id' => $item['service_type_id'],
                        'item_description' => $item['service_type_id'] ? null : $item['item_description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['total_price'],
                    ]);
                }

                // Log activity
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'edited',
                    'changed_by' => auth()->id(),
                    'note' => 'Order details updated',
                ]);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Order Update Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to update order.']);
        }
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.serviceType', 'statusHistory.changedBy', 'cubbies.cubby']);
        $branchId = auth()->user()->branch_id ?? 1;
        $cubbies = Cubby::where('branch_id', $branchId)->orderBy('name')->get();

        return view('orders.show', compact('order', 'cubbies'));
    }

    public function printBill(Order $order)
    {
        $order->load(['customer', 'items.serviceType']);
        $branch = Branch::find($order->branch_id);

        return view('orders.print', compact('order', 'branch'));
    }

    public function printChit(Order $order)
    {
        $order->load(['customer', 'items']);

        return view('orders.chit', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,cancelled',
            'cubby_id' => 'nullable|exists:cubbies,id',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order) {
                $order->status = $request->status;

                if ($request->status === 'done' && $request->cubby_id) {
                    // Assign cubby
                    OrderCubby::create([
                        'order_id' => $order->id,
                        'cubby_id' => $request->cubby_id,
                        'assigned_at' => now(),
                    ]);
                    Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);

                    // Queue WhatsApp msg
                    $customer = $order->customer;
                    $cubby = Cubby::find($request->cubby_id);
                    $msgContent = WhatsappTemplate::generateMessage('order_ready', $order, $customer, $cubby ? $cubby->name : '');

                    if (! empty($msgContent)) {
                        WhatsappLog::create([
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'phone' => $customer->phone,
                            'message_type' => 'order_ready',
                            'message' => $msgContent,
                            'status' => 'pending',
                        ]);
                    }
                }

                if ($request->status === 'done') {
                    // Complete Workshop Dispatch
                    $dispatch = WorkshopDispatch::where('order_id', $order->id)
                        ->whereNotIn('status', ['received_at_branch'])
                        ->first();
                    if ($dispatch) {
                        $dispatch->update([
                            'status' => 'received_at_branch',
                            'received_at_branch_at' => now(),
                        ]);
                    }
                }

                if ($request->status === 'done') {
                    $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt && $appt->status !== 'ready') {
                        $appt->update(['status' => 'ready']);
                    }
                }

                if ($request->status === 'delivered') {
                    $order->delivered_at = now();
                    if ($request->has('payment_method')) {
                        $order->payment_method = $request->payment_method;
                    }
                    if ($order->payment_method === 'credit') {
                        $order->payment_status = 'pending';
                    } else {
                        // Check if it's already paid, otherwise create payment transaction
                        $amountToPay = $order->total - $order->paid_amount;
                        if ($amountToPay > 0) {
                            PaymentTransaction::create([
                                'order_id' => $order->id,
                                'amount' => $amountToPay,
                                'payment_method' => $order->payment_method ?? 'cash',
                                'created_by' => auth()->id(),
                            ]);
                        }
                        $order->payment_status = 'paid';
                        $order->paid_amount = $order->total;
                    }

                    // Release cubby
                    $orderCubby = $order->cubbies()->whereNull('released_at')->first();
                    if ($orderCubby) {
                        $orderCubby->update(['released_at' => now()]);
                        Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                    }

                    // Queue WhatsApp msg
                    $customer = $order->customer;
                    $msgContent = WhatsappTemplate::generateMessage('order_delivered', $order, $customer);

                    if (! empty($msgContent)) {
                        WhatsappLog::create([
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'phone' => $customer->phone,
                            'message_type' => 'thank_you',
                            'message' => $msgContent,
                            'status' => 'pending',
                        ]);
                    }

                    $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt) {
                        $appt->update(['status' => 'delivered']);
                    }
                }

                $order->save();

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $request->status,
                    'changed_by' => auth()->id(),
                ]);
            });

            if ($request->status === 'delivered') {
                return redirect()->back()
                    ->with('success', 'Order delivered successfully!')
                    ->with('print_bill_id', $order->id);
            }

            return redirect()->back()->with('success', 'Status updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function reverseOrder(Request $request, Order $order)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        $authorized = false;
        foreach ($admins as $admin) {
            if (\Hash::check($request->password, $admin->password)) {
                $authorized = true;
                break;
            }
        }

        if (! $authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized password. Access denied.',
            ], 403);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->status = 'received';
            $order->payment_status = 'pending';
            $order->paid_amount = 0;

            $orderCubby = $order->cubbies()->whereNull('released_at')->first();
            if ($orderCubby) {
                $orderCubby->update(['released_at' => now()]);
                Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
            }

            $order->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'received',
                'changed_by' => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order reversed successfully.',
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,cancelled',
            'cubby_id' => 'nullable|exists:cubbies,id',
        ]);

        $stateOrder = [
            'received' => 1,
            'dispatched_to_workshop' => 2,
            'processing' => 3,
            'returned_from_workshop' => 4,
            'done' => 5,
            'delivered' => 6,
            'cancelled' => 7,
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $stateOrder) {
            foreach ($request->order_ids as $id) {
                $order = Order::with('customer')->find($id);

                if ($request->status !== 'cancelled') {
                    if (($stateOrder[$request->status] ?? 0) <= ($stateOrder[$order->status] ?? 0)) {
                        continue; // Skip backward or same state updates
                    }
                }

                $order->status = $request->status;

                if ($request->status === 'done' && $request->cubby_id) {
                    // Release old active cubby if any
                    $oldCubby = $order->cubbies()->whereNull('released_at')->first();
                    if ($oldCubby && $oldCubby->cubby_id != $request->cubby_id) {
                        $oldCubby->update(['released_at' => now()]);
                        Cubby::where('id', $oldCubby->cubby_id)->update(['status' => 'empty']);
                    }

                    if (! $oldCubby || $oldCubby->cubby_id != $request->cubby_id) {
                        OrderCubby::create([
                            'order_id' => $order->id,
                            'cubby_id' => $request->cubby_id,
                            'assigned_at' => now(),
                        ]);
                    }

                    Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);

                    // WhatsApp MSG
                    $customer = $order->customer;
                    $cubby = Cubby::find($request->cubby_id);
                    $msgContent = WhatsappTemplate::generateMessage('order_ready', $order, $customer, $cubby ? $cubby->name : '');

                    if (! empty($msgContent)) {
                        WhatsappLog::create([
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'phone' => $customer->phone,
                            'message_type' => 'order_ready',
                            'message' => $msgContent,
                            'status' => 'pending',
                        ]);
                    }

                    // Complete Workshop Dispatch
                    $dispatch = WorkshopDispatch::where('order_id', $order->id)
                        ->whereNotIn('status', ['received_at_branch'])
                        ->first();
                    if ($dispatch) {
                        $dispatch->update([
                            'status' => 'received_at_branch',
                            'received_at_branch_at' => now(),
                        ]);
                    }
                }

                if ($request->status === 'done') {
                    $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt && $appt->status !== 'ready') {
                        $appt->update(['status' => 'ready']);
                    }
                }

                if ($request->status === 'delivered') {
                    $order->delivered_at = now();
                    if ($order->payment_method === 'credit') {
                        $order->payment_status = 'pending';
                    } else {
                        $amountToPay = $order->total - $order->paid_amount;
                        if ($amountToPay > 0) {
                            PaymentTransaction::create([
                                'order_id' => $order->id,
                                'amount' => $amountToPay,
                                'payment_method' => $order->payment_method ?? 'cash',
                                'created_by' => auth()->id(),
                            ]);
                        }
                        $order->payment_status = 'paid';
                        $order->paid_amount = $order->total;
                    }

                    $orderCubby = $order->cubbies()->whereNull('released_at')->first();
                    if ($orderCubby) {
                        $orderCubby->update(['released_at' => now()]);
                        Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                    }

                    $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt) {
                        $appt->update(['status' => 'delivered']);
                    }
                }

                $order->save();

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $request->status,
                    'changed_by' => auth()->id(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Bulk status updated successfully.',
        ]);
    }

    public function collectCreditPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $order = Order::findOrFail($id);

        if ($order->payment_status === 'paid' || $order->total <= $order->paid_amount) {
            return redirect()->back()->with('error', 'Order is already fully paid.');
        }

        // Amount cannot be more than due
        $due = $order->total - $order->paid_amount;
        $payAmount = min($request->amount, $due);

        \DB::transaction(function () use ($order, $payAmount, $request) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $payAmount,
                'payment_method' => $request->payment_method,
                'created_by' => auth()->id(),
            ]);

            $order->paid_amount += $payAmount;

            // If fully paid, change method from credit and mark paid
            if ($order->paid_amount >= $order->total) {
                $order->payment_status = 'paid';
                $order->payment_method = $request->payment_method;
            }

            $order->save();
        });

        return redirect()->back()->with('success', 'Credit payment collected successfully!');
    }
}
