<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\Notification;

class OrderCorrectionController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'type' => 'required|in:payment_change,items_change,order_change',
            'reason' => 'required|string|max:500',
            'new_data' => 'required|array',
        ]);

        if ($order->correctionRequests()->where('status', 'pending')->exists()) {
            return response()->json(['success' => false, 'message' => 'An active correction request already exists for this order.']);
        }

        $oldData = [];
        if ($request->type === 'payment_change') {
            $oldData = [
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'paid_amount' => $order->paid_amount,
            ];
        } else if ($request->type === 'items_change') {
            $oldData = [
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'items' => $order->items->toArray(),
            ];
        } else if ($request->type === 'order_change') {
            $oldData = [
                'payment_method' => $order->payment_method,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'items' => $order->items->toArray(),
            ];
        }

        $newData = $request->new_data;
        if (in_array($request->type, ['items_change', 'order_change'])) {
            $subtotal = 0;
            if (isset($newData['cart']) && is_array($newData['cart'])) {
                foreach ($newData['cart'] as $item) {
                    $qty = (float) ($item['qty'] ?? 1);
                    $basePrice = (float) ($item['base_price'] ?? 0);
                    $subtotal += $qty * $basePrice;
                }
            }
            $discountPercentage = isset($newData['discount_percentage']) ? (float) $newData['discount_percentage'] : ($order->customer ? $order->customer->default_discount : 0);
            $discountAmount = $subtotal * ($discountPercentage / 100);
            $deliveryFee = isset($newData['delivery_fee']) ? (float) $newData['delivery_fee'] : $order->delivery_fee;
            
            $newData['calculated_total'] = $subtotal - $discountAmount + $deliveryFee;
        }

        $correction = OrderCorrectionRequest::create([
            'order_id' => $order->id,
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'type' => $request->type,
            'reason' => $request->reason,
            'old_data' => $oldData,
            'new_data' => $newData,
        ]);

        $user = auth()->user();
        $isAdmin = in_array($user->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']);

        if ($isAdmin) {
            // Auto approve if requested by an admin
            $this->approve($correction);
            
            // The note is added inside approve(), but we can add a specific note that it was auto-approved
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => $user->id,
                'note' => "Correction auto-applied by Admin ({$request->type}): " . $request->reason,
            ]);

            return response()->json(['success' => true, 'message' => 'Correction applied successfully.']);
        }

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'changed_by' => auth()->id(),
            'note' => "Correction requested ({$request->type}): " . $request->reason,
        ]);

        // Notify admins
        $admins = User::whereIn('role', ['admin', 'branch_admin'])->where('branch_id', $order->branch_id)->get();
        Notification::send($admins, new SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

        return response()->json(['success' => true, 'message' => 'Correction request submitted. Waiting for admin approval.']);
    }

    public function approve(OrderCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return back()->with('error', 'Request is not pending.');
        }

        DB::transaction(function () use ($correction) {
            $order = $correction->order;
            $newData = $correction->new_data;

            if ($correction->type === 'payment_change') {
                $newMethod = $newData['payment_method'];
                $oldMethod = $order->payment_method;

                $order->update([
                    'payment_method' => $newMethod,
                ]);

                // Update payment transactions
                PaymentTransaction::where('order_id', $order->id)
                    ->where('payment_method', $oldMethod)
                    ->update(['payment_method' => $newMethod]);

            } else if ($correction->type === 'items_change' || $correction->type === 'order_change') {
                if ($correction->type === 'order_change' && isset($newData['payment_method'])) {
                    $newMethod = $newData['payment_method'];
                    $oldMethod = $order->payment_method;

                    $order->update([
                        'payment_method' => $newMethod,
                    ]);

                    PaymentTransaction::where('order_id', $order->id)
                        ->where('payment_method', $oldMethod)
                        ->update(['payment_method' => $newMethod]);
                }

                // Similar to updateOrder logic
                OrderItem::where('order_id', $order->id)->delete();
                $subtotal = 0;
                
                foreach ($newData['cart'] as $item) {
                    $qty = (float) ($item['qty'] ?? 1);
                    $basePrice = (float) ($item['base_price'] ?? 0);
                    $itemTotal = $qty * $basePrice;
                    $subtotal += $itemTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'service_type_id' => $item['service_type_id'] ?? null,
                        'item_description' => isset($item['service_type_id']) ? null : ($item['name'] ?? 'Custom Service'),
                        'quantity' => $qty,
                        'unit_price' => $basePrice,
                        'total_price' => $itemTotal,
                    ]);
                }

                $discountPercentage = $order->customer ? $order->customer->default_discount : 0;
                $discountAmount = $subtotal * ($discountPercentage / 100);
                $total = $subtotal - $discountAmount + $order->delivery_fee;
                
                $paymentStatus = $order->paid_amount >= $total ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');

                $order->update([
                    'subtotal' => $subtotal,
                    'discount' => $discountAmount,
                    'total' => $total,
                    'payment_status' => $paymentStatus,
                ]);
            }

            $correction->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => auth()->id(),
                'note' => "Correction approved ({$correction->type}).",
            ]);
        });

        // Notify requester
        if ($correction->requestedBy) {
            Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Approved for Order #{$correction->order->order_number}", route('orders.show', $correction->order->order_number), 'success'));
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Correction approved successfully.']);
        }
        return back()->with('success', 'Correction approved successfully.');
    }

    public function reject(OrderCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return back()->with('error', 'Request is not pending.');
        }

        $correction->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        OrderStatusHistory::create([
            'order_id' => $correction->order_id,
            'status' => $correction->order->status,
            'changed_by' => auth()->id(),
            'note' => "Correction rejected ({$correction->type}).",
        ]);

        if ($correction->requestedBy) {
            Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Rejected for Order #{$correction->order->order_number}", route('orders.show', $correction->order->order_number), 'error'));
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Correction rejected.']);
        }
        return back()->with('success', 'Correction rejected.');
    }
}
