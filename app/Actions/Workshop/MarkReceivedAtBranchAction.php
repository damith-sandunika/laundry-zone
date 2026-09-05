<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;

class MarkReceivedAtBranchAction
{
    public function execute(WorkshopDispatch $dispatch, array $data): void
    {
        $dispatch->update([
            'status' => 'received_at_branch',
            'received_at_branch_at' => now(),
            'workshop_notes' => $data['workshop_notes'] ?? null,
        ]);

        $order = $dispatch->order;
        $order->update([
            'status' => 'done',
            'returned_from_workshop_at' => now(),
            'ready_at' => now(),
        ]);

        $commService = new CommunicationService();
        $commService->sendCollectionOrderReady($order);
    }
}
