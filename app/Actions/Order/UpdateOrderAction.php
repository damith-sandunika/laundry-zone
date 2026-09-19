<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Services\CommunicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateOrderAction
{
    public function execute(Order $order, array $data, int $userId): void
    {
        $cart = $data['cart'];

        DB::transaction(function () use ($order, $data, $cart, $userId) {
            OrderItem::where('order_id', $order->id)->delete();

            $subtotal = 0;
            $processedCart = [];

            foreach ($cart as $item) {
                $qty = (float) ($item['qty'] ?? 1);
                if ($qty <= 0) {
                    continue;
                }

                $basePrice = (float) ($item['base_price'] ?? 0);
                $itemSubtotalBeforeTurnaround = $qty * $basePrice;

                // Turnaround Charge
                $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
                $customTurnaroundPercent = null;
                if ($turnaroundTypeId === 'custom') {
                    $turnaroundTypeId = null;
                    $customTurnaroundPercent = isset($item['custom_turnaround_percentage']) ? (float) $item['custom_turnaround_percentage'] : null;
                }
                $turnaroundCharge = 0;
                
                if ($customTurnaroundPercent !== null) {
                    $turnaroundCharge = $itemSubtotalBeforeTurnaround * ($customTurnaroundPercent / 100);
                } else if ($turnaroundTypeId) {
                    $tt = \App\Models\TurnaroundType::find($turnaroundTypeId);
                    if ($tt) {
                        if ($tt->markup_type === 'percentage') {
                            $turnaroundCharge = $itemSubtotalBeforeTurnaround * ($tt->markup_value / 100);
                        } else {
                            $turnaroundCharge = $tt->markup_value * $qty;
                        }
                    }
                }

                $itemSubtotal = $itemSubtotalBeforeTurnaround + $turnaroundCharge;
                $subtotal += $itemSubtotal;
                
                $selectedExtras = $item['selected_extras'] ?? [];

                $processedCart[] = [
                    'service_type_id' => $item['service_type_id'] ?? null,
                    'item_description' => $item['name'] ?? 'Custom Service',
                    'quantity' => $qty,
                    'unit_price' => $basePrice,
                    'total_price' => $itemSubtotal,
                    'turnaround_type_id' => $turnaroundTypeId,
                    'custom_turnaround_percentage' => $customTurnaroundPercent,
                    'turnaround_extra_charge' => $turnaroundCharge,
                    'selected_extras' => $selectedExtras,
                ];
                
                if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                    foreach ($selectedExtras as $selEx) {
                        $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                        $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : $qty;
                        
                        $extra = \App\Models\Extra::find($extraId);
                        if ($extra) {
                            $subtotal += ($extra->price * $extraQty);
                        }
                    }
                }
            }

            $discountPercentage = max(0, min(100, (float) ($data['discount_percentage'] ?? 0)));
            $discountAmount = $subtotal * ($discountPercentage / 100);
            
            if ($discountPercentage > 0 && $order->customer_id) {
                \App\Models\Customer::where('id', $order->customer_id)
                    ->update(['default_discount' => $discountPercentage]);
            }

            $deliveryFee = isset($data['delivery_fee']) ? (float)$data['delivery_fee'] : $order->delivery_fee;
            $total = $subtotal - $discountAmount + $deliveryFee;

            $paidAmount = $order->paid_amount;
            if ($paidAmount > $total) {
                $paidAmount = $total;
            }

            $order->update([
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'requested_advance' => $data['requested_advance'] ?? $order->requested_advance,
            ]);

            if ($order->delivery_appointment_id) {
                \App\Models\DeliveryAppointment::where('id', $order->delivery_appointment_id)->update([
                    'delivery_cost' => $deliveryFee,
                    'cart_json' => json_encode($processedCart)
                ]);
            }

            foreach ($processedCart as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'service_type_id' => $item['service_type_id'],
                    'item_description' => $item['service_type_id'] ? null : $item['item_description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'turnaround_type_id' => $item['turnaround_type_id'],
                    'custom_turnaround_percentage' => $item['custom_turnaround_percentage'] ?? null,
                    'turnaround_extra_charge' => $item['turnaround_extra_charge'] ?? 0,
                ]);
                
                $selectedExtras = $item['selected_extras'];
                if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                    foreach ($selectedExtras as $selEx) {
                        $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                        $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : $item['quantity'];
                        
                        $extra = \App\Models\Extra::find($extraId);
                        if ($extra) {
                            \App\Models\OrderItemExtra::create([
                                'order_item_id' => $orderItem->id,
                                'extra_id' => $extraId,
                                'quantity' => $extraQty,
                                'unit_price' => $extra->price,
                                'total_price' => $extra->price * $extraQty,
                            ]);
                        }
                    }
                }
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'edited',
                'changed_by' => $userId,
                'note' => 'Order details updated',
            ]);

            if (isset($data['next_action'])) {
                if ($data['next_action'] === 'start_processing') {
                    $order->update(['status' => 'processing']);
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'processing',
                        'changed_by' => $userId,
                        'note' => 'Workshop started processing',
                    ]);
                } else if ($data['next_action'] === 'dispatch_to_workshop') {
                    $order->update(['status' => 'dispatched_to_workshop']);
                    
                    $workshop = \App\Models\Branch::workshops()->first();
                    if ($workshop) {
                        \App\Models\WorkshopDispatch::create([
                            'order_id' => $order->id,
                            'branch_id' => $order->branch_id,
                            'workshop_branch_id' => $workshop->id,
                            'status' => 'dispatched_to_workshop',
                            'dispatched_by' => $userId,
                            'dispatch_notes' => 'Dispatched from branch after confirmation',
                        ]);
                    }

                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => 'dispatched_to_workshop',
                        'changed_by' => $userId,
                        'note' => 'Branch verified and dispatched order to workshop',
                    ]);
                } else if ($data['next_action'] === 'request_payment') {
                    $commService = new CommunicationService();
                    $commService->sendFinalizedBill($order);
                }
            }

            if (isset($data['advance_cash_amount']) && $data['advance_cash_amount'] > 0) {
                $advance = (float) $data['advance_cash_amount'];
                \App\Models\PaymentTransaction::create([
                    'order_id' => $order->id,
                    'amount' => $advance,
                    'payment_method' => 'cash',
                    'status' => 'completed',
                    'collected_by' => $userId,
                ]);
                $order->increment('paid_amount', $advance);
                
                if ($order->paid_amount >= $order->total) {
                    $order->update(['payment_status' => 'paid']);
                } else {
                    $order->update(['payment_status' => 'partial']);
                }
            }
        });
    }
}
