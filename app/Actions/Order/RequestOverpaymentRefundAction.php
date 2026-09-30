<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;

class RequestOverpaymentRefundAction
{
    public function execute(Order $order, string $action, int $userId): array
    {
        $overpayment = round($order->paid_amount - $order->total, 2);

        if ($overpayment <= 0) {
            return ['success' => false, 'message' => 'No overpayment exists on this order.'];
        }

        if ($order->correctionRequests()->where('status', 'pending')->where('type', 'overpayment_handling')->exists()) {
            return ['success' => false, 'message' => 'An overpayment request is already pending.'];
        }

        $user = \App\Models\User::find($userId);
        $isAdmin = in_array($user->role, ['branch_admin', 'workshop_admin', 'super_admin']);

        DB::beginTransaction();

        try {
            $correction = OrderCorrectionRequest::create([
                'order_id' => $order->id,
                'requested_by' => $userId,
                'status' => 'pending',
                'type' => 'overpayment_handling',
                'reason' => 'Overpayment refund/store credit requested',
                'old_data' => [],
                'new_data' => [
                    'action' => $action,
                    'amount' => $overpayment
                ],
            ]);

            DB::commit();

            if ($isAdmin) {
                app(\App\Http\Controllers\OrderCorrectionController::class)->approve($correction);
                
                return [
                    'success' => true,
                    'message' => 'Overpayment handled successfully.'
                ];
            }

            $actionText = $action === 'refund' ? 'Refund' : 'Keep as Store Credit';
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $order->status,
                'note'       => "Overpayment action requested: {$actionText} for Rs. " . number_format($overpayment, 2),
                'changed_by' => $userId,
            ]);

            return [
                'success' => true,
                'message' => 'Request submitted for admin approval.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
