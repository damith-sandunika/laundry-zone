                <!-- Laundry Items Card -->
                <div
                    style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);overflow:hidden;">
                    <div style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        <h3
                            style="font-size:12px;font-weight:800;color:#0f172a;margin:0;text-transform:uppercase;letter-spacing:0.05em;">
                            Laundry Items</h3>
                    </div>
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;">
                        <thead>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <th
                                    style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;">
                                    Service Type</th>
                                <th
                                    style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:center;">
                                    Qty / Weight</th>
                                <th
                                    style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:right;">
                                    Unit Price</th>
                                <th
                                    style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:right;">
                                    Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
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

                            @forelse($adminDisplayRows as $row)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 16px;font-weight:700;color:#0f172a;">
                                        @if(isset($row['category_name']) && $row['category_name'])
                                            <div style="font-size:9px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">
                                                {{ $row['category_name'] }}
                                            </div>
                                        @endif
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            {{ $row['name'] }}
                                            @if($row['turnaround_name'])
                                                <span
                                                    style="display:inline-block;font-size:9px;font-weight:800;padding:1px 6px;border-radius:4px;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;white-space:nowrap;">
                                                    ⚡ {{ $row['turnaround_name'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="padding:10px 16px;text-align:center;font-weight:700;color:#334155;">
                                        {{ $row['qty'] }}
                                    </td>
                                    <td style="padding:10px 16px;text-align:right;font-weight:600;color:#475569;">
                                        Rs.{{ number_format($row['unit_price'], 2) }}</td>
                                    <td style="padding:10px 16px;text-align:right;font-weight:800;color:#0f172a;">
                                        Rs.{{ number_format($row['total_price'], 2) }}</td>
                                </tr>
                                @if($row['turnaround_charge'] > 0)
                                    <tr style="border-bottom:1px solid #f1f5f9;background:#fffbeb;">
                                        <td style="padding:6px 16px 6px 32px;font-weight:600;color:#d97706;font-size:11px;">
                                            ⚡ {{ $row['turnaround_name'] ?? 'Express' }} Surcharge
                                            <span style="color:#94a3b8;font-weight:500;"> for {{ $row['name'] }}</span>
                                        </td>
                                        <td style="padding:6px 16px;text-align:center;font-size:11px;color:#d97706;">
                                            {{ $row['qty'] }}
                                        </td>
                                        <td style="padding:6px 16px;text-align:right;font-size:11px;color:#d97706;">
                                            Rs.{{ number_format($row['turnaround_charge'] / max(1, $row['qty']), 2) }}</td>
                                        <td
                                            style="padding:6px 16px;text-align:right;font-size:12px;font-weight:800;color:#d97706;">
                                            + Rs.{{ number_format($row['turnaround_charge'], 2) }}</td>
                                    </tr>
                                @endif
                                @foreach($row['extras'] as $e)
                                    <tr style="border-bottom:1px solid #f1f5f9;background:#f8faff;">
                                        <td style="padding:6px 16px 6px 32px;font-weight:600;color:#6366f1;font-size:11px;">+
                                            {{ $e['name'] }}
                                        </td>
                                        <td style="padding:6px 16px;text-align:center;font-size:11px;color:#6366f1;">
                                            {{ $e['qty'] }}
                                        </td>
                                        <td style="padding:6px 16px;text-align:right;font-size:11px;color:#6366f1;">
                                            Rs.{{ number_format($e['price'], 2) }}</td>
                                        <td
                                            style="padding:6px 16px;text-align:right;font-size:12px;font-weight:800;color:#6366f1;">
                                            Rs.{{ number_format($e['price'] * $e['qty'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="4"
                                        style="padding:20px 16px;text-align:center;color:#94a3b8;font-size:12px;font-weight:700;">
                                        No items added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"
                                    style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">
                                    Subtotal</td>
                                <td style="padding:8px 16px;text-align:right;font-weight:700;color:#334155;">
                                    Rs.{{ number_format($cartSubtotal, 2) }}</td>
                            </tr>
                            @if($order->additional > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:12px;font-weight:700;color:#64748b;">
                                        Additional Charges</td>
                                    <td
                                        style="padding:8px 16px;text-align:right;font-size:13px;font-weight:800;color:#dc2626;">
                                        + Rs.{{ number_format($order->additional, 2) }}</td>
                                </tr>
                            @endif
                            @if($order->delivery_fee > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:12px;font-weight:700;color:#64748b;">
                                        Delivery Fee</td>
                                    <td
                                        style="padding:8px 16px;text-align:right;font-size:13px;font-weight:800;color:#0f172a;">
                                        + Rs.{{ number_format($order->delivery_fee, 2) }}</td>
                                </tr>
                            @endif
                            @if($order->discount > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">
                                        Discount</td>
                                    <td style="padding:8px 16px;text-align:right;font-weight:700;color:#dc2626;">-
                                        Rs.{{ number_format($order->discount, 2) }}</td>
                                </tr>
                            @endif
                            <tr style="background:#f8fafc;border-top:1.5px solid #e2e8f0;">
                                <td colspan="3"
                                    style="padding:12px 16px;text-align:right;font-size:12px;font-weight:800;color:#0f172a;">
                                    Total</td>
                                @php
                                    $displayTotal = $order->total;
                                    if ($order->items->count() === 0 && $cartSubtotal > 0) {
                                        $displayTotal = $cartSubtotal + ($order->delivery_fee ?? 0) - ($order->discount ?? 0);
                                    }
                                @endphp
                                <td
                                    style="padding:12px 16px;text-align:right;font-size:15px;font-weight:900;color:#1d4ed8;">
                                    {{ $order->isPackageOrder() && $displayTotal == 0 ? 'Package' : 'Rs.' . number_format($displayTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>


            </div>
