<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Models\OrderStatusHistory;
use Exception;
use Illuminate\Support\Facades\Log;

class StartProcessingWorkshopAction
{
    public function execute(WorkshopDispatch $dispatch, int $userId): void
    {
        $order = $dispatch->order;

        $dispatch->update(['status' => 'processing']);
        $order->update(['status' => 'processing']);
        
        try {
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'processing',
                'changed_by' => $userId,
                'note'       => 'Processing started',
            ]);
        } catch (Exception $e) {
            Log::warning('OrderStatusHistory insert failed (startProcessing): ' . $e->getMessage());
        }
    }
}
