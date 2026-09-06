<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\CommunicationService;

class ConfirmAppointmentAction
{
    public function execute(DeliveryAppointment $appointment): void
    {
        $appointment->update(['status' => 'confirmed']);

        // Sync the linked Order status if it exists
        if ($appointment->order_id && $appointment->order) {
            $appointment->order->update(['status' => 'confirmed']);
            try {
                OrderStatusHistory::create([
                    'order_id' => $appointment->order->id,
                    'status' => 'confirmed',
                    'changed_by' => auth()->id(),
                    'note' => 'Delivery appointment confirmed by admin.',
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (confirmAppointment): ' . $e->getMessage());
            }
        }

        $commService = new CommunicationService();
        $commService->sendBookingConfirmation($appointment);
    }
}
