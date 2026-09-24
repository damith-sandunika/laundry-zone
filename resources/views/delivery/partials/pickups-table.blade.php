                <!-- PICKUPS TABLE -->
                @if($currentTab === 'pickups')
                <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Booking ID & Customer</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Pickup Location</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Scheduled Time</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status & Rider</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;{{ $appt->status === 'cancelled' ? 'opacity:0.7;background:#fff5f5;' : '' }}" onmouseover="this.style.opacity='{{ $appt->status === 'cancelled' ? '0.7' : '0.85' }}'" onmouseout="this.style.opacity='{{ $appt->status === 'cancelled' ? '0.7' : '1' }}'">
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
                                    <div style="margin-top:4px;">
                                        <span style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #cbd5e1;background:#f8fafc;color:#475569;text-transform:uppercase;">
                                            {{ $appt->payment_method ?? 'Cash' }}
                                        </span>
                                    </div>
                                </td>
                                <td style="padding:12px 20px;">
                                    <div style="font-weight:600;color:#334155;">{{ $appt->pickup_address }}</div>
                                    @if($appt->notes)
                                        <div style="font-size:11px;color:#94a3b8;font-style:italic;margin-top:2px;">Note: {{ $appt->notes }}</div>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                    <span style="display:block;">{{ $appt->pickup_scheduled_at?->format('d M Y') }}</span>
                                    <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $appt->pickup_scheduled_at?->format('H:i A') }}</span>
                                </td>
                                <td style="padding:12px 20px;">
                                    <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;
                                        {{ $appt->status === 'pending' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' : '' }}
                                        {{ in_array($appt->status, ['confirmed', 'rider_assigned']) ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' : '' }}
                                        {{ in_array($appt->status, ['picked_up', 'order_created']) ? 'background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;' : '' }}
                                        {{ $appt->status === 'cancelled' ? 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', $appt->status) }}
                                    </span>
                                    @php $riderTask = $appt->deliveryTasks->where('task_type', 'pickup')->last(); @endphp
                                    @if($riderTask)
                                        <div style="margin-top:4px;font-size:11px;font-weight:700;color:#64748b;">
                                            Rider: {{ $riderTask->rider->name ?? 'Unknown' }}
                                        </div>
                                    @elseif(in_array($appt->status, ['rider_assigned', 'picked_up', 'at_branch']))
                                        <div style="margin-top:4px;font-size:11px;font-weight:800;color:#eab308;">
                                            PickMe/Flash (Pickup)
                                        </div>
                                    @endif
                                    @if($appt->status === 'cancelled' && $appt->cancel_reason)
                                        <div style="margin-top:4px;font-size:11px;font-weight:700;color:#dc2626;background:#fef2f2;padding:4px;border-radius:4px;border:1px solid #fecaca;">
                                            Reason: {{ $appt->cancel_reason }}<br>
                                            <span style="font-size:9.5px;color:#991b1b;opacity:0.8;">Rejected by: {{ $appt->cancelledBy->name ?? 'System' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;text-align:right;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;">
                                        <button type="button" data-appt="{{ json_encode(['customer' => $appt->customer, 'order' => $appt->order, 'branch' => $appt->branch, 'delivery_preference' => $appt->delivery_preference, 'pickup_lat' => $appt->latitude, 'pickup_lng' => $appt->longitude, 'delivery_lat' => $appt->delivery_lat, 'delivery_lng' => $appt->delivery_lng, 'pickup_address' => $appt->pickup_address, 'delivery_address' => $appt->delivery_address, 'pickup_scheduled_at' => $appt->pickup_scheduled_at, 'delivery_scheduled_at' => $appt->delivery_scheduled_at, 'notes' => $appt->special_notes ?? '', 'item_description' => $appt->item_description ?? '', 'cart_json' => formatCartForPreview($appt->cart_json)]) }}" @click="openPreview($event.currentTarget.dataset.appt)" style="padding:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;color:#3b82f6;cursor:pointer;" title="Preview Order"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>
                                        @if($appt->status === 'pending')
                                            <form method="POST" action="{{ route('delivery.confirmAction', ['appointment' => $appt->id]) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" style="padding:5px 10px;background:#1d4ed8;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(29,78,216,0.3);">
                                                    Confirm
                                                </button>
                                            </form>
                                            <button type="button" @click="openRejectModal({{ $appt->id }})" style="padding:5px 10px;background:#dc2626;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(220,38,38,0.3);">
                                                Reject
                                            </button>
                                        @elseif($appt->status === 'confirmed')
                                            <div x-data="{ deliveryMethod: 'pickme_flash' }" style="display:flex;gap:10px;align-items:center;">
                                                <form method="POST" action="{{ route('delivery.assignRider', ['appointment' => $appt->id]) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                    @csrf
                                                    <select x-model="deliveryMethod" name="delivery_method" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;">
                                                        <option value="pickme_flash">PickMe</option>
                                                        <option value="in_house">In-house Rider</option>
                                                    </select>

                                                    <select x-show="deliveryMethod === 'in_house'" name="rider_id" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;">
                                                        <option value="">Select Rider</option>
                                                        @foreach($riders as $rider)
                                                            <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                                        @endforeach
                                                    </select>

                                                    <button type="submit" style="padding:5px 10px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(79,70,229,0.3);">
                                                        Assign
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif(in_array($appt->status, ['picked_up', 'order_created']) || ($appt->status === 'rider_assigned' && !isset($riderTask)))
                                            <div x-data="{ showCost: false }" style="display:flex;gap:4px;align-items:center;">
                                                <form method="POST" action="{{ route('delivery.atBranch', ['appointment' => $appt->id]) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                    @csrf
                                                    
                                                    <button type="button" @click="showCost = !showCost" title="Add PickMe Cost" style="padding:4px 6px;background:white;color:#ea580c;border:1px solid #ea580c;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;">
                                                        <i class="fa-solid fa-taxi"></i> Cost
                                                    </button>
                                                    
                                                    <input x-show="showCost" type="number" step="0.01" name="pickme_flash_cost" placeholder="Amount" style="width:70px;padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;" x-cloak>

                                                    <button type="submit" style="padding:5px 10px;background:#ea580c;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,88,12,0.3);">
                                                        Received at Workshop
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600;">No Action</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                    No incoming pickups found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif