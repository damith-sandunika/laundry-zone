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

        try {
            DB::transaction(function () use ($dispatchIds, $status, $userId) {
                WorkshopDispatch::with('order')->whereIn('id', $dispatchIds)->chunkById(100, function ($dispatches) use ($status, $userId) {
                    foreach ($dispatches as $dispatch) {
                        if ($status === 'processing') {
                            $dispatch->update(['status' => 'processing']);
                            if ($dispatch->order) {
                                $dispatch->order->update(['status' => 'processing']);
                            }
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
                            if ($dispatch->order) {
                                $dispatch->order->update(['status' => 'returned_from_workshop']);
                            }
                        }
                    }
                });
            });

            Log::info("Workshop bulk status update completed.", ['count' => count($dispatchIds), 'status' => $status, 'user_id' => $userId]);

        } catch (\Throwable $e) {
            Log::error("Workshop Bulk Status Update failed: " . $e->getMessage(), [
                'dispatch_ids' => $dispatchIds,
                'status' => $status,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
