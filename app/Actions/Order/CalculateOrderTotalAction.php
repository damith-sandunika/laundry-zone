<?php

namespace App\Actions\Order;

class CalculateOrderTotalAction
{
    /**
     * Calculate the subtotal and per-item totals of an order cart array, including turnaround and extras.
     *
     * @param array $cart The cart data containing items, base_price, qty, turnaround_type_id, selected_extras, etc.
     * @return array Returns ['subtotal' => float, 'cart' => array] where cart has calculated fields added.
     */
    public function execute(array $cart): array
    {
        $subtotal = 0;
        $calculatedCart = [];

        foreach ($cart as $item) {
            $qty = (float) ($item['qty'] ?? 1);
            $basePrice = (float) ($item['base_price'] ?? 0);
            
            $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
            $customTurnaroundPercent = null;
            
            if ($turnaroundTypeId === 'custom') {
                $turnaroundTypeId = null;
                $customTurnaroundPercent = isset($item['custom_turnaround_percentage']) ? (float) $item['custom_turnaround_percentage'] : null;
            }

            $itemSubtotalBeforeTurnaround = ($qty * $basePrice);
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

            $itemTotal = $itemSubtotalBeforeTurnaround + $turnaroundCharge;
            $selectedExtras = $item['selected_extras'] ?? [];
            
            if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                foreach ($selectedExtras as $selEx) {
                    $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                    $extraQty = is_array($selEx) && isset($selEx['qty']) ? (float)$selEx['qty'] : 1;
                    $extra = \App\Models\Extra::find($extraId);
                    if ($extra) {
                        $itemTotal += ($extraQty * $extra->price);
                    }
                }
            }
            $subtotal += $itemTotal;

            $item['calculated_qty'] = $qty;
            $item['calculated_base_price'] = $basePrice;
            $item['calculated_turnaround_type_id'] = $turnaroundTypeId;
            $item['calculated_custom_turnaround_percentage'] = $customTurnaroundPercent;
            $item['calculated_turnaround_charge'] = $turnaroundCharge;
            $item['calculated_item_total'] = $itemTotal;
            $item['calculated_selected_extras'] = $selectedExtras;
            
            $calculatedCart[] = $item;
        }

        return [
            'subtotal' => $subtotal,
            'cart' => $calculatedCart
        ];
    }
}
