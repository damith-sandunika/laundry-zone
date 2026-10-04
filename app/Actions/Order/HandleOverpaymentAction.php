<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\CashBalanceService;

class HandleOverpaymentAction
{
    public function execute(Order $order, array $newData)
    {
        $action = $newData['action'] ?? 'keep';
        $overpayment = (float) ($newData['amount'] ?? 0);

        if ($action === 'refund') {
            PaymentTransaction::create([
                'order_id'       => $order->id,
                'amount'         => -$overpayment,
                'payment_method' => 'cash',
                'created_by'     => auth()->id(),
                'status'         => 'completed',
                'transaction_id' => 'REFUND-' . strtoupper(uniqid()),
            ]);

            $order->paid_amount -= $overpayment;
            $order->payment_status = $order->paid_amount >= $order->total ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');
            $order->save();

            return 'Correction approved (Refund): Rs. ' . number_format($overpayment, 2) . ' cash returned to customer.';
        }

        return 'Correction approved (Store Credit): Rs. ' . number_format($overpayment, 2) . ' kept as store credit.';
    }
}
