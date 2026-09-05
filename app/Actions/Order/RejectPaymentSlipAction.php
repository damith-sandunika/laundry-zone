<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Exception;

class RejectPaymentSlipAction
{
    public function execute(Order $order, PaymentTransaction $transaction, array $data): void
    {
        if ($transaction->order_id !== $order->id) {
            throw new Exception('Transaction does not belong to this order.');
        }

        $transaction->update([
            'status' => 'rejected',
            'reject_reason' => $data['reject_reason'],
        ]);
    }
}
