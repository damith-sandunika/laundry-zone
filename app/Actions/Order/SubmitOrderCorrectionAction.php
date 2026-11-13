<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SubmitOrderCorrectionAction
{
    public function execute(Order $order, string $type, string $reason, array $oldData, array $newData, $user, bool $isAdmin): array
    {
        return DB::transaction(function () use ($order, $type, $reason, $oldData, $newData, $user, $isAdmin) {
            $correction = OrderCorrectionRequest::create([
                'order_id' => $order->id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'type' => $type,
                'reason' => $reason,
                'old_data' => $oldData,
                'new_data' => $newData,
            ]);

            if ($isAdmin) {
                app(ApproveOrderCorrectionAction::class)->execute($correction, $user);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'changed_by' => $user->id,
                    'note' => "Correction auto-applied by Admin ({$type}): " . $reason,
                ]);

                return ['is_admin' => true, 'correction' => $correction];
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => $user->id,
                'note' => "Correction requested ({$type}): " . $reason,
            ]);

            // Notify branch/workshop admins
            $admins = User::whereIn('role', ['branch_admin', 'workshop_admin'])
                ->where(fn($q) => $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id'))->get();
            Notification::send($admins, new SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

            return ['is_admin' => false, 'correction' => $correction];
        });
    }
}
