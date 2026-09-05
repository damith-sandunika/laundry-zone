<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;

class CancelAppointmentAction
{
    public function execute(DeliveryAppointment $appointment, array $data): void
    {
        $appointment->update([
            'status' => 'cancelled',
            'cancel_reason' => $data['reason'] ?? null,
            'cancelled_by' => auth()->id(),
        ]);
    }
}
