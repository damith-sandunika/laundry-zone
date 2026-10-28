<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;

class AssignPickupRiderAction
{
    public function execute(DeliveryAppointment $appointment, array $data): ?User
    {
        $deliveryMethod = $data['delivery_method'] ?? 'in_house';

        if ($deliveryMethod === 'pickme_flash') {
            $appointment->update(['status' => 'rider_assigned']);
            return null; // Return null when PickMe is assigned
        }

        $rider = User::findOrFail($data['rider_id']);

        DB::transaction(function () use ($appointment, $data, $rider) {
            DeliveryTask::create([
                'delivery_appointment_id' => $appointment->id,
                'rider_id' => $rider->id,
                'branch_id' => $appointment->branch_id,
                'task_type' => 'pickup',
                'status' => 'assigned',
                'pickup_location' => $appointment->pickup_address,
                'dropoff_location' => Branch::find($appointment->branch_id)->address ?? 'Workshop',
            ]);

            $appointment->update(['status' => 'rider_assigned']);

            $order = Order::find($appointment->order_id);
            if ($order) {
                $order->update(['status' => 'rider_assigned']);
                try {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'rider_assigned',
                        'changed_by' => auth()->id(),
                        'note' => 'Pickup rider assigned: ' . $rider->name,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('OrderStatusHistory insert failed (assignPickup): ' . $e->getMessage());
                }

                try {
                    $commService = new \App\Services\CommunicationService();
                    $commService->sendRiderAssigned($order, $rider);
                } catch (\Exception $e) {
                    \Log::warning('sendRiderAssigned failed (assignPickup): ' . $e->getMessage());
                }
            }
        });

        Notification::send($rider, new SystemAlertNotification('New Pickup Task Assigned: #' . $appointment->id, route('rider.tasks')));

        return $rider;
    }
}
