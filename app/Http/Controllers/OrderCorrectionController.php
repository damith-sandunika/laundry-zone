<?php

namespace App\Http\Controllers;

use App\Actions\Order\ApproveOrderCorrectionAction;
use App\Actions\Order\CalculateOrderTotalAction;
use App\Actions\Order\RejectOrderCorrectionAction;
use App\Actions\Order\SubmitOrderCorrectionAction;
use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderCorrectionController extends Controller
{
    public function store(Request $request, Order $order, SubmitOrderCorrectionAction $action)
    {
        $request->validate([
            'type' => 'required|in:payment_change,items_change,order_change',
            'reason' => 'required|string|max:500',
            'new_data' => 'required|array',
        ]);

        if ($request->type === 'payment_change') {
            $txId = $request->new_data['transaction_id'] ?? null;
            if ($txId) {
                $tx = PaymentTransaction::withTrashed()->find($txId);
                if (!$tx) return response()->json(['success' => false, 'message' => 'Payment transaction not found.']);
                if ($tx->created_at->format('Y-m-d') !== now()->format('Y-m-d')) {
                    return response()->json(['success' => false, 'message' => 'Cannot edit or delete payments of previous days because accounts are already balanced.']);
                }
            } elseif (isset($request->new_data['payment_method']) && $request->new_data['payment_method'] !== $order->payment_method) {
                $tx = PaymentTransaction::where('order_id', $order->id)->latest()->first();
                if ($tx && $tx->created_at->format('Y-m-d') !== now()->format('Y-m-d')) {
                    return response()->json(['success' => false, 'message' => 'Cannot edit payments of previous days because accounts are already balanced.']);
                }
            }
        }

        if ($order->correctionRequests()->where('status', 'pending')->exists()) {
            return response()->json(['success' => false, 'message' => 'An active correction request already exists for this order.']);
        }

        $oldData = $this->extractOldData($order, $request->type, $request->new_data['transaction_id'] ?? null);
        $newData = $request->new_data;

        if (in_array($request->type, ['items_change', 'order_change'])) {
            $subtotal = 0;
            if (isset($newData['cart']) && is_array($newData['cart'])) {
                $calc = app(CalculateOrderTotalAction::class)->execute($newData['cart']);
                $subtotal = $calc['subtotal'];
            }
            if (isset($newData['discount_type']) && $newData['discount_type'] === 'fixed') {
                $discountAmount = max(0, min($subtotal, (float) ($newData['discount_amount'] ?? 0)));
            } else {
                $discountPercentage = isset($newData['discount_percentage']) ? (float) $newData['discount_percentage'] : ($order->customer ? $order->customer->default_discount : 0);
                $discountAmount = $subtotal * ($discountPercentage / 100);
            }
            $deliveryFee = isset($newData['delivery_fee']) ? (float) $newData['delivery_fee'] : $order->delivery_fee;
            $newData['calculated_total'] = $subtotal - $discountAmount + $deliveryFee;
        }

        $user = auth()->user();
        $isAdmin = in_array($user->role, ['branch_admin', 'workshop_admin', 'super_admin']);

        try {
            $res = $action->execute($order, $request->type, $request->reason, $oldData, $newData, $user, $isAdmin);
            $msg = $res['is_admin'] ? 'Order updated successfully.' : 'Correction request submitted. Waiting for admin approval.';
            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            Log::error('Order Correction Store Error: ' . $e->getMessage(), ['order_id' => $order->id]);
            return response()->json(['success' => false, 'message' => 'Failed to submit correction request. Please try again.']);
        }
    }

    public function approve(OrderCorrectionRequest $correction, ApproveOrderCorrectionAction $action)
    {
        if ($correction->status !== 'pending') {
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => 'Request is not pending.']) : back()->with('error', 'Request is not pending.');
        }

        try {
            $action->execute($correction, auth()->user());
            return request()->wantsJson() ? response()->json(['success' => true, 'message' => 'Correction approved successfully.']) : back()->with('success', 'Correction approved successfully.');
        } catch (\Exception $e) {
            Log::error('Order Correction Error: ' . $e->getMessage());
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => 'Something went wrong while processing.']) : back()->with('error', 'Something went wrong while processing.');
        }
    }

    public function reject(OrderCorrectionRequest $correction, RejectOrderCorrectionAction $action)
    {
        if ($correction->status !== 'pending') {
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => 'Request is not pending.']) : back()->with('error', 'Request is not pending.');
        }

        try {
            $action->execute($correction, auth()->user());
            return request()->wantsJson() ? response()->json(['success' => true, 'message' => 'Correction rejected.']) : back()->with('success', 'Correction rejected.');
        } catch (\Exception $e) {
            Log::error('Order Correction Reject Error: ' . $e->getMessage());
            return request()->wantsJson() ? response()->json(['success' => false, 'message' => 'Failed to reject request.']) : back()->with('error', 'Failed to reject request.');
        }
    }

    private function extractOldData(Order $order, string $type, ?int $transactionId): array
    {
        if ($type === 'payment_change') {
            $targetTx = $transactionId ? PaymentTransaction::withTrashed()->find($transactionId) : null;
            return [
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'paid_amount' => $order->paid_amount,
                'transaction_id' => $transactionId,
                'amount' => $targetTx ? (float)$targetTx->amount : null,
                'transaction_method' => $targetTx ? $targetTx->payment_method : null,
            ];
        }

        $order->load('items.extras');
        return [
            'payment_method' => $order->payment_method,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
            'delivery_fee' => $order->delivery_fee,
            'discount_percentage' => $order->discount,
            'items' => $order->items->toArray(),
        ];
    }
}
