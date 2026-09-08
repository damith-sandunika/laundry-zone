<x-app-layout>
    @php
    if (!function_exists('formatCartForPreview')) {
        function formatCartForPreview($cart_json) {
            if (!$cart_json) return null;
            $cartArr = is_string($cart_json) ? json_decode($cart_json, true) : $cart_json;
            if (!is_array($cartArr)) return null;
            
            $processed = [];
            foreach ($cartArr as $ci) {
                $svc = \App\Models\ServiceType::with('category')->find($ci['service_type_id'] ?? $ci['id'] ?? null);
                $qty = (float)($ci['qty'] ?? 1);
                $base_price = (float)($ci['base_price'] ?? 0);
                
                $ttId = $ci['turnaround_type_id'] ?? null;
                $ttCharge = 0;
                if ($ttId) {
                    $tt = \App\Models\TurnaroundType::find($ttId);
                    if ($tt && !$tt->is_default) {
                        $ttCharge = $tt->markup_type === 'percentage' ? ($base_price * $qty) * ($tt->markup_value / 100) : $tt->markup_value * $qty;
                    }
                }
                
                $extras = [];
                foreach ($ci['selected_extras'] ?? [] as $se) {
                    $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                    $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                    $ex = \App\Models\Extra::find($exId);
                    if ($ex) {
                        $extras[] = ['name' => $ex->name, 'qty' => $exQty, 'price' => $ex->price];
                    }
                }
                
                $processed[] = [
                    'name' => $ci['name'] ?? ($svc ? $svc->name : 'Service'),
                    'service' => $svc ? ['category' => $svc->category ? ['name' => $svc->category->name] : null] : null,
                    'qty' => $qty,
                    'base_price' => $base_price,
                    'turnaround_extra_charge' => $ttCharge,
                    'extras' => $extras,
                ];
            }
            return $processed;
        }
    }
    @endphp
    <x-slot name="header">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">Delivery & Logistics</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage incoming pickups and outgoing return deliveries</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div x-data="deliveryManager()">
        <!-- STAT CARDS / TABS -->
        <div class="grid grid-cols-2 gap-4 mb-6" style="max-width:400px;">
            <!-- Incoming Pickups -->
            <a href="?tab=pickups"
                class="{{ $currentTab === 'pickups' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }} flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                <div class="{{ $currentTab === 'pickups' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600' }} w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2 {{ $currentTab === 'pickups' ? 'text-blue-700' : 'text-gray-900' }}">{{ $pickupsCount }} Pickups</span>
            </a>

            <!-- Return Deliveries -->
            <a href="?tab=returns"
                class="{{ $currentTab === 'returns' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md' }} flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                <div class="{{ $currentTab === 'returns' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-600' }} w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2 {{ $currentTab === 'returns' ? 'text-purple-700' : 'text-gray-900' }}">{{ $returnsCount }} Returns</span>
            </a>
        </div>

        <!-- TABLE SECTION -->
        <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">{{ $currentTab === 'pickups' ? 'Incoming Pickups List' : 'Return Deliveries List' }}</h3>
            </div>

            <div style="overflow-x:auto;">
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

            <!-- Pagination -->
            @if($appointments->hasPages())
                <div style="padding:16px 24px;border-top:1px solid #f1f5f9;">
                    {{ $appointments->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- ── Cash Received Modal ── -->
        <div x-cloak x-show="showCashModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:400px;overflow:hidden;" @click.away="showCashModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fffbeb;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#d97706;margin:0;">Verify Cash Collection</h3>
                        <p style="font-size:12px;color:#b45309;margin:2px 0 0;font-weight:600;" x-text="'Order ' + (cashData?.orderNumber || '')"></p>
                    </div>
                    <button @click="showCashModal = false" style="background:none;border:none;cursor:pointer;color:#d97706;font-size:20px;">✕</button>
                </div>
                
                <form :action="`/delivery/${cashData?.id}/collect-cash`" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#64748b;">Total Balance</span>
                                <span style="font-size:13px;font-weight:800;color:#334155;" x-text="'LKR ' + Number(cashData?.total || 0).toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#16a34a;">Received Balance</span>
                                <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'- LKR ' + Number(cashData?.paid || 0).toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1.5px dashed #cbd5e1;">
                                <span style="font-size:14px;font-weight:900;color:#ea580c;">Pending Payment</span>
                                <span style="font-size:16px;font-weight:900;color:#ea580c;" x-text="'LKR ' + Number(cashData?.pending || 0).toFixed(2)"></span>
                            </div>
                        </div>

                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Cash Count by Rider (Rs.)</label>
                            <input type="number" step="0.01" name="cash_collected" :value="cashData?.pending" required style="width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:18px;font-weight:900;color:#0f172a;outline:none;text-align:center;" onfocus="this.style.borderColor='#f59e0b'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="showCashModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:14px;background:#f59e0b;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                                Confirm Cash
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Reject Modal ── -->
        <div x-cloak x-show="showRejectModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:400px;overflow:hidden;" @click.away="showRejectModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fef2f2;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#dc2626;margin:0;">Reject Order</h3>
                        <p style="font-size:12px;color:#b91c1c;margin:2px 0 0;font-weight:600;">Please provide a reason for rejecting this order.</p>
                    </div>
                    <button @click="showRejectModal = false" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:20px;">✕</button>
                </div>
                
                <form :action="`/delivery/${rejectApptId}/cancel`" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Rejection Reason</label>
                            <input type="text" name="reason" x-model="rejectReason" required placeholder="E.g. We are full for today" style="width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:600;color:#0f172a;outline:none;" onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="showRejectModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:14px;background:#ef4444;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(239,68,68,0.3);">
                                Confirm Reject
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Preview Modal ── -->
        <template x-teleport="body">
            <div x-cloak x-show="showPreviewModal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 2147483647; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px);">
                <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:850px;max-height:90vh;overflow-y:auto;display:flex;flex-direction:column;" @click.away="showPreviewModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;position:sticky;top:0;z-index:10;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0;">Order Preview</h3>
                        <p style="font-size:12px;color:#64748b;margin:2px 0 0;font-weight:600;" x-text="previewData?.order?.order_number ? 'Order #' + previewData.order.order_number : 'Pending Order'"></p>
                    </div>
                    <button @click="showPreviewModal = false" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:20px;">✕</button>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6 p-6">
                    
                    <!-- Left Column: Customer & Order Details -->
                    <div class="flex-1 flex flex-col gap-5">
                        
                        <!-- Customer Details -->
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:bold;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Customer Details</div>
                                <div style="font-size:15px;font-weight:800;color:#0f172a;" x-text="previewData?.customer?.name || 'Unknown'"></div>
                                <div style="font-size:13px;font-weight:600;color:#475569;margin-top:2px;" x-text="previewData?.customer?.contact_phone || previewData?.customer?.phone || ''"></div>
                            </div>
                        </div>
                        
                        <!-- Order Summary -->
                        <div style="background:white;padding:16px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                            <h4 style="font-size:13px;font-weight:800;color:#334155;text-transform:uppercase;margin:0 0 16px 0;letter-spacing:0.05em;display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-clipboard-list" style="color:#3b82f6;"></i> Order Summary
                            </h4>
                            
                            <template x-if="!previewData?.order?.items?.length">
                                <div style="display:flex;flex-direction:column;gap:8px;">
                                    <template x-if="previewData?.cart_json?.length">
                                        <template x-for="item in previewData.cart_json">
                                            <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:8px;">
                                                <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#334155;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                                    <div>
                                                        <!-- Category -->
                                                        <template x-if="item.service?.category?.name">
                                                            <div style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;" x-text="item.service.category.name"></div>
                                                        </template>
                                                        <!-- Item Name -->
                                                        <div>
                                                            <span x-text="(item.service?.name || item.name || 'Item')" style="font-size:14px;color:#0f172a;font-weight:700;"></span>
                                                            <template x-if="parseFloat(item.turnaround_extra_charge || 0) > 0">
                                                                <span style="font-size:10px;background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;margin-left:6px;font-weight:800;text-transform:uppercase;">Express</span>
                                                            </template>
                                                            <div style="font-size:11px;color:#64748b;margin-top:2px;" x-text="parseFloat(item.qty || item.quantity || 1) + ' x LKR ' + parseFloat((item.base_price || item.service?.base_price || item.unit_price || 0) + (item.turnaround_extra_charge || 0)).toFixed(2)"></div>
                                                        </div>
                                                    </div>
                                                    <span x-text="'LKR ' + parseFloat((item.qty || item.quantity || 1) * ((item.base_price || item.service?.base_price || item.unit_price || 0) + (item.turnaround_extra_charge || 0))).toFixed(2)" style="font-weight:900;color:#0f172a;font-size:14px;"></span>
                                                </div>
                                                <!-- Extras -->
                                                <template x-if="item.extras && item.extras.length > 0">
                                                    <template x-for="extra in item.extras">
                                                        <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#475569;padding:6px 12px 6px 24px;background:#f8faff;border-radius:8px;border:1px solid #e0e7ff;">
                                                            <div>
                                                                + <span x-text="extra.name || 'Extra'"></span>
                                                                <span x-text="' (x' + parseFloat(extra.pivot?.quantity || extra.qty || 1) + ')'" style="color:#94a3b8;"></span>
                                                            </div>
                                                            <span x-text="'LKR ' + parseFloat((extra.price || extra.unit_price || 0) * (extra.pivot?.quantity || extra.qty || 1)).toFixed(2)" style="font-weight:800;color:#3730a3;"></span>
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </template>
                                    <template x-if="!previewData?.cart_json?.length && previewData?.item_description">
                                        <div style="font-size:13px;font-weight:600;color:#334155;white-space:pre-line;padding:12px;background:#f8fafc;border-radius:8px;border:1px dashed #cbd5e1;" x-text="previewData.item_description"></div>
                                    </template>
                                </div>
                            </template>
    
                            <template x-if="previewData?.order?.items?.length">
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <template x-for="item in (previewData?.order?.items || [])">
                                        <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:8px;">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#334155;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                                <div>
                                                    <!-- Category -->
                                                    <template x-if="(item.service_type || item.serviceType)?.category?.name">
                                                        <div style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;" x-text="(item.service_type || item.serviceType).category.name"></div>
                                                    </template>
                                                    <!-- Item Name -->
                                                    <div>
                                                        <span x-text="((item.service_type || item.serviceType)?.name || item.item_description || 'Service')" style="font-size:14px;color:#0f172a;font-weight:700;"></span>
                                                        <template x-if="(item.turnaround_type || item.turnaroundType) && !(item.turnaround_type || item.turnaroundType).is_default">
                                                            <span style="font-size:10px;background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;margin-left:6px;font-weight:800;text-transform:uppercase;" x-text="(item.turnaround_type || item.turnaroundType).name"></span>
                                                        </template>
                                                        <div style="font-size:11px;color:#64748b;margin-top:2px;" x-text="parseFloat(item.quantity) + ' x LKR ' + (parseFloat(item.quantity) > 0 ? (parseFloat(item.total_price) / parseFloat(item.quantity)).toFixed(2) : parseFloat(item.total_price).toFixed(2))"></div>
                                                    </div>
                                                </div>
                                                <span x-text="'LKR ' + parseFloat(item.total_price).toFixed(2)" style="font-weight:900;color:#0f172a;font-size:14px;"></span>
                                            </div>
                                            <!-- Extras -->
                                            <template x-if="item.extras && item.extras.length > 0">
                                                <template x-for="extra in item.extras">
                                                    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#475569;padding:6px 12px 6px 24px;background:#f8faff;border-radius:8px;border:1px solid #e0e7ff;">
                                                        <div>
                                                            + <span x-text="extra.extra ? extra.extra.name : 'Extra'"></span>
                                                            <span x-text="' (x' + parseFloat(extra.quantity || 1) + ')'" style="color:#94a3b8;"></span>
                                                        </div>
                                                        <span x-text="'LKR ' + parseFloat(extra.unit_price * extra.quantity).toFixed(2)" style="font-weight:800;color:#3730a3;"></span>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <div style="margin-top:8px;padding-top:12px;border-top:1px dashed #cbd5e1;display:flex;flex-direction:column;gap:6px;">
                                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#64748b;">
                                            <span>Subtotal</span>
                                            <span x-text="'LKR ' + parseFloat(previewData.order.subtotal || 0).toFixed(2)"></span>
                                        </div>
                                        <template x-if="parseFloat(previewData.order.discount || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#16a34a;">
                                                <span>Discount</span>
                                                <span x-text="'- LKR ' + parseFloat(previewData.order.discount).toFixed(2)"></span>
                                            </div>
                                        </template>
                                        <template x-if="parseFloat(previewData.order.additional || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#dc2626;">
                                                <span>Additional Charges</span>
                                                <span x-text="'+ LKR ' + parseFloat(previewData.order.additional).toFixed(2)"></span>
                                            </div>
                                        </template>
                                        <template x-if="parseFloat(previewData.order.delivery_fee || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#475569;">
                                                <span>Delivery Fee</span>
                                                <span x-text="'+ LKR ' + parseFloat(previewData.order.delivery_fee).toFixed(2)"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#0f172a;margin-top:4px;padding-top:12px;border-top:1px solid #cbd5e1;">
                                        <span>Total Amount</span>
                                        <span x-text="'LKR ' + parseFloat(previewData.order.total || previewData.order.total_amount).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="previewData?.notes">
                            <div style="background:#fef2f2;padding:16px;border-radius:12px;border:1px solid #fca5a5;">
                                <div style="font-size:11px;font-weight:900;color:#dc2626;text-transform:uppercase;margin-bottom:6px;letter-spacing:0.05em;"><i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>Special Notes</div>
                                <div style="font-size:13px;font-weight:600;color:#991b1b;line-height:1.5;" x-text="previewData.notes"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Right Column: Maps -->
                    <div class="w-full md:w-[320px] flex flex-col gap-4 shrink-0">
                        <!-- Pickup Location Map -->
                        <div style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div style="padding:12px 16px;background:#fff7ed;display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;background:#ffedd5;color:#ea580c;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;"><i class="fa-solid fa-arrow-up"></i></div>
                                <div>
                                    <div style="font-size:10px;font-weight:900;color:#ea580c;text-transform:uppercase;letter-spacing:0.05em;">Pickup Location</div>
                                    <div style="font-size:12px;font-weight:800;color:#431407;line-height:1.2;margin-top:2px;">
                                        Branch: <span x-text="previewData?.branch?.name || 'Main Branch'"></span>
                                    </div>
                                    <template x-if="previewData?.pickup_scheduled_at">
                                        <div style="font-size:11px;font-weight:800;color:#9a3412;margin-top:2px;">
                                            <i class="fa-regular fa-clock" style="margin-right:2px;"></i> <span x-text="new Date(previewData.pickup_scheduled_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div style="padding:10px 16px;font-size:13px;font-weight:600;color:#334155;background:white;" x-text="previewData?.pickup_address || 'Not specified'"></div>

                            <template x-if="parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.pickup_lng)">
                                <div>
                                    <!-- Pickup Route Distance Badge -->
                                    <div id="pickup-route-badge" style="display:none;padding:8px 14px;background:linear-gradient(135deg,#ea580c,#f97316);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                        <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                        <span id="pickup-route-text">Calculating route...</span>
                                    </div>
                                    <!-- Pickup Route Map -->
                                    <div id="pickup-route-map" style="width:100%;height:200px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;"></div>
                                </div>
                            </template>
                            <template x-if="!(parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.pickup_lng))">
                                <div style="width:100%;height:140px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;font-weight:600;">
                                    <i class="fa-solid fa-map-location-dot" style="margin-right:6px;"></i> Map pin not provided
                                </div>
                            </template>
                        </div>
                        
                        <!-- Delivery Location Map -->
                        <div style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div style="padding:12px 16px;background:#f0fdf4;display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;"><i class="fa-solid fa-arrow-down"></i></div>
                                <div>
                                    <div style="font-size:10px;font-weight:900;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">Delivery Location</div>
                                    <template x-if="previewData?.delivery_scheduled_at">
                                        <div style="font-size:11px;font-weight:800;color:#166534;margin-top:2px;">
                                            <i class="fa-regular fa-clock" style="margin-right:2px;"></i> <span x-text="new Date(previewData.delivery_scheduled_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Case 1: Customer will pick up from branch -->
                            <template x-if="previewData?.delivery_preference === 'pickup_from_branch' || previewData?.delivery_preference === 'pickup_from_store' || previewData?.delivery_preference === 'self_pickup'">
                                <div style="padding:20px 16px;background:white;text-align:center;font-size:13px;font-weight:700;color:#475569;border-top:1px solid #f1f5f9;">
                                    <i class="fa-solid fa-store" style="color:#ea580c;margin-bottom:8px;font-size:22px;display:block;"></i>
                                    Customer will pick up from branch
                                </div>
                            </template>

                            <!-- Case 2: Deliver to same address as pickup -->
                            <template x-if="previewData?.delivery_preference !== 'pickup_from_branch' && previewData?.delivery_preference !== 'pickup_from_store' && previewData?.delivery_preference !== 'self_pickup' && (!previewData?.delivery_address || previewData?.delivery_address === previewData?.pickup_address || (parseFloat(previewData?.delivery_lat) > 0 && parseFloat(previewData?.delivery_lat) === parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.delivery_lng) === parseFloat(previewData?.pickup_lng)))">
                                <div style="padding:20px 16px;background:white;text-align:center;font-size:13px;font-weight:700;color:#475569;border-top:1px solid #f1f5f9;">
                                    <i class="fa-solid fa-arrows-left-right" style="color:#6366f1;margin-bottom:8px;font-size:22px;display:block;"></i>
                                    Same as pickup location
                                </div>
                            </template>

                            <!-- Case 3: Different delivery address with map + route from branch -->
                            <template x-if="previewData?.delivery_preference !== 'pickup_from_branch' && previewData?.delivery_preference !== 'pickup_from_store' && previewData?.delivery_preference !== 'self_pickup' && previewData?.delivery_address && previewData?.delivery_address !== previewData?.pickup_address && !(parseFloat(previewData?.delivery_lat) > 0 && parseFloat(previewData?.delivery_lat) === parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.delivery_lng) === parseFloat(previewData?.pickup_lng))">
                                <div>
                                    <div style="padding:10px 16px;font-size:13px;font-weight:600;color:#334155;background:white;border-top:1px solid #f1f5f9;" x-text="previewData?.delivery_address || 'Not specified'"></div>
                                    <template x-if="parseFloat(previewData?.delivery_lat) && parseFloat(previewData?.delivery_lng)">
                                        <div>
                                            <!-- Route Distance Badge -->
                                            <div id="route-distance-badge" style="display:none;padding:8px 14px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                                <span id="route-distance-text">Calculating route...</span>
                                            </div>
                                            <!-- Route Map -->
                                            <div id="delivery-route-map" style="width:100%;height:200px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;"></div>
                                        </div>
                                    </template>
                                    <template x-if="!(parseFloat(previewData?.delivery_lat) && parseFloat(previewData?.delivery_lng))">
                                        <div style="width:100%;height:140px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;font-weight:600;">
                                            <i class="fa-solid fa-map-location-dot" style="margin-right:6px;"></i> Map pin not provided
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('deliveryManager', () => ({
                currentTab: 'pickups',
                showCashModal: false,
                cashData: null,
                previewData: null,
                showPreviewModal: false,
                openPreview(data) {
                    this.previewData = typeof data === 'string' ? JSON.parse(data) : data;
                    this.showPreviewModal = true;
                    setTimeout(() => this.initPreviewMap(), 300);
                },
                initPreviewMap() {
                    let pLat = parseFloat(this.previewData.pickup_lat);
                    let pLng = parseFloat(this.previewData.pickup_lng);
                    let dLat = parseFloat(this.previewData.delivery_lat);
                    let dLng = parseFloat(this.previewData.delivery_lng);
                    let bLat = parseFloat(this.previewData.branch?.latitude);
                    let bLng = parseFloat(this.previewData.branch?.longitude);
                    let bName = this.previewData.branch?.name || 'Branch';
                    let isSameLocation = (pLat === dLat && pLng === dLng);
                    let isDifferentDelivery = this.previewData.delivery_preference !== 'pickup_from_branch' &&
                        this.previewData.delivery_preference !== 'pickup_from_store' &&
                        this.previewData.delivery_preference !== 'self_pickup' &&
                        this.previewData.delivery_address &&
                        this.previewData.delivery_address !== this.previewData.pickup_address &&
                        !isSameLocation;

                    // Initialize Pickup Route Map (Branch → Customer Pickup Location)
                    if (pLat && pLng) {
                        setTimeout(() => {
                            let pEl = document.getElementById('pickup-route-map');
                            if (pEl) {
                                // Destroy old map if exists
                                if (this.pickupMap) {
                                    this.pickupMap.remove();
                                    this.pickupMap = null;
                                }
                                this.pickupMap = L.map('pickup-route-map', {zoomControl: true});
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {maxZoom: 19}).addTo(this.pickupMap);

                                // Customer pickup marker (orange)
                                let pickupIcon = L.divIcon({
                                    html: '<div style="width:32px;height:32px;background:#ea580c;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(234,88,12,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                });
                                L.marker([pLat, pLng], {icon: pickupIcon}).bindPopup('<b>📍 Customer Pickup</b><br>' + (this.previewData.pickup_address || '')).addTo(this.pickupMap);

                                if (bLat && bLng) {
                                    // Branch marker (blue)
                                    let branchIcon = L.divIcon({
                                        html: '<div style="width:32px;height:32px;background:#1d4ed8;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(29,78,216,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                        className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                    });
                                    L.marker([bLat, bLng], {icon: branchIcon}).bindPopup('<b>🏪 ' + bName + '</b>').addTo(this.pickupMap);

                                    // Fit to show both points
                                    let pBounds = L.latLngBounds([[bLat, bLng], [pLat, pLng]]);
                                    this.pickupMap.fitBounds(pBounds, {padding: [30, 30]});

                                    // Show badge
                                    let pBadge = document.getElementById('pickup-route-badge');
                                    if (pBadge) { pBadge.style.display = 'block'; }

                                    // Fetch OSRM route branch → pickup
                                    let osrmPickupUrl = `https://router.project-osrm.org/route/v1/driving/${bLng},${bLat};${pLng},${pLat}?overview=full&geometries=geojson`;
                                    fetch(osrmPickupUrl)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);

                                                // Draw route polyline (orange for pickup)
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, {
                                                    color: '#ea580c', weight: 4, opacity: 0.8
                                                }).addTo(this.pickupMap);

                                                // Update badge
                                                let pText = document.getElementById('pickup-route-text');
                                                if (pText) {
                                                    pText.innerHTML = `${bName} → Pickup &nbsp;|&nbsp; 🛣️ ${distKm} km &nbsp;|&nbsp; ⏱️ ~${durationMin} min`;
                                                }

                                                // Re-fit to route
                                                this.pickupMap.fitBounds(L.latLngBounds(routeCoords), {padding: [25, 25]});
                                            }
                                        })
                                        .catch(() => {
                                            let pText = document.getElementById('pickup-route-text');
                                            if (pText) { pText.textContent = 'Route unavailable (offline)'; }
                                        });
                                } else {
                                    // No branch coords — just show pickup point
                                    this.pickupMap.setView([pLat, pLng], 15);
                                }
                                this.pickupMap.invalidateSize();
                            }
                        }, 200);
                    }

                    // Initialize Delivery Route Map (Branch → Customer) if different location
                    if (isDifferentDelivery && dLat && dLng) {
                        setTimeout(() => {
                            let dEl = document.getElementById('delivery-route-map');
                            if (dEl) {
                                // Destroy old map if exists
                                if (this.deliveryMap) {
                                    this.deliveryMap.remove();
                                    this.deliveryMap = null;
                                }
                                this.deliveryMap = L.map('delivery-route-map', {zoomControl: true});
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {maxZoom: 19}).addTo(this.deliveryMap);

                                // Customer delivery marker (green)
                                let customerIcon = L.divIcon({
                                    html: '<div style="width:32px;height:32px;background:#16a34a;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(22,163,74,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                    className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                });
                                L.marker([dLat, dLng], {icon: customerIcon}).bindPopup('<b>📍 Customer Location</b><br>' + (this.previewData.delivery_address || '')).addTo(this.deliveryMap);

                                // Branch marker (blue/orange) — if branch coordinates available
                                if (bLat && bLng) {
                                    let branchIcon = L.divIcon({
                                        html: '<div style="width:32px;height:32px;background:#1d4ed8;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 8px rgba(29,78,216,0.5);"><div style="width:10px;height:10px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);"></div></div>',
                                        className: '', iconSize: [32, 32], iconAnchor: [16, 32]
                                    });
                                    L.marker([bLat, bLng], {icon: branchIcon}).bindPopup('<b>🏪 ' + bName + '</b>').addTo(this.deliveryMap);

                                    // Fit map to show both points
                                    let bounds = L.latLngBounds([[bLat, bLng], [dLat, dLng]]);
                                    this.deliveryMap.fitBounds(bounds, {padding: [30, 30]});

                                    // Show distance badge
                                    let badge = document.getElementById('route-distance-badge');
                                    if (badge) { badge.style.display = 'block'; }

                                    // Fetch route from OSRM
                                    let osrmUrl = `https://router.project-osrm.org/route/v1/driving/${bLng},${bLat};${dLng},${dLat}?overview=full&geometries=geojson`;
                                    fetch(osrmUrl)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);

                                                // Draw route polyline
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, {
                                                    color: '#4f46e5', weight: 4, opacity: 0.8, dashArray: null
                                                }).addTo(this.deliveryMap);

                                                // Update distance badge
                                                let distText = document.getElementById('route-distance-text');
                                                if (distText) {
                                                    distText.innerHTML = `${bName} → Customer &nbsp;|&nbsp; 🛣️ ${distKm} km &nbsp;|&nbsp; ⏱️ ~${durationMin} min`;
                                                }

                                                // Re-fit to route
                                                this.deliveryMap.fitBounds(L.latLngBounds(routeCoords), {padding: [25, 25]});
                                            }
                                        })
                                        .catch(() => {
                                            let distText = document.getElementById('route-distance-text');
                                            if (distText) { distText.textContent = 'Route unavailable (offline)'; }
                                        });
                                } else {
                                    // No branch coords — just show delivery point
                                    this.deliveryMap.setView([dLat, dLng], 15);
                                }
                                this.deliveryMap.invalidateSize();
                            }
                        }, 200);
                    }
                },
                openCashModal(apptId, orderNumber, total, paid, pending) {
                    this.cashData = { id: apptId, orderNumber, total, paid, pending };
                    this.showCashModal = true;
                },
                showRejectModal: false,
                rejectApptId: null,
                rejectReason: '',
                openRejectModal(apptId) {
                    this.rejectApptId = apptId;
                    this.rejectReason = '';
                    this.showRejectModal = true;
                }
            }));
        });
    </script>
</x-app-layout>


