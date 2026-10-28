<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiveWorkshopDispatchAction
{
    public function execute(WorkshopDispatch $dispatch, int $userId): void
    {
        DB::transaction(function () use ($dispatch, $userId) {
            $dispatch->update([
                'status'                    => 'received_at_workshop',
                'received_by'               => $userId,
                'received_at_workshop_at'   => now(),
            ]);

            $dispatch->order->update(['status' => 'processing']);
        });
    }
}
