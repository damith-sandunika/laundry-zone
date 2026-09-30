<?php

namespace App\Actions\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Actions\Order\CalculateOrderTotalAction;

class UpdateOrderItemsAction
{
    public function execute(Order $order, array $newData, string $type)
    {
        $newMethod = $order->payment_method;
        $oldMethod = $order->payment_method;

        if ($type === 'order_change' && isset($newData['payment_method'])) {
            $newMethod = $newData['payment_method'];
            $oldMethod = $order->payment_method;

            if ($newMethod === 'credit' && $oldMethod !== 'credit') {
                PaymentTransaction::where('order_id', $order->id)->delete();
                $order->update([
                    'payment_method' => $newMethod,
                    'paid_amount' => 0,
                    'payment_status' => 'unpaid'
                ]);
            } elseif ($oldMethod === 'credit' && $newMethod !== 'credit') {
                // Will create transaction at the end with correct new total
                $order->update([
                    'payment_method' => $newMethod,
                ]);
            } else {
                $order->update([
                    'payment_method' => $newMethod,
                ]);
                PaymentTransaction::where('order_id', $order->id)
                    ->where('payment_method', $oldMethod)
                    ->update(['payment_method' => $newMethod]);
            }
        }

        OrderItem::where('order_id', $order->id)->delete();
        
        $calcResult = app(CalculateOrderTotalAction::class)->execute($newData['cart']);
        $subtotal = $calcResult['subtotal'];
        $calculatedCart = $calcResult['cart'];
        
        foreach ($calculatedCart as $item) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'service_type_id' => $item['service_type_id'] ?? null,
                'item_description' => isset($item['service_type_id']) ? null : ($item['name'] ?? 'Custom Service'),
                'quantity' => $item['calculated_qty'],
                'unit_price' => $item['calculated_base_price'],
                'total_price' => $item['calculated_item_total'],
                'turnaround_type_id' => $item['calculated_turnaround_type_id'],
                'custom_turnaround_percentage' => $item['calculated_custom_turnaround_percentage'],
                'turnaround_extra_charge' => $item['calculated_turnaround_charge'],
                'note' => $item['note'] ?? null,
            ]);

            if (is_array($item['calculated_selected_extras']) && count($item['calculated_selected_extras']) > 0) {
                foreach ($item['calculated_selected_extras'] as $ex) {
                    $extraId = is_array($ex) ? ($ex['id'] ?? $ex) : $ex;
                    $extraQty = is_array($ex) && isset($ex['qty']) ? (float)$ex['qty'] : 1;
                    
                    $extra = \App\Models\Extra::find($extraId);
                    if ($extra) {
                        \App\Models\OrderItemExtra::create([
                            'order_item_id' => $orderItem->id,
                            'extra_id' => $extraId,
                            'quantity' => $extraQty,
                            'unit_price' => $extra->price,
                            'total_price' => $extra->price * $extraQty
                        ]);
                    }
                }
            }
        }

        $discountPercentage = isset($newData['discount_percentage']) ? (float) $newData['discount_percentage'] : ($order->customer ? $order->customer->default_discount : 0);
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $deliveryFee = isset($newData['delivery_fee']) ? (float) $newData['delivery_fee'] : $order->delivery_fee;
        $total = $subtotal - $discountAmount + $deliveryFee;
        
        $actualPaidAmount = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'completed')
            ->sum('amount');

        if ($type === 'order_change' && $oldMethod === 'credit' && $newMethod !== 'credit') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $total,
                'payment_method' => $newMethod,
                'status' => 'completed',
                'created_by' => auth()->id() ?? 1,
            ]);
            $actualPaidAmount = $total;
        }

        $paymentStatus = $actualPaidAmount >= $total ? 'paid' : ($actualPaidAmount > 0 ? 'partial' : 'unpaid');

        $order->update([
            'subtotal' => $subtotal,
            'discount' => $discountAmount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'paid_amount' => $actualPaidAmount,
            'payment_status' => $paymentStatus,
        ]);
        
        return "Correction approved ({$type}): Items and total recalculated. New Total: Rs. " . number_format($total, 2);
    }
}
