<x-app-layout>
    <x-slot name="header">
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

    <div x-data="{ 
        currentTab: 'pickups',
        showCashModal: false,
        cashData: null,
        openCashModal(apptId, orderNumber, total, paid, pending) {
            this.cashData = { id: apptId, orderNumber, total, paid, pending };
            this.showCashModal = true;
        }
    }">
        <!-- STAT CARDS / TABS -->
        <div class="grid grid-cols-2 gap-4 mb-6" style="max-width:400px;">
            <!-- Incoming Pickups -->
            <div @click="currentTab = 'pickups'"
                :class="currentTab === 'pickups' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <div :class="currentTab === 'pickups' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600'"
                    class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2"
                    :class="currentTab === 'pickups' ? 'text-blue-700' : 'text-gray-900'">{{ count($pickups) }} Pickups</span>
            </div>

            <!-- Return Deliveries -->
            <div @click="currentTab = 'returns'"
                :class="currentTab === 'returns' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-4 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <div :class="currentTab === 'returns' ? 'bg-purple-600 text-white' : 'bg-purple-100 text-purple-600'"
                    class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                </div>
                <span class="text-sm font-black mt-2"
                    :class="currentTab === 'returns' ? 'text-purple-700' : 'text-gray-900'">{{ count($returns) }} Returns</span>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;" x-text="currentTab === 'pickups' ? 'Incoming Pickups List' : 'Return Deliveries List'"></h3>
            </div>

            <div style="overflow-x:auto;">
                <!-- PICKUPS TABLE -->
                <table x-show="currentTab === 'pickups'" style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
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
                        @forelse($pickups as $appt)
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <div style="font-size:13px;font-weight:800;color:#1d4ed8;">#{{ str_pad($appt->id, 5, '0', STR_PAD_LEFT) }}</div>
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
                                    @endif
                                </td>
                                <td style="padding:12px 20px;text-align:right;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;">
                                        @if($appt->status === 'pending')
                                            <form method="POST" action="{{ route('delivery.confirmAction', $appt) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" style="padding:5px 10px;background:#1d4ed8;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(29,78,216,0.3);">
                                                    Confirm
                                                </button>
                                            </form>
                                        @elseif($appt->status === 'confirmed')
                                            <div x-data="{ deliveryMethod: 'in_house' }" style="display:flex;gap:10px;align-items:center;">
                                                <form method="POST" action="{{ route('delivery.assignRider', $appt) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                    @csrf
                                                    <select x-model="deliveryMethod" name="delivery_method" style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;">
                                                        <option value="in_house">In-house Rider</option>
                                                        <option value="pickme_flash">PickMe Flash</option>
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
                                        @elseif(in_array($appt->status, ['picked_up', 'order_created']))
                                            <div x-data="{ showCost: false }" style="display:flex;gap:4px;align-items:center;">
                                                <form method="POST" action="{{ route('delivery.atBranch', $appt) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                    @csrf
                                                    
                                                    <button type="button" @click="showCost = !showCost" title="Add PickMe Flash Cost" style="padding:4px 6px;background:white;color:#ea580c;border:1px solid #ea580c;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;">
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

                <!-- RETURNS TABLE -->
                <table x-cloak x-show="currentTab === 'returns'" style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
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
                        @forelse($returns as $appt)
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <div style="font-size:13px;font-weight:800;color:#1d4ed8;">#{{ str_pad($appt->id, 5, '0', STR_PAD_LEFT) }}</div>
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-weight:700;color:#0f172a;">{{ $appt->customer->name ?? 'Unknown' }}</span>
                                        <span style="font-size:11px;color:#64748b;font-weight:600;">{{ $appt->contact_phone }}</span>
                                    </div>
                                    @if($appt->order_id)
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
                                        if ($order) {
                                            $pendingCashTx = \App\Models\PaymentTransaction::where('order_id', $order->id)
                                                ->where('payment_method', 'cash')
                                                ->whereNull('collected_from_rider_at')
                                                ->whereNotNull('created_by')
                                                ->exists();
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
                                                <span style="font-size:10px;font-weight:800;color:#d97706;background:#fef3c7;padding:2px 6px;border-radius:4px;border:1px solid #fde68a;">⏳ Waiting for Cash</span>
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
                                    @if($riderTask)
                                        <div style="margin-top:4px;font-size:11px;font-weight:700;color:#64748b;">
                                            Rider: {{ $riderTask->rider->name ?? 'Unknown' }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:12px 20px;text-align:right;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;">
                                        @if($appt->status === 'ready')
                                            <form method="POST" action="{{ route('delivery.assignDeliveryRider', $appt) }}" style="margin:0;display:flex;gap:4px;align-items:center;">
                                                @csrf
                                                <input type="hidden" name="order_id" value="{{ $appt->order_id }}">
                                                <select name="rider_id" required style="padding:4px 8px;border:1.5px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:600;color:#334155;outline:none;background:white;">
                                                    <option value="">Select Rider</option>
                                                    @foreach($riders as $rider)
                                                        <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" style="padding:5px 10px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(79,70,229,0.3);">
                                                    Assign
                                                </button>
                                            </form>
                                        @elseif($appt->status === 'out_for_delivery')
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600;">Awaiting Rider</span>
                                        @elseif($appt->status === 'delivered')
                                            @if($pendingCashTx)
                                                <button type="button" @click="openCashModal({{ $appt->id }}, '{{ $order->order_number }}', {{ $order->total }}, {{ $order->paid_amount }}, {{ max(0, $order->total - $order->paid_amount) }})" style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(234,179,8,0.3);">
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
            </div>
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

    </div>
</x-app-layout>
