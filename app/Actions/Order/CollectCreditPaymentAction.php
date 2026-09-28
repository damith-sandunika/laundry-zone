<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class CollectCreditPaymentAction
{
    public function execute(Order $order, array $data, int $userId): void
    {
        if ($order->payment_status === 'paid' || $order->total <= $order->paid_amount) {
            throw new Exception('Order is already fully paid.');
        }

        $due = $order->total - $order->paid_amount;
        $payAmount = min($data['amount'], $due);

        DB::transaction(function () use ($order, $payAmount, $data, $userId) {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $payAmount,
                'payment_method' => $data['payment_method'],
                'created_by' => $userId,
            ]);

            $order->paid_amount += $payAmount;

            if ($order->paid_amount >= $order->total) {
                $order->payment_status = 'paid';
            }

            $order->save();
        });
    }
}
