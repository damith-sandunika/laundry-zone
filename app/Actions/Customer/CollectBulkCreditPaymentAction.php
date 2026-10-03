<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class CollectBulkCreditPaymentAction
{
    public function execute(Customer $customer, array $data, int $userId): array
    {
        $totalPayment = (float)$data['amount'];
        $remainingPayment = $totalPayment;
        $settledOrders = [];

        if ($totalPayment <= 0) {
            throw new Exception('Payment amount must be greater than zero.');
        }

        // Get all unpaid credit orders for this customer, oldest first
        $orders = Order::where('customer_id', $customer->id)
            ->where('payment_method', 'credit')
            ->where('status', '!=', 'cancelled')
            ->whereColumn('paid_amount', '<', 'total')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($orders->isEmpty()) {
            throw new Exception('No pending credit orders found for this customer.');
        }

        DB::transaction(function () use ($orders, &$remainingPayment, $data, $userId, &$settledOrders) {
            foreach ($orders as $order) {
                if ($remainingPayment <= 0) {
                    break;
                }

                $due = $order->total - $order->paid_amount;
                $payAmount = min($remainingPayment, $due);

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

                $settledOrders[] = [
                    'order_number' => $order->order_number,
                    'amount_applied' => $payAmount,
                    'fully_settled' => $order->payment_status === 'paid'
                ];

                $remainingPayment -= $payAmount;
            }
        });

        return [
            'total_paid' => $totalPayment,
            'unused_amount' => $remainingPayment,
            'settled_orders' => $settledOrders
        ];
    }
}
