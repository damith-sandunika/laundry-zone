<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\Cubby;
use App\Models\OrderCubby;
use App\Models\WorkshopDispatch;
use App\Models\DeliveryAppointment;
use App\Models\PaymentTransaction;
use App\Models\OrderStatusHistory;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusAction
{
    public function execute(Order $order, array $data, int $userId): void
    {
        $status = $data['status'];
        $cubby_id = $data['cubby_id'] ?? null;

        DB::transaction(function () use ($order, $status, $cubby_id, $userId, $data) {
            // Lock order for update to prevent status update race conditions
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();
            if (!$lockedOrder) {
                throw new \Exception("Order #{$order->id} not found.");
            }

            $lockedOrder->status = $status;

            if ($status === 'done') {
                if ($cubby_id) {
                    OrderCubby::create([
                        'order_id'    => $order->id,
                        'cubby_id'    => $cubby_id,
                        'assigned_at' => now(),
                    ]);
                    Cubby::where('id', $cubby_id)->update(['status' => 'occupied']);
                }
            }

            if ($status === 'done') {
                $dispatch = WorkshopDispatch::where('order_id', $lockedOrder->id)
                    ->whereNotIn('status', ['received_at_branch'])
                    ->first();
                if ($dispatch) {
                    $dispatch->update([
                        'status'                => 'received_at_branch',
                        'received_at_branch_at' => now(),
                    ]);
                }
            }

            if ($status === 'done') {
                $appt = DeliveryAppointment::where('order_id', $lockedOrder->id)->first();
                if ($appt && $appt->status !== 'ready') {
                    $appt->update(['status' => 'ready']);
                }
            }

            if ($status === 'delivered') {
                $lockedOrder->delivered_at = now();
                if (isset($data['payment_method'])) {
                    $lockedOrder->payment_method = $data['payment_method'];
                }
                if ($lockedOrder->payment_method === 'credit') {
                    $lockedOrder->payment_status = 'pending';
                } else {
                    $amountToPay = $lockedOrder->total - $lockedOrder->paid_amount;
                    if ($amountToPay > 0) {
                        PaymentTransaction::create([
                            'order_id'       => $lockedOrder->id,
                            'amount'         => $amountToPay,
                            'payment_method' => $lockedOrder->payment_method ?? 'cash',
                            'created_by'     => $userId,
                        ]);
                        $lockedOrder->paid_amount += $amountToPay;
                    } elseif ($amountToPay < 0 && !empty($data['refund_overpayment'])) {
                        $refundAmount = abs($amountToPay);
                        PaymentTransaction::create([
                            'order_id'       => $lockedOrder->id,
                            'amount'         => -$refundAmount,
                            'payment_method' => 'cash',
                            'created_by'     => $userId,
                            'status'         => 'completed',
                            'transaction_id' => 'REFUND-' . strtoupper(uniqid()),
                        ]);
                        $lockedOrder->paid_amount -= $refundAmount;
                    }
                    $lockedOrder->payment_status = 'paid';
                }

                $orderCubby = $lockedOrder->cubbies()->whereNull('released_at')->first();
                if ($orderCubby) {
                    $orderCubby->update(['released_at' => now()]);
                    Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                }

                $appt = DeliveryAppointment::where('order_id', $lockedOrder->id)->first();
                if ($appt) {
                    $appt->update(['status' => 'delivered']);
                }
            }

            if ($status === 'completed') {
                if (isset($data['payment_method'])) {
                    $lockedOrder->payment_method = $data['payment_method'];
                }
                $amountToPay = $lockedOrder->total - $lockedOrder->paid_amount;
                if ($amountToPay > 0) {
                    PaymentTransaction::create([
                        'order_id'                => $lockedOrder->id,
                        'amount'                  => $amountToPay,
                        'payment_method'          => $lockedOrder->payment_method ?? 'cash',
                        'created_by'              => $userId,
                        'collected_from_rider_at' => now(),
                        'status'                  => 'completed',
                        'transaction_id'          => 'TXN-' . strtoupper(uniqid()),
                    ]);
                    if ($amountToPay > 0) {
                        $lockedOrder->paid_amount += $amountToPay;
                    }
                    $lockedOrder->payment_status = 'paid';
                } else {
                    PaymentTransaction::where('order_id', $lockedOrder->id)
                        ->where('payment_method', 'cash')
                        ->whereNull('collected_from_rider_at')
                        ->update(['collected_from_rider_at' => now()]);
                }
            }

            $lockedOrder->save();

            // Strict auditability: status history must be recorded inside the same transaction
            OrderStatusHistory::create([
                'order_id'   => $lockedOrder->id,
                'status'     => $status,
                'changed_by' => $userId,
                'note'       => 'Status updated to ' . $status,
            ]);

            // Sync original model attributes
            $order->status = $lockedOrder->status;
            $order->paid_amount = $lockedOrder->paid_amount;
            $order->payment_status = $lockedOrder->payment_status;
        }, 5);

        \App\Jobs\SendOrderNotificationJob::dispatch($order, $status);
    }
}
