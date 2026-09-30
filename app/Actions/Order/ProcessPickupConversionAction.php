<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ProcessPickupConversionAction
{
    protected ConvertOrderToPickupAction $convertAction;

    public function __construct(ConvertOrderToPickupAction $convertAction)
    {
        $this->convertAction = $convertAction;
    }

    public function execute(Order $order, array $data, $user)
    {
        $deductAmount = (float)($data['deduct_amount'] ?? 0);
        $isAdmin = in_array($user->role, ['branch_admin', 'workshop_admin', 'super_admin']);

        if (!$isAdmin) {
            if ($order->correctionRequests()->where('status', 'pending')->exists()) {
                throw new \Exception('An active correction request already exists for this order.');
            }

            OrderCorrectionRequest::create([
                'order_id' => $order->id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'type' => 'convert_to_pickup',
                'reason' => 'Requested to convert order to Customer Pickup',
                'old_data' => [
                    'is_delivery_order' => true,
                    'delivery_fee' => $order->delivery_fee,
                    'total' => $order->total
                ],
                'new_data' => [
                    'deduct_amount' => $deductAmount
                ],
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => $user->id,
                'note' => "Correction requested (convert_to_pickup): Convert order to Customer Pickup",
            ]);

            $admins = User::whereIn('role', ['admin', 'branch_admin', 'workshop_admin'])
                ->where(function($q) use ($order) {
                    $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id');
                })->get();
            Notification::send($admins, new \App\Notifications\SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

            return ['status' => 'pending', 'message' => 'Conversion request submitted for admin approval.', 'overpayment' => 0];
        }

        DB::beginTransaction();
        try {
            $this->convertAction->execute($order, $data);
            
            $userName = $user->name ?? 'System';
            $nowDate = now()->format('d M Y, h:i A');
            
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $order->status,
                'note'       => 'Converted to Customer Pickup. Customer will collect from branch. Delivery fee deducted: Rs. ' . number_format($deductAmount, 2) . "\n[Requested by: {$userName} at {$nowDate} | Approved by: {$userName} at {$nowDate}]",
                'changed_by' => $user->id,
            ]);

            DB::commit();
            
            $order->refresh();
            $overpayment = round($order->paid_amount - $order->total, 2);

            return [
                'status' => 'approved', 
                'message' => 'Order converted to customer pickup.', 
                'overpayment' => $overpayment > 0 ? $overpayment : 0
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
