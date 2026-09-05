<?php

namespace App\Actions\Workshop;

use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use App\Models\User;

class BulkDispatchToWorkshopAction
{
    public function execute(array $data, int $userId): int
    {
        $orders = Order::whereIn('id', $data['order_ids'])
            ->whereNotIn('status', ['dispatched_to_workshop'])
            ->get();

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

        $workshopAdmins = User::whereIn('role', ['workshop_admin', 'staff'])->where('branch_id', $data['workshop_branch_id'])->get();
        Notification::send($workshopAdmins, new SystemAlertNotification(count($orders) . ' Orders Dispatched from Branch', route('workshop.dashboard'), 'branch_dispatch'));

        return count($orders);
    }
}
