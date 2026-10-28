<?php

namespace App\Actions\Workshop;

use App\Models\Order;
use App\Models\WorkshopDispatch;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class DispatchToWorkshopAction
{
    public function execute(Order $order, array $data, int $userId): void
    {
        $existing = WorkshopDispatch::where('order_id', $order->id)
            ->whereNotIn('status', ['received_at_branch'])
            ->first();

        if ($existing) {
            throw new Exception('Order is already dispatched to workshop.');
        }

        DB::transaction(function () use ($order, $data, $userId) {
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
            ]);
        });

        $workshopAdmins = User::whereIn('role', ['workshop_admin', 'staff'])->where('branch_id', $data['workshop_branch_id'])->get();
        Notification::send($workshopAdmins, new SystemAlertNotification('New Order Dispatched from Branch: #' . $order->order_number, route('workshop.dashboard'), 'branch_dispatch'));
    }
}
