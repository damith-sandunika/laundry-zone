<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class WorkshopBulkStatusUpdateAction
{
    public function execute(array $data, int $userId): void
    {
        $status = $data['status'];
        $dispatchIds = $data['dispatch_ids'];

        DB::transaction(function () use ($dispatchIds, $status, $userId) {
            foreach ($dispatchIds as $id) {
                $dispatch = WorkshopDispatch::find($id);
                if (! $dispatch) {
                    continue;
                }

                if ($status === 'processing') {
                    $dispatch->update(['status' => 'processing']);
                    $dispatch->order->update(['status' => 'processing']);
                } elseif ($status === 'completed_at_workshop') {
                    $dispatch->update([
                        'status' => 'completed_at_workshop',
                        'completed_at_workshop_at' => now(),
                    ]);
                } elseif ($status === 'returned_to_branch') {
                    $dispatch->update([
                        'status' => 'returned_to_branch',
                        'returned_by' => $userId,
                        'returned_at' => now(),
                    ]);
                    $dispatch->order->update(['status' => 'returned_from_workshop']);
                }
            }
        });
    }
}
