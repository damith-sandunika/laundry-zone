<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemAlertNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class ReturnToBranchAction
{
    public function execute(WorkshopDispatch $dispatch, int $userId): void
    {
        $dispatch->update([
            'status'      => 'returned_to_branch',
            'returned_by' => $userId,
            'returned_at' => now(),
        ]);

        $order = $dispatch->order;
        $order->update(['status' => 'returned_from_workshop']);

        try {
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'returned_from_workshop',
                'changed_by' => $userId,
                'note'       => 'Washing completed. Dispatched back to branch.',
            ]);
        } catch (Exception $e) {
            Log::warning('OrderStatusHistory insert failed (returnToBranch): ' . $e->getMessage());
        }

        try {
            $branchAdmins = User::whereIn('role', ['branch_admin', 'admin', 'staff'])
                ->where('branch_id', $dispatch->branch_id)->get();
            Notification::send(
                $branchAdmins,
                new SystemAlertNotification(
                    'Order Ready from Workshop: #' . $order->order_number,
                    route('orders.show', $order->order_number),
                    'workshop_return'
                )
            );
        } catch (Exception $e) {
            Log::error('Notification failed (returnToBranch): ' . $e->getMessage());
        }
    }
}
