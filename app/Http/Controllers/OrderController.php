<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $q = $request->input('q');
        
        $query = \App\Models\Order::with(['customer', 'items', 'cubby.cubby'])
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
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId ?? 1)->where('status', 'empty')->get();

        return view('orders.index', compact('orders', 'cubbies'));
    }

    public function show(\App\Models\Order $order)
    {
        $order->load(['customer', 'items.serviceType', 'statusHistory.changedBy', 'cubbies.cubby']);
        $branchId = auth()->user()->branch_id ?? 1;
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->where('status', 'empty')->get();
        return view('orders.show', compact('order', 'cubbies'));
    }

    public function printBill(\App\Models\Order $order)
    {
        $order->load(['customer', 'items.serviceType']);
        $branch = \App\Models\Branch::find($order->branch_id);
        return view('orders.print', compact('order', 'branch'));
    }
    public function updateStatus(Request $request, \App\Models\Order $order)
    {
        $request->validate([
            'status' => 'required|in:received,washing,ironing,done,delivered,cancelled',
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

                if ($request->status === 'delivered') {
                    $order->delivered_at = now();
                    $order->payment_status = 'paid';
                    $order->paid_amount = $order->total;
                    if ($request->has('payment_method')) {
                        $order->payment_method = $request->payment_method;
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
                }

                $order->save();

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $request->status,
                    'changed_by' => auth()->id(),
                ]);
            });

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
            'status' => 'required|in:received,done,delivered,cancelled',
        ]);

        foreach ($request->order_ids as $id) {
            $order = \App\Models\Order::find($id);
            $order->status = $request->status;
            
            if ($request->status === 'delivered') {
                $order->delivered_at = now();
                $order->payment_status = 'paid';
                $order->paid_amount = $order->total;

                $orderCubby = $order->cubbies()->whereNull('released_at')->first();
                if ($orderCubby) {
                    $orderCubby->update(['released_at' => now()]);
                    \App\Models\Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                }
            }
            
            $order->save();

            \App\Models\OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $request->status,
                'changed_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk status updated successfully.'
        ]);
    }
}
