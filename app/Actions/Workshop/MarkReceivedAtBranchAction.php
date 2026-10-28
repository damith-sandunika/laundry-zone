<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;

class MarkReceivedAtBranchAction
{
    public function execute(WorkshopDispatch $dispatch, array $data): void
    {
        $order = $dispatch->order;

        DB::transaction(function () use ($dispatch, $order, $data) {
            $dispatch->update([
                'status' => 'received_at_branch',
                'received_at_branch_at' => now(),
                'workshop_notes' => $data['workshop_notes'] ?? null,
            ]);

            $order->update([
                'status' => 'done',
                'returned_from_workshop_at' => now(),
                'ready_at' => now(),
            ]);
        });

        $commService = new CommunicationService();
        $commService->sendCollectionOrderReady($order);
    }
}
