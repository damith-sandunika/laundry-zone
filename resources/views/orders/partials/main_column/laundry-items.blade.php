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
                            @include('orders.partials.main_column.laundry_items_data')

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
                                        @if(!empty($row['note']))
                                            <div style="margin-top:4px;font-size:10px;color:#16a34a;background:#f0fdf4;padding:4px 8px;border-radius:6px;border:1px solid #bbf7d0;display:inline-flex;align-items:center;gap:4px;">
                                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                {{ $row['note'] }}
                                            </div>
                                        @endif
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
                        @include('orders.partials.main_column.laundry_items_footer')
                    </table>
                </div>
