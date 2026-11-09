<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\CashBalanceService;
use Illuminate\Support\Facades\DB;

class UpdateOrderPaymentAction
{
    /**
     * Execute payment update/delete with pessimistic row locking and automatic deadlock retry.
     *
     * @param Order $order
     * @param array $newData
     * @return array Result metadata including success flag, action type, note, and refreshed order
     * @throws \Exception
     */
    public function execute(Order $order, array $newData): array
    {
        return DB::transaction(function () use ($order, $newData) {
            // Lock order for update to prevent concurrent payment recalculation races
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (!$lockedOrder) {
                throw new \Exception("Order #{$order->id} not found.");
            }

            $transactionId = $newData['transaction_id'] ?? null;
            $isDeletion = !empty($newData['is_delete']);

            if ($transactionId) {
                $tx = PaymentTransaction::withTrashed()->where('id', $transactionId)->lockForUpdate()->first();
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
                    $this->recalculateOrderTotals($lockedOrder);

                    // Sync caller instance
                    $order->refresh();

                    return [
                        'success' => true,
                        'action' => 'deleted',
                        'transaction_id' => $tx->id,
                        'message' => "Payment transaction #{$tx->id} of Rs. " . number_format($oldAmount, 2) . " ({$oldMethod}) was DELETED. Reason: {$reason}",
                        'order' => $order,
                    ];
                } else {
                    // Editing existing transaction
                    $oldMethod = $tx->payment_method;
                    $oldAmount = (float) $tx->amount;
                    $newMethod = $newData['payment_method'] ?? $oldMethod;
                    $newAmount = isset($newData['amount']) ? (float) $newData['amount'] : $oldAmount;
                    $reason = $newData['reason'] ?? 'Payment details updated';

                    // Handle cash balance adjustments if amounts or methods changed
                    $this->adjustCashBalances($lockedOrder->branch_id, $oldMethod, $oldAmount, $newMethod, $newAmount);

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
                    $this->recalculateOrderTotals($lockedOrder);

                    // Sync caller instance
                    $order->refresh();

                    return [
                        'success' => true,
                        'action' => 'updated',
                        'transaction_id' => $tx->id,
                        'message' => "Payment transaction #{$tx->id} updated from Rs. " . number_format($oldAmount, 2) . " ({$oldMethod}) to Rs. " . number_format($newAmount, 2) . " ({$newMethod}). Reason: {$reason}",
                        'order' => $order,
                    ];
                }
            }

            // Fallback for general order-level payment method change (if no specific transaction ID)
            $newMethod = $newData['payment_method'] ?? $lockedOrder->payment_method;
            $oldMethod = $lockedOrder->payment_method;

            if ($newMethod === 'credit' && $oldMethod !== 'credit') {
                PaymentTransaction::where('order_id', $lockedOrder->id)->delete();
            } elseif ($oldMethod === 'credit' && $newMethod !== 'credit') {
                PaymentTransaction::create([
                    'order_id' => $lockedOrder->id,
                    'amount' => $lockedOrder->total,
                    'payment_method' => $newMethod,
                    'status' => 'completed',
                    'created_by' => auth()->id() ?? 1,
                ]);
            } else {
                PaymentTransaction::where('order_id', $lockedOrder->id)
                    ->where('payment_method', $oldMethod)
                    ->update(['payment_method' => $newMethod]);
            }

            $this->recalculateOrderTotals($lockedOrder);
            $order->refresh();

            return [
                'success' => true,
                'action' => 'method_changed',
                'transaction_id' => null,
                'message' => "Order payment method updated to {$newMethod}.",
                'order' => $order,
            ];
        }, 5);
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
