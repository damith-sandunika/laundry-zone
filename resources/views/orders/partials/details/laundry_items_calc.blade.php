@php
    // Fallback to cart_json if no order items yet (unfinalized delivery order)
    $adminDisplayRows = collect();
    $cartSubtotal = $order->subtotal;

    if ($order->items->count() > 0) {
        foreach ($order->items as $item) {
            $unitWithTurnaround = $item->quantity > 0 ? ($item->total_price / $item->quantity) : $item->total_price;
            $stId = $item->service_type_id;
            $stInfo = $stId && isset($serviceTypesMap[$stId]) ? $serviceTypesMap[$stId] : null;
            $catName = $stInfo ? $stInfo['category'] : null;
            $subCatName = $stInfo ? $stInfo['subcategory'] : null;
            $catStr = $catName ? ($catName . ($subCatName ? ' › ' . $subCatName : '')) : null;

            $adminDisplayRows->push([
                'type' => 'item',
                'category_name' => $catStr,
                'name' => $item->serviceType ? $item->serviceType->name : $item->item_description,
                'turnaround_name' => ($item->turnaroundType && !$item->turnaroundType->is_default) ? $item->turnaroundType->name : ((float)$item->custom_turnaround_percentage > 0 ? 'Express ('.(float)$item->custom_turnaround_percentage.'%)' : null),
                'extras' => $item->extras->map(fn($e) => ['name' => optional($e->extra)->name, 'qty' => $e->quantity, 'price' => $e->unit_price]),
                'qty' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->unit_price * max(1, $item->quantity),
                'turnaround_charge' => $item->turnaround_extra_charge ?? 0,
            ]);
        }
    } elseif ($order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
        $cartArr = json_decode($order->deliveryAppointment->cart_json, true) ?? [];
        $calcSub = 0;
        foreach ($cartArr as $ci) {
            $svc = \App\Models\ServiceType::find($ci['service_type_id'] ?? $ci['id'] ?? null);
            $svcName = $ci['name'] ?? ($svc ? $svc->name : 'Service');
            $qty = (float) ($ci['qty'] ?? 1);
            $price = (float) ($ci['base_price'] ?? 0);
            $lineTotal = $price * $qty;

            // Turnaround
            $ttId = $ci['turnaround_type_id'] ?? null;
            $ttName = null;
            $ttCharge = 0;
            if ($ttId) {
                $tt = \App\Models\TurnaroundType::find($ttId);
                if ($tt && !$tt->is_default) {
                    $ttName = $tt->name;
                    if ($tt->markup_type === 'percentage') {
                        $ttCharge = $lineTotal * ($tt->markup_value / 100);
                    } else {
                        $ttCharge = $tt->markup_value * $qty;
                    }
                }
            }

            // Extras
            $extras = [];
            foreach ($ci['selected_extras'] ?? [] as $se) {
                $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                $ex = \App\Models\Extra::find($exId);
                if ($ex) {
                    $extras[] = ['name' => $ex->name, 'qty' => $exQty, 'price' => $ex->price];
                }
            }

            $calcSub += $lineTotal + $ttCharge + array_sum(array_column($extras, 'price'));

            $stId = $ci['service_type_id'] ?? $ci['id'] ?? null;
            $stInfo = $stId && isset($serviceTypesMap[$stId]) ? $serviceTypesMap[$stId] : null;
            $catName = $stInfo ? $stInfo['category'] : null;
            $subCatName = $stInfo ? $stInfo['subcategory'] : null;
            $catStr = $catName ? ($catName . ($subCatName ? ' › ' . $subCatName : '')) : null;

            $adminDisplayRows->push([
                'type' => 'item',
                'category_name' => $catStr,
                'name' => $ci['name'] ?? ($svc ? $svc->name : 'Service'),
                'turnaround_name' => $ttName,
                'extras' => collect($extras),
                'qty' => $qty,
                'unit_price' => $price,
                'total_price' => $lineTotal,
                'turnaround_charge' => $ttCharge,
            ]);
        }
        $cartSubtotal = $calcSub;
    }
@endphp
