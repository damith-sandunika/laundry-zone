<?php

namespace App\Actions\Order;

use App\Models\Order;

class ConvertOrderToPickupAction
{
    public function execute(Order $order, array $newData)
    {
        if ($order->deliveryAppointment) {
            $order->deliveryAppointment->update([
                'delivery_preference'   => 'pickup_from_branch',
                'delivery_scheduled_at' => null,
                'delivery_cost'         => 0,
            ]);
        }

        $deductAmount = (float)($newData['deduct_amount'] ?? 0);
        $newDeliveryFee = $order->delivery_fee;
        $newTotal = $order->total;

        if ($deductAmount > 0) {
            $deductAmount = min($deductAmount, $order->delivery_fee);
            $newDeliveryFee -= $deductAmount;
            $newTotal -= $deductAmount;
        }

        $paymentStatus = $order->paid_amount >= $newTotal ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');

        $order->update([
            'is_delivery_order' => false,
            'delivery_fee' => $newDeliveryFee,
            'total' => $newTotal,
            'payment_status' => $paymentStatus,
        ]);

        return "Correction approved (convert_to_pickup): Converted to Customer Pickup. Delivery fee deducted: Rs. " . number_format($deductAmount, 2);
    }
}
