<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\PaymentTransaction;

class UpdateOrderPaymentAction
{
    public function execute(Order $order, array $newData)
    {
        $newMethod = $newData['payment_method'];
        $transactionId = $newData['transaction_id'] ?? null;

        if ($transactionId) {
            $tx = PaymentTransaction::find($transactionId);
            if ($tx) {
                $oldMethod = $tx->payment_method;
                if ($newMethod === 'credit' && $oldMethod !== 'credit') {
                    $tx->delete();
                } else {
                    $tx->update(['payment_method' => $newMethod]);
                }
            }
        } else {
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
        }

        $actualPaidAmount = PaymentTransaction::where('order_id', $order->id)->where('status', 'completed')->sum('amount');
        $paymentStatus = $actualPaidAmount >= $order->total ? 'paid' : ($actualPaidAmount > 0 ? 'partial' : ($newMethod === 'credit' ? 'pending' : 'unpaid'));
        
        $order->update([
            'payment_method' => $newMethod,
            'paid_amount' => $actualPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
        
        return "Correction approved (payment_change): Payment method updated to {$newMethod}.";
    }
}
