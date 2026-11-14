@php
    $displayItems = collect();
    if ($order->items->count() > 0) {
        foreach ($order->items as $item) {
            $extrasArr = [];
            $extrasTotal = 0;
            foreach ($item->extras as $extra) {
                $extTotal = $extra->total_price ?? ($extra->unit_price * $extra->quantity) ?? 0;
                $extrasTotal += $extTotal;
                $extrasArr[] = (object)[
                    'name' => $extra->extra->name ?? 'Extra',
                    'quantity' => $extra->quantity,
                    'price' => $extra->unit_price ?? $extra->price,
                    'total' => $extTotal
                ];
            }
            
            $base_total = $item->unit_price * $item->quantity;
            $turnaround_charge = $item->total_price - $base_total;
            $base_total = ($item->unit_price * $item->quantity) + $turnaround_charge;
            
            $mainCategoryName = null;
            $subCategoryName = null;
            if ($item->serviceType && $item->serviceType->category) {
                $cat = $item->serviceType->category;
                if ($cat->parent) {
                    $mainCategoryName = $cat->parent->name;
                    $subCategoryName = $cat->name;
                } else {
                    $mainCategoryName = $cat->name;
                }
            }

            $displayItems->push((object)[
                'name' => $item->serviceType->name ?? ($item->item_description ?? 'Service'),
                'main_category' => $mainCategoryName,
                'sub_category' => $subCategoryName,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'base_total' => $base_total,
                'extras' => $extrasArr,
                'extras_total' => $extrasTotal,
                'total_price' => $item->total_price + $extrasTotal,
                'turnaround_name' => ($item->turnaroundType && !$item->turnaroundType->is_default) ? $item->turnaroundType->name : ((float)$item->custom_turnaround_percentage > 0 ? 'Express ('.(float)$item->custom_turnaround_percentage.'%)' : null),
                'turnaround_charge' => $turnaround_charge > 0 ? $turnaround_charge : 0,
            ]);
        }
    } else if ($order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
        $cartArr = json_decode($order->deliveryAppointment->cart_json, true) ?? [];
        foreach ($cartArr as $cartItem) {
            $qty = (float)($cartItem['qty'] ?? 1);
            $price = (float)($cartItem['base_price'] ?? 0);
            
            $ttName = null;
            $ttCharge = 0;
            $ttId = $cartItem['turnaround_type_id'] ?? null;
            if ($ttId) {
                $tt = \App\Models\TurnaroundType::find($ttId);
                if ($tt && !$tt->is_default) {
                    $ttName = $tt->name;
                    $lineTotal = $price * $qty;
                    if ($tt->markup_type === 'percentage') {
                        $ttCharge = $lineTotal * ($tt->markup_value / 100);
                    } else {
                        $ttCharge = $tt->markup_value * $qty;
                    }
                }
            }
            
            $extrasArr = [];
            $extrasTotal = 0;
            foreach ($cartItem['selected_extras'] ?? [] as $se) {
                $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                $ex = \App\Models\Extra::find($exId);
                if ($ex) {
                    $exTotal = $ex->price * $exQty;
                    $extrasTotal += $exTotal;
                    $extrasArr[] = (object)[
                        'name' => $ex->name,
                        'quantity' => $exQty,
                        'price' => $ex->price,
                        'total' => $exTotal
                    ];
                }
            }
            
            $base_total = ($price * $qty) + $ttCharge;
            $total_price_with_extras = $base_total + $extrasTotal;

            $mainCategoryName = null;
            $subCategoryName = null;
            $service = \App\Models\ServiceType::find($cartItem['id'] ?? null);
            if ($service && $service->category) {
                $cat = $service->category;
                if ($cat->parent) {
                    $mainCategoryName = $cat->parent->name;
                    $subCategoryName = $cat->name;
                } else {
                    $mainCategoryName = $cat->name;
                }
            }

            $displayItems->push((object)[
                'name' => $cartItem['name'] ?? 'Service',
                'main_category' => $mainCategoryName,
                'sub_category' => $subCategoryName,
                'quantity' => $qty,
                'unit_price' => $price,
                'base_total' => $base_total,
                'extras' => $extrasArr,
                'extras_total' => $extrasTotal,
                'total_price' => $total_price_with_extras,
                'turnaround_name' => $ttName,
                'turnaround_charge' => $ttCharge,
            ]);
        }
    }
@endphp

