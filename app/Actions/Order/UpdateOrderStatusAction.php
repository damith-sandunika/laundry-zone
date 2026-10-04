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
            $order->status = $status;

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
                $dispatch = WorkshopDispatch::where('order_id', $order->id)
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
                $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                if ($appt && $appt->status !== 'ready') {
                    $appt->update(['status' => 'ready']);
                }
            }

            if ($status === 'delivered') {
                $order->delivered_at = now();
                if (isset($data['payment_method'])) {
                    $order->payment_method = $data['payment_method'];
                }
                if ($order->payment_method === 'credit') {
                    $order->payment_status = 'pending';
                } else {
                    $amountToPay = $order->total - $order->paid_amount;
                    if ($amountToPay > 0) {
                        PaymentTransaction::create([
                            'order_id'       => $order->id,
                            'amount'         => $amountToPay,
                            'payment_method' => $order->payment_method ?? 'cash',
                            'created_by'     => $userId,
                        ]);
                        $order->paid_amount += $amountToPay;
                    } elseif ($amountToPay < 0 && !empty($data['refund_overpayment'])) {
                        $refundAmount = abs($amountToPay);
                        PaymentTransaction::create([
                            'order_id'       => $order->id,
                            'amount'         => -$refundAmount,
                            'payment_method' => 'cash',
                            'created_by'     => $userId,
                            'status'         => 'completed',
                            'transaction_id' => 'REFUND-' . strtoupper(uniqid()),
                        ]);
                        $order->paid_amount -= $refundAmount;
                    }
                    $order->payment_status = 'paid';
                }

                $orderCubby = $order->cubbies()->whereNull('released_at')->first();
                if ($orderCubby) {
                    $orderCubby->update(['released_at' => now()]);
                    Cubby::where('id', $orderCubby->cubby_id)->update(['status' => 'empty']);
                }

                $appt = DeliveryAppointment::where('order_id', $order->id)->first();
                if ($appt) {
                    $appt->update(['status' => 'delivered']);
                }
            }

            if ($status === 'completed') {
                if (isset($data['payment_method'])) {
                    $order->payment_method = $data['payment_method'];
                }
                $amountToPay = $order->total - $order->paid_amount;
                if ($amountToPay > 0) {
                    PaymentTransaction::create([
                        'order_id'                => $order->id,
                        'amount'                  => $amountToPay,
                        'payment_method'          => $order->payment_method ?? 'cash',
                        'created_by'              => $userId,
                        'collected_from_rider_at' => now(),
                        'status'                  => 'completed',
                        'transaction_id'          => 'TXN-' . strtoupper(uniqid()),
                    ]);
                    if ($amountToPay > 0) {
                        $order->paid_amount += $amountToPay;
                    }
                    $order->payment_status = 'paid';
                } else {
                    PaymentTransaction::where('order_id', $order->id)
                        ->where('payment_method', 'cash')
                        ->whereNull('collected_from_rider_at')
                        ->update(['collected_from_rider_at' => now()]);
                }
            }

            $order->save();

            try {
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => $status,
                    'changed_by' => $userId,
                    'note'       => 'Status updated to ' . $status,
                ]);
            } catch (\Exception $e) {
                Log::warning('OrderStatusHistory insert failed: ' . $e->getMessage());
            }
        });

        \App\Jobs\SendOrderNotificationJob::dispatch($order, $status);
    }
}
