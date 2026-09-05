<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class ApprovePaymentSlipAction
{
    public function execute(Order $order, PaymentTransaction $transaction): void
    {
        if ($transaction->order_id !== $order->id) {
            throw new Exception('Transaction does not belong to this order.');
        }

        DB::transaction(function () use ($order, $transaction) {
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
    }
}
