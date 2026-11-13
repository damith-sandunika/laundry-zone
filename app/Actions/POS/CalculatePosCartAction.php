<?php

namespace App\Actions\POS;

use App\Models\CustomerPackage;
use App\Models\TurnaroundType;
use App\Models\Extra;

class CalculatePosCartAction
{
    public function execute(array $cart, ?int $packageId): array
    {
        $activePackage = null;
        $packageServices = collect();

        if ($packageId) {
            $activePackage = CustomerPackage::with('services')->find($packageId);
            if ($activePackage && $activePackage->status === 'active') {
                $packageServices = $activePackage->services->keyBy('service_type_id');
            } else {
                $packageId = null;
            }
        }

        $processedCart = [];
        $subtotal = 0;
        $packageDeductions = [];

        foreach ($cart as $item) {
            $serviceTypeId = $item['service_type_id'] ?? $item['id'];
            $qty = (float) $item['qty'];
            $basePrice = (float) $item['base_price'];

            $turnaroundTypeId = !empty($item['turnaround_type_id']) ? $item['turnaround_type_id'] : null;
            $customTurnaroundPercent = null;
            if ($turnaroundTypeId === 'custom') {
                $turnaroundTypeId = null;
                $customTurnaroundPercent = isset($item['custom_turnaround_percentage']) ? (float) $item['custom_turnaround_percentage'] : null;
            }
            $selectedExtras = $item['selected_extras'] ?? [];

            $coveredQty = 0;
            $chargeableQty = $qty;

            if ($packageId) {
                if (empty($item['is_special']) && $packageServices->has($serviceTypeId)) {
                    $ps = $packageServices->get($serviceTypeId);
                    $remaining = max(0, $ps->total_quantity - $ps->used_quantity);

                    if ($qty > $remaining) {
                        throw new \Exception("Cannot process order. Requested quantity ({$qty}) for '{$item['name']}' exceeds the remaining package limit ({$remaining}).");
                    }

                    $coveredQty = $qty;
                    $chargeableQty = 0;
                    $packageDeductions[$ps->id] = $coveredQty;
                } else {
                    throw new \Exception("Cannot process order. '{$item['name']}' is not covered by the selected package.");
                }
            }

            $itemSubtotalBeforeTurnaround = ($chargeableQty * $basePrice);
            $turnaroundCharge = 0;

            if ($customTurnaroundPercent !== null) {
                $turnaroundCharge = $itemSubtotalBeforeTurnaround * ($customTurnaroundPercent / 100);
            } elseif ($turnaroundTypeId) {
                $tt = TurnaroundType::find($turnaroundTypeId);
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

            $cartItem = [
                'service_type_id' => $serviceTypeId,
                'name' => $item['name'],
                'qty' => $qty,
                'base_price' => $basePrice,
                'covered_qty' => $coveredQty,
                'chargeable_qty' => $chargeableQty,
                'total_price' => $itemSubtotal,
                'is_special' => $item['is_special'] ?? false,
                'turnaround_type_id' => $turnaroundTypeId,
                'custom_turnaround_percentage' => $customTurnaroundPercent,
                'turnaround_extra_charge' => $turnaroundCharge,
                'selected_extras' => [],
                'note' => $item['note'] ?? null,
            ];

            if (is_array($selectedExtras) && count($selectedExtras) > 0) {
                foreach ($selectedExtras as $selEx) {
                    $extraId = is_array($selEx) ? ($selEx['id'] ?? $selEx) : $selEx;
                    $extraQty = is_array($selEx) && isset($selEx['qty']) ? $selEx['qty'] : 1;

                    $extra = Extra::find($extraId);
                    if ($extra) {
                        $extraSubtotal = $extra->price * $extraQty;
                        $subtotal += $extraSubtotal;

                        $cartItem['selected_extras'][] = [
                            'id' => $extra->id,
                            'qty' => $extraQty,
                            'price' => $extra->price,
                            'total' => $extraSubtotal
                        ];
                    }
                }
            }

            $processedCart[] = $cartItem;
        }

        return [
            'processedCart' => $processedCart,
            'subtotal' => $subtotal,
            'packageId' => $packageId,
            'activePackage' => $activePackage,
            'packageDeductions' => $packageDeductions,
        ];
    }
}
