<?php

namespace App\Actions\Workshop;

use App\Models\WorkshopDispatch;
use App\Models\OrderStatusHistory;

class ApproveWorkshopPaymentAction
{
    public function execute(WorkshopDispatch $dispatch, int $userId): void
    {
        $order = $dispatch->order;

        // IMPORTANT: Do NOT add to paid_amount here.
        // The customer already paid via bank transfer (submitPayment added it).
        // We are just APPROVING the payment and starting processing.
        $order->update([
            'payment_approved' => true,
        ]);

        // Update payment_status based on current paid_amount vs total
        $freshOrder = $order->fresh();
        if ($freshOrder->paid_amount >= $freshOrder->total) {
            $freshOrder->update(['payment_status' => 'paid']);
        } elseif ($freshOrder->paid_amount > 0) {
            $freshOrder->update(['payment_status' => 'partial']);
        }

        if (in_array($dispatch->status, ['dispatched_to_workshop', 'received_at_workshop'])) {
            $dispatch->update(['status' => 'processing']);
            $freshOrder->update(['status' => 'processing']);
            
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'processing',
                'changed_by' => $userId,
                'note' => 'Payment approved, processing started',
            ]);
        }
    }
}
