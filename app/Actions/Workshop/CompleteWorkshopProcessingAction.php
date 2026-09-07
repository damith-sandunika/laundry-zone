<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Models\OrderCubby;
use App\Models\Cubby;
use App\Models\PaymentTransaction;
use App\Models\OrderStatusHistory;
use App\Services\CommunicationService;
use Exception;
use Illuminate\Support\Facades\Log;

class CompleteWorkshopProcessingAction
{
    public function execute(WorkshopDispatch $dispatch, array $data, int $userId): void
    {
        $dispatch->update([
            'status'                   => 'completed_at_workshop',
            'completed_at_workshop_at' => now(),
        ]);

        $order = $dispatch->order;

        if (!empty($data['cubby_id'])) {
            OrderCubby::where('order_id', $order->id)->whereNull('released_at')
                ->update(['released_at' => now()]);
            $cubby = Cubby::find($data['cubby_id']);
            if ($cubby) {
                OrderCubby::create([
                    'order_id'    => $order->id,
                    'cubby_id'    => $cubby->id,
                    'assigned_at' => now(),
                ]);
                $cubby->update(['status' => 'occupied']);
            }
        }

        if (!empty($data['amount_paid']) && $data['amount_paid'] > 0) {
            PaymentTransaction::create([
                'order_id'       => $order->id,
                'amount'         => $data['amount_paid'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'created_by'     => $userId,
                'status'         => 'completed',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ]);
            $newPaid = $order->paid_amount + $data['amount_paid'];
            $order->update([
                'paid_amount'    => $newPaid,
                'payment_method' => $data['payment_method'] ?? $order->payment_method,
                'payment_status' => $newPaid >= $order->total ? 'paid' : 'partial',
            ]);
        }

        $order->update(['status' => 'completed_at_workshop']);

        try {
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'completed_at_workshop',
                'note'       => 'Completed at Workshop',
                'changed_by' => $userId,
            ]);
        } catch (Exception $e) {
            Log::warning('OrderStatusHistory insert failed (complete): ' . $e->getMessage());
        }

        if ($dispatch->branch_id === $dispatch->workshop_branch_id && !$order->is_delivery_order) {
            try {
                $commService = new CommunicationService();
                $commService->sendPosWalkinReady($order);
            } catch (Exception $e) {
                Log::error('Email notification failed (complete ready): ' . $e->getMessage());
            }
        }
    }
}
