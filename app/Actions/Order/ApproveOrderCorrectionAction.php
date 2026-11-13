<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApproveOrderCorrectionAction
{
    public function execute(OrderCorrectionRequest $correction, $user): void
    {
        DB::transaction(function () use ($correction, $user) {
            $order = $correction->order ?? Order::withoutGlobalScopes()->find($correction->order_id);
            $newData = $correction->new_data;

            if ($correction->type === 'payment_change') {
                $result = app(UpdateOrderPaymentAction::class)->execute($order, $newData);
                $correctionNote = is_array($result) ? ($result['message'] ?? 'Payment updated') : (string)$result;
            } elseif (in_array($correction->type, ['items_change', 'order_change'])) {
                $correctionNote = app(UpdateOrderItemsAction::class)->execute($order, $newData, $correction->type);
            } elseif ($correction->type === 'convert_to_delivery') {
                $correctionNote = app(ConvertOrderToDeliveryAction::class)->execute($order, $newData);
            } elseif ($correction->type === 'convert_to_pickup') {
                $correctionNote = app(ConvertOrderToPickupAction::class)->execute($order, $newData);
            } elseif ($correction->type === 'overpayment_handling') {
                $correctionNote = app(HandleOverpaymentAction::class)->execute($order, $newData);
            }

            $correction->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'resolved_at' => now(),
            ]);

            $requesterName = $correction->requestedBy->name ?? 'System';
            $approverName = $user->name ?? 'System';
            $reqDate = Carbon::parse($correction->created_at)->format('d M Y, h:i A');
            $appDate = now()->format('d M Y, h:i A');
            $finalNote = ($correctionNote ?? "Correction approved ({$correction->type}).") . "\n[Requested by: {$requesterName} at {$reqDate} | Approved by: {$approverName} at {$appDate}]";

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => $user->id,
                'note' => $finalNote,
            ]);
        });

        if ($correction->requestedBy) {
            $notificationOrder = $correction->order ?? Order::withoutGlobalScopes()->find($correction->order_id);
            if ($notificationOrder) {
                Notification::send([$correction->requestedBy], new SystemAlertNotification("Correction Approved for Order #{$notificationOrder->order_number}", route('orders.show', $notificationOrder->order_number), 'success'));
            }
        }
    }
}

