<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\DeliveryTask;
use App\Models\Expense;
use App\Models\PaymentTransaction;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class DispatchOrderDeliveryAction
{
    public function execute(Order $order, array $data, int $userId): array
    {
        $appointment = $order->deliveryAppointment;
        $branchId = User::find($userId)->branch_id ?? \App\Models\Branch::first()->id;

        if ($data['delivery_method'] === 'rider') {
            DeliveryTask::create([
                'delivery_appointment_id' => $appointment ? $appointment->id : null,
                'order_id' => $order->id,
                'rider_id' => $data['rider_id'],
                'branch_id' => $branchId,
                'task_type' => 'delivery',
                'status' => 'assigned',
            ]);

            if ($appointment) {
                $appointment->update(['status' => 'out_for_delivery']);
            }
            $order->update(['status' => 'out_for_delivery']);

            $riderUser = User::find($data['rider_id']);
            if ($riderUser) {
                Notification::send($riderUser, new SystemAlertNotification('New Delivery Task: Order #' . $order->order_number, route('rider.tasks')));

                try {
                    $commService = new CommunicationService();
                    $commService->sendRiderAssigned($order, $riderUser);
                } catch (Exception $e) {
                    Log::warning('sendRiderAssigned failed (DispatchOrderDelivery): ' . $e->getMessage());
                }
            }

            return ['status' => 'dispatched_for_delivery'];

        } else {
            Expense::create([
                'branch_id' => $branchId,
                'amount' => $data['pickme_cost'],
                'category' => 'PickMe',
                'description' => 'PickMe Delivery for Order #' . $order->order_number,
                'expense_date' => now(),
                'created_by' => $userId,
            ]);

            $amountToPay = $order->total - $order->paid_amount;
            if ($amountToPay > 0) {
                $paymentMethod = $data['payment_method'] ?? 'cash';
                PaymentTransaction::create([
                    'order_id'       => $order->id,
                    'amount'         => $amountToPay,
                    'payment_method' => $paymentMethod,
                    'created_by'     => $userId,
                    'collected_from_rider_at' => now(),
                    'status'         => 'completed',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                ]);
                $order->update([
                    'paid_amount' => $order->total,
                    'payment_status' => 'paid',
                ]);
            }

            if ($appointment) {
                $appointment->update(['status' => 'delivered']);
            }
            $order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'delivered',
                'changed_by' => $userId,
                'note' => 'Delivered via PickMe',
            ]);

            try {
                $commService = new CommunicationService();
                $commService->sendDeliveryCompleted($order);
            } catch (Exception $e) {
                Log::error('Delivery notification failed (Order PickMe): ' . $e->getMessage());
            }

            return ['status' => 'completed'];
        }
    }
}
