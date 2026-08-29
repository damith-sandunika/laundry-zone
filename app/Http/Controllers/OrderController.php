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
use App\Services\CommunicationService;
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
            'url'          => route('orders.show', $o->order_number),
        ]));
    }

    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q');
        $status = $request->input('status');
        $isOnline = $request->input('is_online');
        $paymentReady = $request->input('payment_ready');

        $query = Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop', 'deliveryAppointment', 'paymentTransactions'])
            ->orderBy('created_at', 'desc');

        if ($branchId && auth()->user()->role !== 'super_admin') {
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhere('workshop_id', $branchId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($isOnline) {
            $query->where('is_online_booking', true);
        }

        if ($paymentReady) {
            $query->where(function($q) {
                $q->where('payment_approved', true)
                  ->orWhereRaw('paid_amount >= requested_advance AND requested_advance > 0');
            });
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

        $orders = $query->get()->map(function($order) {
            // Attach the latest workshop dispatch ID so the branch can confirm receipt
            $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
                ->whereIn('status', ['returned_to_branch', 'received_at_branch'])
                ->orderByDesc('returned_at')
                ->first();
            $order->workshop_dispatch_id = $dispatch ? $dispatch->id : null;
            return $order;
        });
        $cubbies = Cubby::where('branch_id', $branchId ?? 1)->orderBy('name')->get();
        $categories = \App\Models\ServiceCategory::with(['services' => function($q) use ($branchId) {
            $q->where('branch_id', $branchId ?? 1);
        }])->where('is_active', true)->where('branch_id', $branchId ?? 1)->get();
        $workshops = Branch::workshops()->get();
        $riders = \App\Models\User::where('role', 'rider')->where('is_active', true)->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        return view('orders.index', compact('orders', 'cubbies', 'categories', 'workshops', 'riders', 'minimumDeliveryCost'));
    }
    public function requestPayment(Order $order, Request $request)
    {
        $amountType = $request->input('amount_type', 'full');
        $amount = 0;

        if ($amountType === 'full') {
            $amount = $order->total;
        } elseif ($amountType === '50') {
            $amount = $order->total / 2;
        } else {
            $amount = $request->input('custom_amount', $order->total);
        }

        $customer = $order->customer;
        if ($customer) {
            $paymentLink = url("/orders/{$order->id}/pay?amount={$amount}");
            
            // Sending manual payment request using generic method or standard approach?
            // Actually they wanted a "bill finalize" message!
            $commService = new \App\Services\CommunicationService();
            $commService->sendFinalizedBill($order);
            
            return response()->json(['success' => true, 'message' => 'Payment request sent.']);
        }

        return response()->json(['success' => false, 'message' => 'Customer has no phone number.'], 400);
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
                
                // Update customer default_discount if discount > 0
                if ($discountPercentage > 0 && $order->customer_id) {
                    \App\Models\Customer::where('id', $order->customer_id)
                        ->update(['default_discount' => $discountPercentage]);
                }

                $total = $subtotal - $discountAmount + $order->delivery_fee;

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
                    'requested_advance' => $request->requested_advance,
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

                // Handle next action
                if ($request->next_action === 'start_processing') {
                    $order->update(['status' => 'processing']);
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'processing',
                        'changed_by' => auth()->id(),
                        'note' => 'Workshop started processing',
                    ]);
                } else if ($request->next_action === 'dispatch_to_workshop') {
                    $order->update(['status' => 'dispatched_to_workshop']);
                    
                    $workshop = \App\Models\Branch::workshops()->first();
                    if ($workshop) {
                        \App\Models\WorkshopDispatch::create([
                            'order_id' => $order->id,
                            'branch_id' => $order->branch_id,
                            'workshop_branch_id' => $workshop->id,
                            'status' => 'dispatched_to_workshop',
                            'dispatched_by' => auth()->id(),
                            'dispatch_notes' => 'Dispatched from branch after confirmation',
                        ]);
                    }

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'dispatched_to_workshop',
                        'changed_by' => auth()->id(),
                        'note' => 'Branch verified and dispatched order to workshop',
                    ]);
                } else if ($request->next_action === 'request_payment') {
                    // Send payment request / bill finalize
                    $commService = new \App\Services\CommunicationService();
                    $commService->sendFinalizedBill($order);
                }

                // Handle advance cash payment if provided
                if ($request->filled('advance_cash_amount') && $request->advance_cash_amount > 0) {
                    $advance = (float) $request->advance_cash_amount;
                    \App\Models\PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $advance,
                        'payment_method' => 'cash',
                        'status' => 'completed',
                        'collected_by' => auth()->id(),
                    ]);
                    $order->increment('paid_amount', $advance);
                    
                    if ($order->paid_amount >= $order->total) {
                        $order->update(['payment_status' => 'paid']);
                    } else {
                        $order->update(['payment_status' => 'partial']);
                    }
                }
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Order Update Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Failed to update order.']);
        }
    }

    public function showByNumber($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $order->load(['customer', 'items.serviceType', 'statusHistory.changedBy', 'cubbies.cubby', 'createdBy', 'deliveryAppointment']);
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

    public function invoice(Order $order)
    {
        $order->load(['customer', 'items.serviceType', 'branch']);

        return view('orders.invoice', compact('order'));
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

                if ($request->status === 'done') {
                    if ($request->cubby_id) {
                        // Assign cubby
                        OrderCubby::create([
                            'order_id' => $order->id,
                            'cubby_id' => $request->cubby_id,
                            'assigned_at' => now(),
                        ]);
                        Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);
                    }

                    // Queue notification
                    $commService = new \App\Services\CommunicationService();
                    $commService->sendOrderReady($order);
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

                    // Queue notification - handled in after-transaction block

                    $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt) {
                        $appt->update(['status' => 'delivered']);
                    }
                }

                if ($request->status === 'completed') {
                    if ($request->has('payment_method')) {
                        $order->payment_method = $request->payment_method;
                    }

                    // If not fully paid, settle the balance
                    $amountToPay = $order->total - $order->paid_amount;
                    if ($amountToPay > 0) {
                        PaymentTransaction::create([
                            'order_id' => $order->id,
                            'amount' => $amountToPay,
                            'payment_method' => $order->payment_method ?? 'cash',
                            'created_by' => auth()->id(),
                            'collected_from_rider_at' => now(),
                            'status' => 'completed',
                            'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        ]);
                        $order->paid_amount = $order->total;
                        $order->payment_status = 'paid';
                    } else {
                        // Mark previously uncollected transactions from rider as collected
                        PaymentTransaction::where('order_id', $order->id)
                            ->where('payment_method', 'cash')
                            ->whereNull('collected_from_rider_at')
                            ->update(['collected_from_rider_at' => now()]);
                    }
                }

                // Log the status change so timeline shows correct user
                if (in_array($request->status, ['delivered', 'completed', 'done'])) {
                    \App\Models\OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $request->status,
                        'changed_by' => auth()->id(),
                        'note' => 'Status updated to ' . $request->status,
                    ]);
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

                if ($request->status === 'done') {
                    if ($request->cubby_id) {
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

        // Status-specific notifications - sent ONCE after transaction succeeds
        $commService = new \App\Services\CommunicationService();
        $orders = Order::whereIn('id', $request->order_ids)->get();
        foreach ($orders as $order) {
            if ($request->status === 'done') {
                $commService->sendOrderReady($order);
            } elseif ($request->status === 'delivered') {
                $commService->sendOrderDelivered($order);
            } elseif ($request->status === 'dispatched_to_workshop') {
                $commService->sendOrderDispatchedToWorkshop($order);
            } elseif ($request->status === 'processing') {
                $commService->sendOrderProcessing($order);
            } elseif ($request->status === 'returned_from_workshop') {
                $commService->sendOrderReturnedFromWorkshop($order);
            } elseif ($request->status === 'cancelled') {
                $commService->sendOrderCancelled($order);
            }
        }

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

    public function approvePayment(Request $request, Order $order, PaymentTransaction $transaction)
    {
        if ($transaction->order_id !== $order->id) {
            abort(403);
        }

        \DB::transaction(function () use ($order, $transaction) {
            $transaction->update([
                'status' => 'completed',
            ]);

            $order->paid_amount += $transaction->amount;

            if ($order->paid_amount >= $order->total && $order->total > 0) {
                $order->payment_status = 'paid';
            } elseif ($order->paid_amount > 0) {
                $order->payment_status = 'partial';
            }

            $order->save();
        });

        return redirect()->back()->with('success', 'Payment slip approved successfully.');
    }

    public function rejectPayment(Request $request, Order $order, PaymentTransaction $transaction)
    {
        if ($transaction->order_id !== $order->id) {
            abort(403);
        }

        $request->validate([
            'reject_reason' => 'required|string|max:255',
        ]);

        $transaction->update([
            'status' => 'rejected',
            'reject_reason' => $request->reject_reason,
        ]);

        return redirect()->back()->with('success', 'Payment slip rejected successfully.');
    }

    public function dispatchDelivery(Request $request, Order $order)
    {
        $request->validate([
            'delivery_method' => 'required|in:rider,pickme_flash',
            'rider_id' => 'required_if:delivery_method,rider|nullable|exists:users,id',
            'pickme_cost' => 'required_if:delivery_method,pickme_flash|nullable|numeric|min:0',
        ]);

        $appointment = $order->deliveryAppointment;
        $branchId = auth()->user()->branch_id ?? 1;

        if ($request->delivery_method === 'rider') {
            \App\Models\DeliveryTask::create([
                'delivery_appointment_id' => $appointment ? $appointment->id : null,
                'order_id' => $order->id,
                'rider_id' => $request->rider_id,
                'branch_id' => $branchId,
                'task_type' => 'delivery',
                'status' => 'assigned',
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'out_for_delivery']);
            }
            $order->update(['status' => 'out_for_delivery']);

            $riderUser = \App\Models\User::find($request->rider_id);
            if ($riderUser) {
                \Illuminate\Support\Facades\Notification::send($riderUser, new \App\Notifications\SystemAlertNotification('New Delivery Task: Order #' . $order->order_number, route('rider.tasks')));
            }

            return response()->json(['success' => true, 'status' => 'dispatched_for_delivery']);

        } else {
            // PickMe
            if ($order->paid_amount < $order->total) {
                return response()->json(['success' => false, 'message' => 'PickMe cannot be used for unpaid or partially paid orders. Customer must pay in full first.'], 403);
            }

            \App\Models\Expense::create([
                'branch_id' => $branchId,
                'amount' => $request->pickme_cost,
                'category' => 'PickMe',
                'description' => 'PickMe Delivery for Order #' . $order->order_number,
                'expense_date' => now(),
                'created_by' => auth()->id(),
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'delivered']);
            }
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            
            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'delivered',
                'changed_by' => auth()->id(),
                'note' => 'Delivered via PickMe',
            ]);

            return response()->json(['success' => true, 'status' => 'completed']);
        }
    }
}
