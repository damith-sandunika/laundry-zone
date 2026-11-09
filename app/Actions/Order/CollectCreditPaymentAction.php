<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class CollectCreditPaymentAction
{
    /**
     * Settle credit/unpaid payment for an order with pessimistic locking and validation.
     *
     * @param Order $order
     * @param array $data
     * @param int $userId
     * @throws Exception
     */
    public function execute(Order $order, array $data, int $userId): void
    {
        // Strict guard: Payment method cannot be credit when settling unpaid balances
        if (isset($data['payment_method']) && $data['payment_method'] === 'credit') {
            throw new Exception('Invalid payment method: Unpaid orders cannot be settled using credit.');
        }

        DB::transaction(function () use ($order, $data, $userId) {
            // Pessimistic lock on the order row to prevent race conditions & double-charging
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (!$lockedOrder) {
                throw new Exception('Order record not found.');
            }

            if ($lockedOrder->payment_status === 'paid' || $lockedOrder->total <= $lockedOrder->paid_amount) {
                throw new Exception('Order is already fully paid.');
            }

            $due = $lockedOrder->total - $lockedOrder->paid_amount;
            $payAmount = min((float)$data['amount'], (float)$due);

            if ($payAmount <= 0) {
                throw new Exception('Invalid payment amount specified.');
            }

            PaymentTransaction::create([
                'order_id'       => $lockedOrder->id,
                'amount'         => $payAmount,
                'payment_method' => $data['payment_method'],
                'created_by'     => $userId,
            ]);

            $lockedOrder->paid_amount += $payAmount;

            if ($lockedOrder->paid_amount >= $lockedOrder->total) {
                $lockedOrder->payment_status = 'paid';
            }

            $lockedOrder->save();

            // Sync original model attributes
            $order->paid_amount = $lockedOrder->paid_amount;
            $order->payment_status = $lockedOrder->payment_status;
        });
    }
}
