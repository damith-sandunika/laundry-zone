<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\DeliveryTask;
use App\Models\Order;
use App\Models\Expense;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Branch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;

class AssignDropoffRiderAction
{
    public function execute(DeliveryAppointment $appointment, array $data): ?User
    {
        $riderId = $data['rider_id'];
        $order = Order::find($data['order_id']);

        if ($riderId === 'pickme_flash') {
            $appointment->update([
                'status' => 'delivered', 
                'delivery_method' => 'pickme_flash',
                'delivery_cost' => !empty($data['pickme_flash_cost']) ? $data['pickme_flash_cost'] : 0
            ]);
            
            if ($order) {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
                
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'changed_by' => auth()->id(),
                    'note' => 'Delivered via PickMe',
                ]);
                
                $amountToPay = $order->total - $order->paid_amount;
                if ($amountToPay > 0) {
                    $paymentMethod = $data['payment_method'] ?? 'cash';
                    PaymentTransaction::create([
                        'order_id' => $order->id,
                        'amount' => $amountToPay,
                        'payment_method' => $paymentMethod,
                        'created_by' => auth()->id(),
                        'collected_from_rider_at' => now(),
                        'status' => 'completed',
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    ]);
                    $order->update([
                        'paid_amount' => $order->total,
                        'payment_status' => 'paid'
                    ]);
                } elseif ($amountToPay <= 0) {
                    $order->update(['payment_status' => 'paid']);
                }

                try {
                    $commService = new CommunicationService();
                    $commService->sendDeliveryCompleted($order);
                } catch (\Exception $e) {
                    \Log::error('Delivery notification failed (PickMe): ' . $e->getMessage());
                }

                foreach (['out_for_delivery', 'completed'] as $st) {
                    try {
                        OrderStatusHistory::create([
                            'order_id' => $order->id,
                            'status' => $st,
                            'changed_by' => auth()->id(),
                            'note' => 'Auto-updated via PickMe/Flash dispatch',
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Status history creation skipped: ' . $e->getMessage());
                    }
                }
            }

            if (!empty($data['pickme_flash_cost'])) {
                Expense::create([
                    'branch_id' => auth()->user()->branch_id,
                    'category' => 'PickMe',
                    'description' => 'PickMe payment for delivery dropoff Order #' . ($appointment->order ? $appointment->order->id : $appointment->order_id),
                    'amount' => $data['pickme_flash_cost'],
                    'expense_date' => now(),
                    'created_by' => auth()->id(),
                ]);
            }

            return null; // Return null when PickMe is assigned
        }

        $rider = User::findOrFail($riderId);

        DeliveryTask::create([
            'delivery_appointment_id' => $appointment->id,
            'order_id' => $order->id,
            'rider_id' => $rider->id,
            'branch_id' => $appointment->branch_id,
            'task_type' => 'delivery',
            'status' => 'assigned',
            'pickup_location' => Branch::find($appointment->branch_id)->address ?? 'Branch',
            'dropoff_location' => $appointment->delivery_address,
        ]);

        $appointment->update(['status' => 'out_for_delivery', 'delivery_method' => 'in_house']);
        
        if ($order) {
            $order->update(['status' => 'out_for_delivery']);
            try {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'out_for_delivery',
                    'changed_by' => auth()->id(),
                    'note' => 'Dropoff rider assigned: ' . $rider->name,
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (assignDropoff): ' . $e->getMessage());
            }
            $commService = new CommunicationService();
            $commService->sendDeliveryConfirmation($order);
            $commService->sendRiderAssigned($order, $rider);
        }

        Notification::send($rider, new SystemAlertNotification('New Delivery Drop-off Task Assigned: Order #' . $appointment->order->id, route('rider.tasks')));

        return $rider;
    }
}
