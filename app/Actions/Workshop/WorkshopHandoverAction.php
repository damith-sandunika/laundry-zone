<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Models\PaymentTransaction;
use App\Models\OrderStatusHistory;
use App\Models\OrderCubby;
use App\Models\Cubby;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class WorkshopHandoverAction
{
    public function execute(WorkshopDispatch $dispatch, array $data, int $userId): void
    {
        $order = $dispatch->order;

        DB::transaction(function () use ($data, $order, $dispatch, $userId) {
            $amountPaid = (float) ($data['amount_paid'] ?? 0);
            if ($amountPaid > 0) {
                PaymentTransaction::create([
                    'order_id'       => $order->id,
                    'amount'         => $amountPaid,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'created_by'     => $userId,
                    'status'         => 'completed',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                ]);
                $newPaid = $order->paid_amount + $amountPaid;
                $order->update([
                    'paid_amount'    => $newPaid,
                    'payment_method' => $data['payment_method'] ?? $order->payment_method,
                    'payment_status' => $newPaid >= $order->total ? 'paid' : 'partial',
                ]);
                $order = $order->fresh();
            }

            if ($order->paid_amount < $order->total) {
                throw new Exception('Balance due: Rs. ' . number_format($order->total - $order->paid_amount, 2) . '. Collect full payment before handover.');
            }

            $order->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
            ]);

            try {
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'delivered',
                    'note'       => 'Handed over to customer at workshop',
                    'changed_by' => $userId,
                ]);
            } catch (Exception $e) {
                Log::warning('OrderStatusHistory insert failed (handover): ' . $e->getMessage());
            }

            $cubbyIds = OrderCubby::where('order_id', $order->id)->pluck('cubby_id');
            OrderCubby::where('order_id', $order->id)->whereNull('released_at')
                ->update(['released_at' => now()]);
            foreach ($cubbyIds as $cid) {
                $cubby = Cubby::find($cid);
                if ($cubby && !$cubby->currentOrder()->exists()) {
                    $cubby->update(['status' => 'available']);
                }
            }

            $dispatch->update([
                'status'         => 'returned_to_branch',
                'returned_by'    => $userId,
                'returned_at'    => now(),
                'workshop_notes' => 'Handed over directly to customer at workshop',
            ]);
        });

        try {
            $order = $order->fresh();
            $commService = new CommunicationService();
            $commService->sendPosWalkinCompleted($order);
        } catch (Exception $e) {
            Log::error('Email notification failed (handover): ' . $e->getMessage());
        }
    }
}
