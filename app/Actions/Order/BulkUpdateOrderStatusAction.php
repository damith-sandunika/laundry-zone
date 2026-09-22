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

class BulkUpdateOrderStatusAction
{
    public function execute(array $data, int $userId): void
    {
        $orderIds = $data['order_ids'];
        $status = $data['status'];
        $cubby_id = $data['cubby_id'] ?? null;

        $stateOrder = [
            'received' => 1,
            'dispatched_to_workshop' => 2,
            'processing' => 3,
            'returned_from_workshop' => 4,
            'done' => 5,
            'delivered' => 6,
            'cancelled' => 7,
        ];

        try {
            DB::transaction(function () use ($orderIds, $status, $cubby_id, $userId, $stateOrder) {
                Order::with('customer', 'cubbies')->whereIn('id', $orderIds)->chunkById(100, function ($orders) use ($status, $cubby_id, $userId, $stateOrder) {
                    foreach ($orders as $order) {
                        if ($status !== 'cancelled') {
                            if (($stateOrder[$status] ?? 0) <= ($stateOrder[$order->status] ?? 0)) {
                                continue;
                            }
                        }

                        $order->status = $status;

                        if ($status === 'done') {
                            if ($cubby_id) {
                                $oldCubby = $order->cubbies()->whereNull('released_at')->first();
                                if ($oldCubby && $oldCubby->cubby_id != $cubby_id) {
                                    $oldCubby->update(['released_at' => now()]);
                                    Cubby::where('id', $oldCubby->cubby_id)->update(['status' => 'empty']);
                                }

                                if (! $oldCubby || $oldCubby->cubby_id != $cubby_id) {
                                    OrderCubby::create([
                                        'order_id' => $order->id,
                                        'cubby_id' => $cubby_id,
                                        'assigned_at' => now(),
                                    ]);
                                }

                                Cubby::where('id', $cubby_id)->update(['status' => 'occupied']);
                            }

                            $dispatch = WorkshopDispatch::where('order_id', $order->id)
                                ->whereNotIn('status', ['received_at_branch'])
                                ->first();
                            if ($dispatch) {
                                $dispatch->update([
                                    'status' => 'received_at_branch',
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
                            if ($order->payment_method === 'credit') {
                                $order->payment_status = 'pending';
                            } else {
                                $amountToPay = $order->total - $order->paid_amount;
                                if ($amountToPay > 0) {
                                    PaymentTransaction::create([
                                        'order_id' => $order->id,
                                        'amount' => $amountToPay,
                                        'payment_method' => $order->payment_method ?? 'cash',
                                        'created_by' => $userId,
                                    ]);
                                }
                                $order->payment_status = 'paid';
                                if ($amountToPay > 0) {
                                    $order->paid_amount += $amountToPay;
                                }
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

                        $order->save();

                        OrderStatusHistory::create([
                            'order_id' => $order->id,
                            'status' => $status,
                            'changed_by' => $userId,
                        ]);
                    }
                });
            });

            Log::info("Bulk update status completed.", ['count' => count($orderIds), 'status' => $status, 'user_id' => $userId]);

            Order::whereIn('id', $orderIds)->chunkById(100, function ($orders) use ($status) {
                foreach ($orders as $order) {
                    $this->triggerNotifications($order, $status);
                }
            });

        } catch (\Throwable $e) {
            Log::error("Bulk Update Order Status failed: " . $e->getMessage(), [
                'order_ids' => $orderIds,
                'status' => $status,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function triggerNotifications(Order $order, string $status)
    {
        try {
            $commService = new CommunicationService();
            $dispatch = WorkshopDispatch::where('order_id', $order->id)->first();
            $isCollection = false;
            
            if (! $order->is_delivery_order && $dispatch && $dispatch->branch_id !== $dispatch->workshop_branch_id) {
                $isCollection = true;
            }

            if ($status === 'done' && $isCollection) {
                $commService->sendCollectionOrderReady($order);
            } elseif ($status === 'delivered' || $status === 'completed') {
                if ($order->is_delivery_order) {
                    $commService->sendDeliveryCompleted($order);
                } elseif ($isCollection) {
                    $commService->sendCollectionOrderCompleted($order);
                } else {
                    $commService->sendPosWalkinCompleted($order);
                }
            }
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }
    }
}
