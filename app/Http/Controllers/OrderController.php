<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q');
        
        $query = \App\Models\Order::with(['customer', 'items.serviceType', 'cubby.cubby', 'workshop'])
                    ->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($q) {
            $query->where(function($qb) use ($q) {
                $qb->where('order_number', 'like', "%{$q}%")
                   ->orWhereHas('customer', function($cq) use ($q) {
                       $cq->where('name', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                   });
            });
        }

        $orders = $query->get();
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId ?? 1)->orderBy('name')->get();
        $services = \App\Models\ServiceType::orderBy('name')->get();
        $workshops = \App\Models\Branch::workshops()->get();

        return view('orders.index', compact('orders', 'cubbies', 'services', 'workshops'));
    }

    public function updateOrder(Request $request, \App\Models\Order $order)
    {
        if (in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json(['success' => false, 'message' => 'Cannot edit an order that is already delivered or cancelled.']);
        }

        $cart = $request->cart;
        \Illuminate\Support\Facades\Log::info('Order Update Request Payload: ', $request->all());
        if (!$cart || !is_array($cart) || count($cart) === 0) {
            return response()->json(['success' => false, 'message' => 'Cart cannot be empty.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order, $cart) {
                // Delete old items
                \App\Models\OrderItem::where('order_id', $order->id)->delete();

                $subtotal = 0;
                $processedCart = [];
                
                // Process new items
                foreach ($cart as $item) {
                    $qty = (float)($item['qty'] ?? 1);
                    if ($qty <= 0) continue;

                    $basePrice = (float)($item['base_price'] ?? 0);
                    $itemTotal = $qty * $basePrice;
                    $subtotal += $itemTotal;

                    $processedCart[] = [
                        'service_type_id'  => $item['service_type_id'] ?? null,
                        'item_description' => $item['name'] ?? 'Custom Service',
                        'quantity'         => $qty,
                        'unit_price'       => $basePrice,
                        'total_price'      => $itemTotal,
                    ];
                }

                // Calculate discount and total
                $discountPercentage = max(0, min(100, (float)($request->discount_percentage ?? 0)));
                $discountAmount = $subtotal * ($discountPercentage / 100);
                $total = $subtotal - $discountAmount;

                // Adjust paid_amount if it exceeds the new total (optional, but good practice)
                $paidAmount = $order->paid_amount;
                if ($paidAmount > $total) {
                    $paidAmount = $total; // Cap paid amount to new total to avoid negative balance
                }

                $order->update([
                    'subtotal'    => $subtotal,
                    'discount'    => $discountAmount,
                    'total'       => $total,
                    'paid_amount' => $paidAmount,
                ]);

                // Insert new order items
                foreach ($processedCart as $item) {
                    \App\Models\OrderItem::create([
                        'order_id'         => $order->id,
                        'service_type_id'  => $item['service_type_id'],
                        'item_description' => $item['service_type_id'] ? null : $item['item_description'],
                        'quantity'         => $item['quantity'],
                        'unit_price'       => $item['unit_price'],
                        'total_price'      => $item['total_price'],
                    ]);
                }

                // Log activity
                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'edited',
                    'changed_by' => auth()->id(),
                    'note'       => 'Order details updated'
                ]);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Order Update Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update order.']);
        }
    }

    public function show(\App\Models\Order $order)
    {
        $order->load(['customer', 'items.serviceType', 'statusHistory.changedBy', 'cubbies.cubby']);
        $branchId = auth()->user()->branch_id ?? 1;
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->orderBy('name')->get();
        return view('orders.show', compact('order', 'cubbies'));
    }

    public function printBill(\App\Models\Order $order)
    {
        $order->load(['customer', 'items.serviceType']);
        $branch = \App\Models\Branch::find($order->branch_id);
        return view('orders.print', compact('order', 'branch'));
    }

    public function printChit(\App\Models\Order $order)
    {
        $order->load(['customer', 'items']);
        return view('orders.chit', compact('order'));
    }
    public function updateStatus(Request $request, \App\Models\Order $order)
    {
        $request->validate([
            'status' => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,cancelled',
            'cubby_id' => 'nullable|exists:cubbies,id'
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $order) {
                $order->status = $request->status;

                if ($request->status === 'done' && $request->cubby_id) {
                    // Assign cubby
                    \App\Models\OrderCubby::create([
                        'order_id' => $order->id,
                        'cubby_id' => $request->cubby_id,
                        'assigned_at' => now(),
                    ]);
                    \App\Models\Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);
                    
                    // Queue WhatsApp msg
                    $customer = $order->customer;
                    $cubby = \App\Models\Cubby::find($request->cubby_id);
                    $msgContent = \App\Models\WhatsappTemplate::generateMessage('order_ready', $order, $customer, $cubby ? $cubby->name : '');
                    
                    if (!empty($msgContent)) {
                        \App\Models\WhatsappLog::create([
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
                    $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
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
                    $appt = \App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
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
                            \App\Models\PaymentTransaction::create([
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
                        \App\Models\Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                    }
                    
                    // Queue WhatsApp msg
                    $customer = $order->customer;
                    $msgContent = \App\Models\WhatsappTemplate::generateMessage('order_delivered', $order, $customer);
                    
                    if (!empty($msgContent)) {
                        \App\Models\WhatsappLog::create([
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'phone' => $customer->phone,
                            'message_type' => 'thank_you',
                            'message' => $msgContent,
                            'status' => 'pending',
                        ]);
                    }
                    
                    $appt = \App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt) {
                        $appt->update(['status' => 'delivered']);
                    }
                }

                $order->save();

                \App\Models\OrderStatusHistory::create([
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
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function reverseOrder(Request $request, \App\Models\Order $order)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
        $authorized = false;
        foreach ($admins as $admin) {
            if (\Hash::check($request->password, $admin->password)) {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized password. Access denied.'
            ], 403);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order->status = 'received';
            $order->payment_status = 'pending';
            $order->paid_amount = 0;
            
            $orderCubby = $order->cubbies()->whereNull('released_at')->first();
            if ($orderCubby) {
                $orderCubby->update(['released_at' => now()]);
                \App\Models\Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
            }

            $order->save();

            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'received',
                'changed_by' => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order reversed successfully.'
        ]);
    }

    public function bulkStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,cancelled',
            'cubby_id' => 'nullable|exists:cubbies,id'
        ]);

        $stateOrder = [
            'received' => 1, 
            'dispatched_to_workshop' => 2, 
            'processing' => 3, 
            'returned_from_workshop' => 4, 
            'done' => 5, 
            'delivered' => 6, 
            'cancelled' => 7
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $stateOrder) {
            foreach ($request->order_ids as $id) {
                $order = \App\Models\Order::with('customer')->find($id);
                
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
                        \App\Models\Cubby::where('id', $oldCubby->cubby_id)->update(['status' => 'empty']);
                    }

                    if (!$oldCubby || $oldCubby->cubby_id != $request->cubby_id) {
                        \App\Models\OrderCubby::create([
                            'order_id' => $order->id,
                            'cubby_id' => $request->cubby_id,
                            'assigned_at' => now(),
                        ]);
                    }
                    
                    \App\Models\Cubby::where('id', $request->cubby_id)->update(['status' => 'occupied']);
                    
                    // WhatsApp MSG
                    $customer = $order->customer;
                    $cubby = \App\Models\Cubby::find($request->cubby_id);
                    $msgContent = \App\Models\WhatsappTemplate::generateMessage('order_ready', $order, $customer, $cubby ? $cubby->name : '');
                    
                    if (!empty($msgContent)) {
                        \App\Models\WhatsappLog::create([
                            'order_id' => $order->id,
                            'customer_id' => $customer->id,
                            'phone' => $customer->phone,
                            'message_type' => 'order_ready',
                            'message' => $msgContent,
                            'status' => 'pending',
                        ]);
                    }
                    
                    // Complete Workshop Dispatch
                    $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)
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
                    $appt = \App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
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
                            \App\Models\PaymentTransaction::create([
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
                        \App\Models\Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                    }
                    
                    $appt = \App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
                    if ($appt) {
                        $appt->update(['status' => 'delivered']);
                    }
                }
                
                $order->save();

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $request->status,
                    'changed_by' => auth()->id(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Bulk status updated successfully.'
        ]);
    }

    public function collectCreditPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card',
            'amount' => 'required|numeric|min:0.01'
        ]);

        $order = \App\Models\Order::findOrFail($id);

        if ($order->payment_status === 'paid' || $order->total <= $order->paid_amount) {
            return redirect()->back()->with('error', 'Order is already fully paid.');
        }

        // Amount cannot be more than due
        $due = $order->total - $order->paid_amount;
        $payAmount = min($request->amount, $due);

        \DB::transaction(function () use ($order, $payAmount, $request) {
            \App\Models\PaymentTransaction::create([
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
