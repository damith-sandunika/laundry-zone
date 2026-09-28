<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\DeliveryAppointment;

class ConvertOrderToDeliveryAction
{
    public function execute(Order $order, array $newData)
    {
        $fee = (float)($newData['delivery_fee'] ?? 0);
        $address = $newData['delivery_address'] ?? '';
        $phone = $newData['contact_phone'] ?? '';

        $appointmentStatus = match($order->status) {
            'done' => 'ready',
            'completed_at_workshop', 'processing' => 'processing',
            'returned_from_workshop' => 'at_branch',
            default => 'order_created',
        };

        $appointment = DeliveryAppointment::updateOrCreate(
            ['order_id' => $order->id, 'order_number' => $order->order_number],
            [
                'customer_id' => $order->customer_id,
                'branch_id' => $order->branch_id,
                'delivery_address' => $address,
                'contact_phone' => $phone,
                'delivery_preference' => 'deliver_to_address',
                'status' => $appointmentStatus,
                'delivery_cost' => $fee,
            ]
        );

        $newTotal = $order->total + $fee;
        $paymentStatus = $order->paid_amount >= $newTotal ? 'paid' : ($order->paid_amount > 0 ? 'partial' : 'unpaid');

        $order->update([
            'is_delivery_order' => true,
            'delivery_fee' => $order->delivery_fee + $fee,
            'total' => $newTotal,
            'payment_status' => $paymentStatus,
            'delivery_address' => $address,
            'delivery_appointment_id' => $appointment->id,
        ]);

        return "Correction approved (convert_to_delivery): Converted to Delivery Order. Delivery Fee: Rs. " . number_format($fee, 2);
    }
}
