<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Services\CommunicationService;

class ConfirmAppointmentAction
{
    public function execute(DeliveryAppointment $appointment): void
    {
        $appointment->update(['status' => 'confirmed']);

        $commService = new CommunicationService();
        $commService->sendBookingConfirmation($appointment);
    }
}
