                <!-- RETURNS TABLE -->
                @if($currentTab === 'returns')
                <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">ID & Customer</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Delivery Address</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Payment Info</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status & Rider</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    @if($appt->order_id && $appt->order)
                                        <a href="{{ route('orders.show', $appt->order->order_number) }}" style="font-size:13px;font-weight:800;color:{{ $appt->status === 'cancelled' ? '#dc2626;text-decoration:line-through;' : '#1d4ed8;' }};text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">#{{ $appt->order->order_number ?? $appt->id }}</a>
                                    @else
                                        <div style="font-size:13px;font-weight:800;color:{{ $appt->status === 'cancelled' ? '#dc2626;text-decoration:line-through;' : '#1d4ed8;' }}">#{{ $appt->id }}</div>
                                    @endif
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-weight:700;color:#0f172a;">{{ $appt->customer->name ?? 'Unknown' }}</span>
                                        <span style="font-size:11px;color:#64748b;font-weight:600;">{{ $appt->contact_phone }}</span>
                                    </div>
                                    @if($appt->order_id && $appt->order)
                                        <div style="margin-top:4px;font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;">
                                            Order: ORD-{{ str_pad($appt->order_id, 4, '0', STR_PAD_LEFT) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;">
                                    <div style="font-weight:600;color:#334155;">{{ $appt->delivery_address ?? 'Same as pickup address' }}</div>
                                </td>
                                <td style="padding:12px 20px;">
                                    @php 
                                        $order = $appt->order; 
                                        $pendingCashTx = false;
                                        $pendingCashAmount = 0;
                                        if ($order) {
                                            $pendingCashAmount = \App\Models\PaymentTransaction::where('order_id', $order->id)
                                                ->where('payment_method', 'cash')
                                                ->whereNull('collected_from_rider_at')
                                                ->whereNotNull('created_by')
                                                ->sum('amount');
                                            $pendingCashTx = $pendingCashAmount > 0;
                                        }
                                    @endphp
                                    @if($order)
                                        <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;
                                            {{ $order->payment_status === 'paid' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' }}
                                        ">
                                            {{ $order->payment_status === 'paid' ? 'Paid' : 'Unpaid (Rs.'.number_format($order->total - $order->paid_amount, 2).')' }}
                                        </span>
                                        @if($pendingCashTx)
                                            <div style="margin-top:6px;">
                                                <span style="font-size:10px;font-weight:800;color:#d97706;background:#fef3c7;padding:2px 6px;border-radius:4px;border:1px solid #fde68a;">⏳ Waiting for Rs. {{ number_format($pendingCashAmount, 2) }} Cash</span>
                                            </div>
                                        @endif
                                    @else
                                        <span style="font-size:11px;color:#94a3b8;font-weight:600;">No Info</span>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;">
                                    <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;
                                        {{ $appt->status === 'ready' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' : '' }}
                                        {{ $appt->status === 'out_for_delivery' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' : '' }}
                                        {{ $appt->status === 'delivered' ? 'background:#f3f4f6;color:#475569;border:1px solid #e2e8f0;' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', $appt->status) }}
                                    </span>
                                    @php $riderTask = $appt->deliveryTasks->where('task_type', 'delivery')->last(); @endphp
                                    @if($appt->delivery_method === 'pickme_flash')
                                        <div style="margin-top:4px;font-size:11px;font-weight:800;color:#eab308;">
                                            PickMe/Flash: Rs. {{ number_format($appt->delivery_cost ?? 0, 2) }}
                                        </div>
                                    @elseif($riderTask)
                                        <div style="margin-top:4px;font-size:11px;font-weight:700;color:#64748b;">
                                            Rider: {{ $riderTask->rider->name ?? 'Unknown' }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;text-align:right;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;">
                                        <button type="button" data-appt="{{ json_encode(['customer' => $appt->customer, 'order' => $appt->order, 'branch' => $appt->branch, 'delivery_preference' => $appt->delivery_preference, 'pickup_lat' => $appt->latitude, 'pickup_lng' => $appt->longitude, 'delivery_lat' => $appt->delivery_lat, 'delivery_lng' => $appt->delivery_lng, 'pickup_address' => $appt->pickup_address, 'delivery_address' => $appt->delivery_address, 'pickup_scheduled_at' => $appt->pickup_scheduled_at, 'delivery_scheduled_at' => $appt->delivery_scheduled_at, 'notes' => $appt->special_notes ?? '', 'item_description' => $appt->item_description ?? '', 'cart_json' => formatCartForPreview($appt->cart_json)]) }}" @click="openPreview($event.currentTarget.dataset.appt)" style="padding:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;color:#3b82f6;cursor:pointer;" title="Preview Order"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>
                                        @if($appt->status === 'ready')
                                            <form method="POST" action="{{ route('delivery.assignDeliveryRider', ['appointment' => $appt->id]) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $appt->order_id }}">
                                                <select name="rider_id" required onchange="this.parentElement.querySelector('.pickme_fields').style.display = this.value === 'pickme_flash' ? 'block' : 'none';" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;">
                                                    <option value="pickme_flash">PickMe / Flash</option>
                                                    <option value="">Select Rider</option>
                                                    @foreach($riders as $rider)
                                                        <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="pickme_fields" style="display:block; margin-top:4px;">
                                                    <input type="number" name="pickme_flash_cost" placeholder="Cost (Rs)" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;width:70px; margin-right: 4px;">
                                                    @if($order && $order->paid_amount < $order->total)
                                                    <div style="margin-top:4px;font-size:10px;font-weight:700;color:#334155;">Balance: Rs. {{ number_format($order->total - $order->paid_amount, 2) }}</div>
                                                    <select name="payment_method" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;width:90px;margin-top:4px;">
                                                        <option value="cash">Cash</option>
                                                        <option value="card">Card</option>
                                                        <option value="bank_transfer">Bank</option>
                                                    </select>
                                                    @endif
                                                </div>
                                                <button type="submit" style="padding:5px 10px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(79,70,229,0.3);">
                                                    Assign
                                                </button>
                                            </form>
                                            @if(session('print_url'))
                                                <script>
                                                    window.open("{{ session('print_url') }}", "_blank");
                                                </script>
                                            @endif
                                        @elseif($appt->status === 'out_for_delivery')
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600;">Awaiting Rider</span>
                                        @elseif($appt->status === 'delivered')
                                            @if($pendingCashTx)
                                                <button type="button" @click="openCashModal({{ $appt->id }}, '{{ $order->order_number }}', {{ $order->total }}, {{ max(0, $order->paid_amount - $pendingCashAmount) }}, {{ $pendingCashAmount }})" style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(234,179,8,0.3);">
                                                    Cash Received
                                                </button>
                                            @else
                                                <span style="font-size:11px;color:#16a34a;font-weight:700;">Delivered</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                    No return deliveries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>