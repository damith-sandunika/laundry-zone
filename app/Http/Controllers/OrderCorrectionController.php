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

        if (isset($request->new_data['payment_method'])) {
            $transactionId = $request->new_data['transaction_id'] ?? null;
            if ($transactionId) {
                $paymentTransaction = \App\Models\PaymentTransaction::find($transactionId);
                // Only allow changes to same-day transactions (or handle it properly)
                if ($paymentTransaction && $paymentTransaction->payment_method !== $request->new_data['payment_method'] && $paymentTransaction->created_at->format('Y-m-d') !== now()->format('Y-m-d')) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Cannot change payment methods of previous days because accounts are already balanced.'
                    ]);
                }
            } else if ($request->new_data['payment_method'] !== $order->payment_method) {
                $paymentTransaction = \App\Models\PaymentTransaction::where('order_id', $order->id)->latest()->first();
                if ($paymentTransaction && $paymentTransaction->created_at->format('Y-m-d') !== now()->format('Y-m-d')) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Cannot change payment methods of previous days because accounts are already balanced.'
                    ]);
                }
            }
        }

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
            $order->load('items.extras');
            $oldData = [
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'delivery_fee' => $order->delivery_fee,
                'discount_percentage' => $order->discount,
                'items' => $order->items->toArray(),
            ];
        } else if ($request->type === 'order_change') {
            $order->load('items.extras');
            $oldData = [
                'payment_method' => $order->payment_method,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'delivery_fee' => $order->delivery_fee,
                'discount_percentage' => $order->discount,
                'items' => $order->items->toArray(),
            ];
        }

        $newData = $request->new_data;
        if (in_array($request->type, ['items_change', 'order_change'])) {
            $subtotal = 0;
            if (isset($newData['cart']) && is_array($newData['cart'])) {
                $calcResult = app(\App\Actions\Order\CalculateOrderTotalAction::class)->execute($newData['cart']);
                $subtotal = $calcResult['subtotal'];
            }
            $discountPercentage = isset($newData['discount_percentage']) ? (float) $newData['discount_percentage'] : ($order->customer ? $order->customer->default_discount : 0);
            $discountAmount = $subtotal * ($discountPercentage / 100);
            $deliveryFee = isset($newData['delivery_fee']) ? (float) $newData['delivery_fee'] : $order->delivery_fee;
            
            $newData['calculated_total'] = $subtotal - $discountAmount + $deliveryFee;
        }

        $user = auth()->user();
        $isAdmin = in_array($user->role, ['branch_admin', 'workshop_admin', 'super_admin']);

        try {
            DB::transaction(function () use ($order, $request, $oldData, $newData, $user, $isAdmin) {
                $correction = OrderCorrectionRequest::create([
                    'order_id' => $order->id,
                    'requested_by' => auth()->id(),
                    'status' => 'pending',
                    'type' => $request->type,
                    'reason' => $request->reason,
                    'old_data' => $oldData,
                    'new_data' => $newData,
                ]);

                if ($isAdmin) {
                    $this->approve($correction);
                    
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'changed_by' => $user->id,
                        'note' => "Correction auto-applied by Admin ({$request->type}): " . $request->reason,
                    ]);
                } else {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'changed_by' => auth()->id(),
                        'note' => "Correction requested ({$request->type}): " . $request->reason,
                    ]);
                }
            });

            if ($isAdmin) {
                return response()->json(['success' => true, 'message' => 'Order updated successfully.']);
            }

            // Notify admins
            $admins = User::whereIn('role', ['branch_admin', 'workshop_admin'])
                ->where(function($q) use ($order) {
                    $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id');
                })->get();
            Notification::send($admins, new SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

            return response()->json(['success' => true, 'message' => 'Correction request submitted. Waiting for admin approval.']);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order Correction Store Error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to submit correction request. Please try again.']);
        }
    }

    public function approve(OrderCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return back()->with('error', 'Request is not pending.');
        }

        try {
            DB::transaction(function () use ($correction) {
                $order = $correction->order ?? \App\Models\Order::withoutGlobalScopes()->find($correction->order_id);
                $newData = $correction->new_data;

                if ($correction->type === 'payment_change') {
                    $correctionNote = app(\App\Actions\Order\UpdateOrderPaymentAction::class)->execute($order, $newData);
                } else if ($correction->type === 'items_change' || $correction->type === 'order_change') {
                    $correctionNote = app(\App\Actions\Order\UpdateOrderItemsAction::class)->execute($order, $newData, $correction->type);
                } else if ($correction->type === 'convert_to_delivery') {
                    $correctionNote = app(\App\Actions\Order\ConvertOrderToDeliveryAction::class)->execute($order, $newData);
                } else if ($correction->type === 'convert_to_pickup') {
                    $correctionNote = app(\App\Actions\Order\ConvertOrderToPickupAction::class)->execute($order, $newData);
                } else if ($correction->type === 'overpayment_handling') {
                    $correctionNote = app(\App\Actions\Order\HandleOverpaymentAction::class)->execute($order, $newData);
                }

                $correction->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'resolved_at' => now(),
                ]);

                $requesterName = $correction->requestedBy->name ?? 'System';
                $approverName = auth()->user()->name ?? 'System';
                $reqDate = \Carbon\Carbon::parse($correction->created_at)->format('d M Y, h:i A');
                $appDate = now()->format('d M Y, h:i A');
                $finalNote = ($correctionNote ?? "Correction approved ({$correction->type}).") . "\n[Requested by: {$requesterName} at {$reqDate} | Approved by: {$approverName} at {$appDate}]";

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'changed_by' => auth()->id(),
                    'note' => $finalNote,
                ]);
            });

            // Notify requester
            if ($correction->requestedBy) {
                $notificationOrder = $correction->order ?? \App\Models\Order::withoutGlobalScopes()->find($correction->order_id);
                if ($notificationOrder) {
                    Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Approved for Order #{$notificationOrder->order_number}", route('orders.show', $notificationOrder->order_number), 'success'));
                }
            }

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Correction approved successfully.']);
            }
            return back()->with('success', 'Correction approved successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order Correction Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Something went wrong while processing the order. Please try again.']);
            }
            return back()->with('error', 'Something went wrong while processing the order. Please try again.');
        }
    }

    public function reject(OrderCorrectionRequest $correction)
    {
        if ($correction->status !== 'pending') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Request is not pending.']);
            }
            return back()->with('error', 'Request is not pending.');
        }

        try {
            DB::transaction(function () use ($correction) {
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
            });

            if ($correction->requestedBy) {
                Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Rejected for Order #{$correction->order->order_number}", route('orders.show', $correction->order->order_number), 'error'));
            }

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Correction rejected.']);
            }
            return back()->with('success', 'Correction rejected.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Order Correction Reject Error: ' . $e->getMessage(), [
                'correction_id' => $correction->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to reject correction request. Please try again.']);
            }
            return back()->with('error', 'Failed to reject correction request. Please try again.');
        }
    }
}

