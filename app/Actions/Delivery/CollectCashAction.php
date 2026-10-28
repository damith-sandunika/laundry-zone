<?php

namespace App\Actions\Delivery;

use App\Models\DeliveryAppointment;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentTransaction;
use App\Models\DeliveryTask;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;

class CollectCashAction
{
    public function execute(DeliveryAppointment $appointment, array $data): bool
    {
        if (! $appointment->order_id) {
            return false;
        }

        $order = Order::withoutGlobalScopes()->find($appointment->order_id);
        if (! $order) {
            return false;
        }

        DB::transaction(function () use ($appointment, $order, $data) {
            $transactions = PaymentTransaction::where('order_id', $order->id)
                ->where('payment_method', 'cash')
                ->whereNull('collected_from_rider_at')
                ->whereNotNull('created_by')
                ->get();

            $cashCollected = $data['cash_collected'] ?? null;

            foreach ($transactions as $tx) {
                $user = User::withoutGlobalScopes()->find($tx->created_by);
                if ($user && $user->role === 'rider') {
                    $tx->update([
                        'collected_from_rider_at' => now(),
                        'amount' => $cashCollected ?? $tx->amount
                    ]);
                }
            }
            
            $task = DeliveryTask::where('delivery_appointment_id', $appointment->id)
                ->where('task_type', 'delivery')
                ->where('status', 'waiting_for_payment')
                ->first();

            if ($task) {
                $task->update(['status' => 'completed', 'completed_at' => now()]);
            }

            $appointment->update(['status' => 'delivered']);
            $order->update(['status' => 'delivered', 'delivered_at' => now()]);

            try {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'delivered',
                    'changed_by' => auth()->id(),
                    'note' => 'Cash collected from rider. Order marked delivered.',
                ]);
            } catch (\Exception $e) {
                \Log::warning('OrderStatusHistory insert failed (collectCash): ' . $e->getMessage());
            }
        });

        try {
            $commService = new CommunicationService();
            $commService->sendDeliveryCompleted($order);
        } catch (\Exception $e) {
            \Log::error('Delivery notification failed (collectCash): ' . $e->getMessage());
        }

        return true;
    }
}
