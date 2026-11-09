<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\CashBalanceService;

class UpdateOrderPaymentAction
{
    public function execute(Order $order, array $newData)
    {
        $transactionId = $newData['transaction_id'] ?? null;
        $isDeletion = !empty($newData['is_delete']);

        if ($transactionId) {
            $tx = PaymentTransaction::withTrashed()->find($transactionId);
            if (!$tx) {
                throw new \Exception("Payment transaction not found.");
            }

            // Strict Same-Day Verification: Accounts cannot be modified for previous days
            if ($tx->created_at->format('Y-m-d') !== now()->format('Y-m-d')) {
                throw new \Exception("Cannot modify payments from previous days because accounts are already balanced.");
            }

            if ($isDeletion) {
                $oldAmount = $tx->amount;
                $oldMethod = $tx->payment_method;
                $reason = $newData['delete_reason'] ?? ($newData['reason'] ?? 'Payment deleted by admin');

                $tx->update([
                    'deleted_by' => auth()->id(),
                    'delete_reason' => $reason,
                ]);
                $tx->delete(); // Soft deletes the transaction

                // Recalculate order totals
                $this->recalculateOrderTotals($order);

                return "Payment transaction #{$tx->id} of Rs. " . number_format($oldAmount, 2) . " ({$oldMethod}) was DELETED. Reason: {$reason}";
            } else {
                // Editing existing transaction
                $oldMethod = $tx->payment_method;
                $oldAmount = (float) $tx->amount;
                $newMethod = $newData['payment_method'] ?? $oldMethod;
                $newAmount = isset($newData['amount']) ? (float) $newData['amount'] : $oldAmount;
                $reason = $newData['reason'] ?? 'Payment details updated';

                // Handle cash balance adjustments if amounts or methods changed
                $this->adjustCashBalances($order->branch_id, $oldMethod, $oldAmount, $newMethod, $newAmount);

                $tx->update([
                    'payment_method' => $newMethod,
                    'amount' => $newAmount,
                    'is_edited' => true,
                    'edited_at' => now(),
                    'edited_by' => auth()->id(),
                    'original_amount' => $tx->original_amount ?? $oldAmount,
                    'original_payment_method' => $tx->original_payment_method ?? $oldMethod,
                    'edit_reason' => $reason,
                ]);

                // Recalculate order totals
                $this->recalculateOrderTotals($order);

                return "Payment transaction #{$tx->id} updated from Rs. " . number_format($oldAmount, 2) . " ({$oldMethod}) to Rs. " . number_format($newAmount, 2) . " ({$newMethod}). Reason: {$reason}";
            }
        }

        // Fallback for general order-level payment method change (if no specific transaction ID)
        $newMethod = $newData['payment_method'];
        $oldMethod = $order->payment_method;

        if ($newMethod === 'credit' && $oldMethod !== 'credit') {
            PaymentTransaction::where('order_id', $order->id)->delete();
        } elseif ($oldMethod === 'credit' && $newMethod !== 'credit') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'payment_method' => $newMethod,
                'status' => 'completed',
                'created_by' => auth()->id() ?? 1,
            ]);
        } else {
            PaymentTransaction::where('order_id', $order->id)
                ->where('payment_method', $oldMethod)
                ->update(['payment_method' => $newMethod]);
        }

        $this->recalculateOrderTotals($order);

        return "Order payment method updated to {$newMethod}.";
    }

    protected function recalculateOrderTotals(Order $order)
    {
        $actualPaidAmount = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'completed')
            ->sum('amount');

        $paymentStatus = $actualPaidAmount >= $order->total ? 'paid' : ($actualPaidAmount > 0 ? 'partial' : 'unpaid');

        // Determine active primary payment method from remaining non-deleted transactions
        $latestTx = PaymentTransaction::where('order_id', $order->id)->latest()->first();
        $primaryMethod = $latestTx ? $latestTx->payment_method : $order->payment_method;

        $order->update([
            'payment_method' => $primaryMethod,
            'paid_amount' => $actualPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    protected function adjustCashBalances(?int $branchId, string $oldMethod, float $oldAmount, string $newMethod, float $newAmount)
    {
        if (!$branchId) return;
        $cashService = app(CashBalanceService::class);

        // Reverse old amount
        if ($oldMethod === 'cash') {
            if ($oldAmount > 0) {
                $cashService->decrementCashBalance($branchId, $oldAmount);
            } elseif ($oldAmount < 0) {
                $cashService->incrementCashBalance($branchId, abs($oldAmount));
            }
        }

        // Apply new amount
        if ($newMethod === 'cash') {
            if ($newAmount > 0) {
                $cashService->incrementCashBalance($branchId, $newAmount);
            } elseif ($newAmount < 0) {
                $cashService->decrementCashBalance($branchId, abs($newAmount));
            }
        }
    }
}
