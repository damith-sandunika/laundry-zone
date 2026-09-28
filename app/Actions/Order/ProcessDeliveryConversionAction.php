<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderCorrectionRequest;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ProcessDeliveryConversionAction
{
    protected ConvertOrderToDeliveryAction $convertAction;

    public function __construct(ConvertOrderToDeliveryAction $convertAction)
    {
        $this->convertAction = $convertAction;
    }

    public function execute(Order $order, array $data, $user)
    {
        $fee = (float)($data['delivery_fee'] ?? 0);
        $isAdmin = in_array($user->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']);

        if (!$isAdmin) {
            if ($order->correctionRequests()->where('status', 'pending')->exists()) {
                throw new \Exception('An active correction request already exists for this order.');
            }

            OrderCorrectionRequest::create([
                'order_id' => $order->id,
                'requested_by' => $user->id,
                'status' => 'pending',
                'type' => 'convert_to_delivery',
                'reason' => 'Requested to convert order to Delivery',
                'old_data' => [
                    'is_delivery_order' => false,
                    'delivery_fee' => $order->delivery_fee
                ],
                'new_data' => [
                    'delivery_address' => $data['delivery_address'],
                    'delivery_fee' => $fee,
                    'contact_phone' => $data['contact_phone'] ?? null,
                ],
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'changed_by' => $user->id,
                'note' => "Correction requested (convert_to_delivery): Convert order to Delivery",
            ]);

            $admins = User::whereIn('role', ['admin', 'branch_admin', 'workshop_admin'])
                ->where(function($q) use ($order) {
                    $q->where('branch_id', $order->branch_id)->orWhereNull('branch_id');
                })->get();
            Notification::send($admins, new \App\Notifications\SystemAlertNotification("Correction Requested for Order #{$order->order_number}", route('orders.show', $order->order_number), 'warning'));

            return ['status' => 'pending', 'message' => 'Conversion request submitted for admin approval.'];
        }

        DB::beginTransaction();
        try {
            $this->convertAction->execute($order, $data);
            
            $userName = $user->name ?? 'System';
            $nowDate = now()->format('d M Y, h:i A');
            
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => "Converted to Delivery Order. Delivery Fee: Rs. " . number_format($fee, 2) . "\n[Requested by: {$userName} at {$nowDate} | Approved by: {$userName} at {$nowDate}]",
                'changed_by' => $user->id,
            ]);

            DB::commit();
            return ['status' => 'approved', 'message' => 'Converted to delivery successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
