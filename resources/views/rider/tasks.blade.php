<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">My Tasks</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage your pickup and delivery tasks</p>
            </div>
            <a href="{{ route('rider.dashboard') }}"
                style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <!-- TABLE SECTION -->
    <div x-data="riderTasks()" style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;display:flex;flex-direction:column;gap:0;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:24px;background:#f8fafc;">
            <div>
                <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Task List</h3>
            </div>
        </div>

        <div style="padding:16px; display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:16px; background:#f8fafc;">
            @forelse($tasks as $task)
                <div style="background:white; border-radius:16px; border:1.5px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05); overflow:hidden; display:flex; flex-direction:column; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="padding:16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <span style="display:inline-block; font-size:10px; font-weight:800; padding:3px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;
                                {{ $task->task_type === 'pickup' ? 'background:#fef3c7; color:#d97706; border:1px solid #fde68a;' : 'background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;' }}">
                                {{ $task->task_type === 'pickup' ? '📦 Pickup' : '🚚 Delivery' }}
                            </span>
                            <div style="font-size:16px; font-weight:900; color:#0f172a;">
                                @if($task->appointment && $task->appointment->order)
                                    {{ $task->appointment->order->order_number }}
                                @elseif($task->order)
                                    {{ $task->order->order_number }}
                                @else
                                    Task #{{ str_pad($task->id, 5, '0', STR_PAD_LEFT) }}
                                @endif
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <span style="display:inline-block; font-size:10px; font-weight:800; padding:4px 8px; border-radius:6px; text-transform:uppercase; letter-spacing:0.04em;
                                {{ $task->status === 'completed' ? 'background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;' : '' }}
                                {{ $task->status === 'cancelled' ? 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;' : '' }}
                                {{ in_array($task->status, ['assigned','accepted','on_way','arrived']) ? 'background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;' : '' }}
                                {{ $task->status === 'waiting_for_payment' ? 'background:#fef2f2; color:#dc2626; border:1px solid #fecaca;' : '' }}
                            ">
                                {{ $task->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    <div style="padding:16px; flex:1;">
                        <div style="margin-bottom:16px;">
                            <div style="font-size:11px; color:#94a3b8; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Customer</div>
                            <div style="font-weight:800; color:#0f172a; font-size:15px; margin-bottom:2px;">{{ $task->appointment->customer->name ?? 'Unknown' }}</div>
                            <a href="tel:{{ $task->appointment->contact_phone }}" style="display:inline-flex; align-items:center; gap:4px; font-size:13px; color:#2563eb; font-weight:700; text-decoration:none; padding:4px 8px; background:#eff6ff; border-radius:6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $task->appointment->contact_phone ?? 'Call Customer' }}
                            </a>
                        </div>
                        <div>
                            <div style="font-size:11px; color:#94a3b8; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Location</div>
                            <div style="font-weight:600; color:#334155; font-size:13px; line-height:1.5; background:#f8fafc; padding:8px 12px; border-radius:8px; border:1px solid #f1f5f9;">
                                {{ $task->task_type === 'pickup' ? $task->pickup_location : $task->dropoff_location }}
                            </div>
                        </div>
                    </div>
                    <div style="padding:16px; background:#f8fafc; border-top:1px solid #f1f5f9; display:flex; gap:8px; flex-wrap:wrap;">
                        @php
                            $mapQ = urlencode($task->task_type === 'pickup' ? $task->pickup_location : $task->dropoff_location);
                            if ($task->task_type === 'pickup' && $task->appointment->latitude && $task->appointment->longitude) {
                                $mapQ = $task->appointment->latitude . ',' . $task->appointment->longitude;
                            } elseif ($task->task_type === 'delivery' && $task->appointment->delivery_lat && $task->appointment->delivery_lng) {
                                $mapQ = $task->appointment->delivery_lat . ',' . $task->appointment->delivery_lng;
                            }
                        @endphp
                        
                        <!-- Location Button -->
                        <a href="https://maps.google.com/?q={{ $mapQ }}" target="_blank"
                            style="flex:1; min-width:80px; text-align:center; padding:12px; background:white; color:#0f172a; border:1.5px solid #cbd5e1; border-radius:12px; font-size:13px; font-weight:800; text-decoration:none; transition:all 0.15s; box-shadow:0 1px 2px rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:center; gap:6px;"
                            onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Map
                        </a>

                        <!-- Simplified Action Buttons -->
                        @if(!in_array($task->status, ['completed', 'cancelled']))
                            @if($task->status === 'waiting_for_payment')
                                <div style="flex:2; padding:12px; background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:12px; font-size:13px; font-weight:800; display:flex; align-items:center; justify-content:center; gap:6px;">
                                    Handover Cash to Admin
                                </div>
                            @elseif($task->task_type === 'pickup')
                                @if($task->appointment && $task->appointment->order_id)
                                    <button onclick="updateTask({{ $task->id }}, 'complete')" style="flex:2; padding:12px; background:#16a34a; color:white; border:none; border-radius:12px; font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 2px 4px rgba(22,163,74,0.3); display:flex; align-items:center; justify-content:center; gap:6px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Complete Pickup
                                    </button>
                                @else
                                    <a href="{{ route('rider.createOrder', $task->id) }}" style="flex:2; padding:12px; background:#16a34a; color:white; text-decoration:none; border-radius:12px; font-size:13px; font-weight:800; box-shadow:0 2px 4px rgba(22,163,74,0.3); display:flex; align-items:center; justify-content:center; gap:6px;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Pick Order
                                    </a>
                                @endif
                            @else
                                @php
                                    $due = 0;
                                    if ($task->order_id && $task->appointment->order) {
                                        $due = $task->appointment->order->total - $task->appointment->order->paid_amount;
                                    }
                                @endphp
                                <button @click="openPaymentModal({{ $task->id }}, {{ $due }})" style="flex:2; padding:12px; background:#16a34a; color:white; border:none; border-radius:12px; font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 2px 4px rgba(22,163,74,0.3); display:flex; align-items:center; justify-content:center; gap:6px;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Deliver Order
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; padding:40px; text-align:center; background:white; border-radius:16px; border:1px dashed #cbd5e1;">
                    <div style="font-size:32px; margin-bottom:12px;">😴</div>
                    <div style="color:#64748b; font-size:14px; font-weight:700;">No active tasks at the moment!</div>
                    <div style="color:#94a3b8; font-size:13px; font-weight:500; margin-top:4px;">Take a break or check back later.</div>
                </div>
            @endforelse
        </div>
        <div style="padding:16px 24px;border-top:1px solid #f1f5f9;">
            {{ $tasks->links() }}
        </div>
        <!-- PAYMENT MODAL -->
        <div x-cloak x-show="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:420px;overflow:hidden;" @click.away="closePaymentModal()">
                <div style="padding:24px;text-align:center;">
                    <div style="width:64px;height:64px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <span style="font-size:28px;">💰</span>
                    </div>
                    <h3 style="font-size:20px;font-weight:900;color:#0f172a;margin:0 0 6px;">Complete Delivery</h3>
                    
                    <template x-if="paymentDue > 0">
                        <p style="font-size:14px;color:#64748b;font-weight:500;margin:0 0 20px;">Amount due for this order: <strong style="color:#ef4444;font-size:16px;">Rs. <span x-text="paymentDue"></span></strong></p>
                    </template>
                    <template x-if="paymentDue <= 0">
                        <p style="font-size:14px;color:#64748b;font-weight:500;margin:0 0 20px;">This order is fully paid. Mark as delivered?</p>
                    </template>

                    <template x-if="paymentDue > 0">
                        <div style="text-align:left;margin-bottom:24px;">
                            <label style="display:block;font-size:12px;font-weight:800;color:#475569;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Payment Method</label>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                <!-- Cash Option -->
                                <div @click="paymentMethod = 'cash'" :style="paymentMethod === 'cash' ? 'border:2px solid #22c55e;background:#f0fdf4;' : 'border:2px solid #e2e8f0;background:white;'" style="border-radius:12px;padding:12px;cursor:pointer;text-align:center;transition:all 0.2s;">
                                    <span style="font-size:24px;display:block;margin-bottom:4px;">💵</span>
                                    <span style="font-size:13px;font-weight:800;color:#0f172a;">Cash</span>
                                </div>
                                <!-- Card Option -->
                                <div @click="paymentMethod = 'card'" :style="paymentMethod === 'card' ? 'border:2px solid #3b82f6;background:#eff6ff;' : 'border:2px solid #e2e8f0;background:white;'" style="border-radius:12px;padding:12px;cursor:pointer;text-align:center;transition:all 0.2s;">
                                    <span style="font-size:24px;display:block;margin-bottom:4px;">💳</span>
                                    <span style="font-size:13px;font-weight:800;color:#0f172a;">Card / Online</span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div style="display:flex;gap:12px;">
                        <button @click="closePaymentModal()" style="flex:1;padding:14px;background:#f8fafc;color:#64748b;font-size:14px;font-weight:800;border:none;border-radius:12px;cursor:pointer;transition:background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">Cancel</button>
                        <button @click="submitDelivery()" style="flex:1;padding:14px;background:#16a34a;color:white;font-size:14px;font-weight:800;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);transition:transform 0.1s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Confirm Delivery</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <x-slot name="scripts">
    <script>
    function updateTask(taskId, action) {
        fetch(`/rider/tasks/${taskId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to update task.');
            }
        });
    }

    function riderTasks() {
        return {
            isPaymentModalOpen: false,
            currentTaskId: null,
            paymentDue: 0,
            paymentMethod: 'cash',

            openPaymentModal(taskId, due) {
                this.currentTaskId = taskId;
                this.paymentDue = due;
                this.paymentMethod = 'cash';
                this.isPaymentModalOpen = true;
            },

            closePaymentModal() {
                this.isPaymentModalOpen = false;
                this.currentTaskId = null;
            },

            async submitDelivery() {
                try {
                    const res = await fetch(`/rider/tasks/${this.currentTaskId}/complete`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            payment_amount: this.paymentDue > 0 ? this.paymentDue : 0,
                            payment_method: this.paymentMethod
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Failed to complete delivery.');
                    }
                } catch (err) {
                    alert('Connection error');
                }
            }
        }
    }
    </script>
    </x-slot>
</x-app-layout>


