<?php

namespace App\Actions\Order;

use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Notifications\SystemAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RejectOrderCorrectionAction
{
    public function execute(OrderCorrectionRequest $correction, $user): void
    {
        DB::transaction(function () use ($correction, $user) {
            $correction->update([
                'status' => 'rejected',
                'approved_by' => $user->id,
                'resolved_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $correction->order_id,
                'status' => $correction->order->status,
                'changed_by' => $user->id,
                'note' => "Correction rejected ({$correction->type}).",
            ]);
        });

        if ($correction->requestedBy) {
            Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Rejected for Order #{$correction->order->order_number}", route('orders.show', $correction->order->order_number), 'error'));
        }
    }
}

