<?php

namespace App\Actions\Workshop;

use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BulkDispatchToWorkshopAction
{
    public function execute(array $data, int $userId): int
    {
        try {
            $count = Order::whereIn('id', $data['order_ids'])
                ->whereNotIn('status', ['dispatched_to_workshop'])
                ->count();

            if ($count === 0) {
                return 0;
            }

            DB::transaction(function () use ($data, $userId) {
                Order::whereIn('id', $data['order_ids'])
                    ->whereNotIn('status', ['dispatched_to_workshop'])
                    ->chunkById(100, function ($orders) use ($data, $userId) {
                        foreach ($orders as $order) {
                            WorkshopDispatch::create([
                                'order_id' => $order->id,
                                'branch_id' => $order->branch_id,
                                'workshop_branch_id' => $data['workshop_branch_id'],
                                'dispatched_by' => $userId,
                                'status' => 'dispatched_to_workshop',
                                'dispatched_at' => now(),
                                'dispatch_notes' => $data['dispatch_notes'] ?? null,
                            ]);

                            $order->update([
                                'status' => 'dispatched_to_workshop',
                                'dispatched_to_workshop_at' => now(),
                                'workshop_id' => $data['workshop_branch_id'],
                            ]);
                        }
                    });
            });

            $workshopAdmins = User::whereIn('role', ['workshop_admin', 'staff'])->where('branch_id', $data['workshop_branch_id'])->get();
            Notification::send($workshopAdmins, new SystemAlertNotification($count . ' Orders Dispatched from Branch', route('workshop.dashboard'), 'branch_dispatch'));

            Log::info("Bulk dispatch to workshop completed successfully.", ['count' => $count, 'workshop_id' => $data['workshop_branch_id'], 'user_id' => $userId]);

            return $count;

        } catch (\Throwable $e) {
            Log::error("Bulk Dispatch to Workshop failed: " . $e->getMessage(), [
                'order_ids' => $data['order_ids'] ?? [],
                'workshop_branch_id' => $data['workshop_branch_id'] ?? null,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
