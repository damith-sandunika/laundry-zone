<x-app-layout>
    <x-slot name="header">
        <style>
            .ord-btn-wrap {
                position: relative;
                display: inline-flex;
                align-items: center;
            }

            .ord-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: 1.5px solid;
                background: white;
                cursor: pointer;
                font-size: 16px;
                line-height: 1;
                transition: all 0.15s;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
                text-decoration: none;
                flex-shrink: 0;
            }

            .ord-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            }

            .ord-btn-label {
                position: absolute;
                bottom: calc(100% + 7px);
                left: 50%;
                transform: translateX(-50%);
                background: #0f172a;
                color: white;
                font-size: 11px;
                font-weight: 700;
                padding: 4px 8px;
                border-radius: 6px;
                white-space: nowrap;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.15s;
                z-index: 50;
            }

            .ord-btn-label::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #0f172a;
            }

            .ord-btn-wrap:hover .ord-btn-label {
                opacity: 1;
            }

            /* Pay Now - green gradient button (larger, pill) */
            .ord-btn--pay-full {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border-radius: 10px;
                border: none;
                background: linear-gradient(135deg, #10b981, #059669);
                cursor: pointer;
                font-size: 16px;
                transition: all 0.15s;
                box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
            }

            .ord-btn--pay-full:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
            }

            /* Individual color accents */
            .ord-btn--edit {
                color: #1d4ed8;
                border-color: #bfdbfe;
            }

            .ord-btn--edit:hover {
                background: #eff6ff;
                border-color: #93c5fd;
            }

            .ord-btn--pdf {
                color: #dc2626;
                border-color: #fecaca;
            }

            .ord-btn--pdf:hover {
                background: #fef2f2;
                border-color: #fca5a5;
            }

            .ord-btn--print {
                color: #334155;
                border-color: #e2e8f0;
            }

            .ord-btn--print:hover {
                background: #f8fafc;
                border-color: #cbd5e1;
            }

            .ord-btn--chit {
                color: #7c3aed;
                border-color: #ddd6fe;
            }

            .ord-btn--chit:hover {
                background: #f5f3ff;
                border-color: #c4b5fd;
            }
        </style>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <a href="{{ route('orders.index') }}"
                        style="font-size:12px;color:#94a3b8;text-decoration:none;font-weight:700;"
                        onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#94a3b8'">← Orders</a>
                    <span style="color:#cbd5e1;font-size:11px;">/</span>
                    <span style="font-size:12px;color:#64748b;font-weight:700;">{{ $order->order_number }}</span>
                </div>
                <h2 class="ph-title">Order Details</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                    Customer: <span
                        style="font-weight:700;color:#334155;">{{ $order->customer->name ?? 'Unknown Customer' }}</span>
                    &bull; {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
            </div>

            {{-- ── Icon Buttons with Hover Tooltip ── --}}
            <div style="display:flex;align-items:center;gap:8px;">

                {{-- 💰 Pay Now --}}
                @if($order->total - $order->paid_amount > 0)
                    <div class="ord-btn-wrap" x-data="{}">
                        <button type="button" @click="$dispatch('open-pay-modal')" class="ord-btn--pay-full" title="">
                            💰
                        </button>
                        <span class="ord-btn-label">Pay Now</span>
                    </div>
                @endif

                {{-- ✏️ Edit Order --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.edit', $order->order_number) }}" class="ord-btn ord-btn--edit" title="">
                        ✏️
                    </a>
                    <span class="ord-btn-label">Edit Order</span>
                </div>

                {{-- 📄 Download PDF --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank"
                        class="ord-btn ord-btn--pdf" title="">
                        📄
                    </a>
                    <span class="ord-btn-label">Download PDF</span>
                </div>

                {{-- 🖨️ Print Bill --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.print', $order->order_number) }}" target="_blank"
                        class="ord-btn ord-btn--print" title="">
                        🖨️
                    </a>
                    <span class="ord-btn-label">Print Bill</span>
                </div>

                {{-- 📋 Print Daw Chit --}}
                <div class="ord-btn-wrap">
                    <a href="{{ route('orders.printChit', $order->id) }}"
                        onclick="window.open(this.href,'_blank','width=320,height=480'); return false;"
                        class="ord-btn ord-btn--chit" title="">
                        📋
                    </a>
                    <span class="ord-btn-label">Print Daw Chit</span>
                </div>

            </div>
        </div>
    </x-slot>


    @if(session('success'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:12px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Please fix the following errors:</span>
            </div>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('print_bill_id'))
        <script>
            window.onload = function () {
                window.open("{{ route('orders.print', session('print_bill_id')) }}", "_blank");
            };
        </script>
    @endif

    <!-- Status Banner -->
    <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;
        {{ $order->status === 'received' ? 'background:#fffbeb;color:#d97706;border:1.5px solid #fde68a;' :
    ($order->status === 'done' ? 'background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;' :
        ($order->status === 'delivered' ? 'background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;' :
            'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;')) }}">

        <div style="display:flex;align-items:center;gap:10px;">
            <span>Order Status:</span>
            <span style="font-size:16px;font-weight:900;">{{ $order->status }}</span>
        </div>

        @php
            $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)->first();
            $hasPayment = $order->paymentTransactions()->whereIn('status', ['completed', 'pending'])->exists() || $order->receipt_path || ($order->deliveryAppointment && $order->deliveryAppointment->receipt_path);
            $canStartWashing = $dispatch && in_array($dispatch->status, ['dispatched_to_workshop', 'received_at_workshop']) && $hasPayment;
        @endphp

        @if($canStartWashing)
            <button onclick="startWashing({{ $dispatch->id }})"
                style="padding:8px 16px;background:#eab308;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <i class="fa-solid fa-play"></i> Start Washing
            </button>
            <script>
                async function startWashing(dispatchId) {
                    if (!confirm('Are you sure you want to start washing this order?')) return;
                    try {
                        const res = await fetch(`/workshop/dispatches/${dispatchId}/start-processing`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (data.order_number) {
                                window.open(`/orders/${data.order_number}/chit`, '_blank');
                            }
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to start washing.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Connection error');
                    }
                }
            </script>
        @endif
    </div>

    @if($order->correctionRequests->count() > 0)
        <div class="flex flex-col gap-3 mb-6">
            @foreach($order->correctionRequests->sortByDesc('created_at') as $cr)
                <div class="p-4 rounded-xl flex items-start justify-between gap-4 text-[13px] border-2 shadow-sm
                            {{ $cr->status === 'pending' ? 'bg-amber-50 text-amber-900 border-amber-200 shadow-amber-100/50' :
                    ($cr->status === 'approved' ? 'bg-emerald-50 text-emerald-900 border-emerald-200 shadow-emerald-100/50' :
                        'bg-red-50 text-red-900 border-red-200 shadow-red-100/50') }}">
                    <div class="flex-1 w-full">
                        <strong class="uppercase text-[11px] tracking-wider">{{ ucfirst($cr->status) }} Correction Request
                            <span class="opacity-75 font-semibold">({{ str_replace('_', ' ', $cr->type) }})</span></strong><br>
                        <span class="text-slate-600">Requested by <strong
                                class="text-slate-800">{{ $cr->requestedBy->name ?? 'Unknown' }}</strong> on
                            {{ $cr->created_at->format('M d, Y h:i A') }}</span><br>
                        <div class="mt-1.5 p-2.5 bg-white/60 rounded-lg border border-slate-200/50 italic text-slate-700">
                            "{{ $cr->reason }}"</div>

                        @if(is_array($cr->old_data) && is_array($cr->new_data))
                            @php
                                $oldTotal = $cr->old_data['total'] ?? $order->total;
                                $newTotal = $cr->new_data['calculated_total'] ?? $cr->old_data['total'] ?? $order->total;
                                $oldMethod = $cr->old_data['payment_method'] ?? $order->payment_method;
                                $newMethod = $cr->new_data['payment_method'] ?? $cr->old_data['payment_method'] ?? $order->payment_method;
                            @endphp
                            <div
                                class="mt-3 bg-white/70 backdrop-blur-sm p-3 rounded-xl text-[12px] border border-slate-200/60 shadow-sm w-full space-y-1.5">
                                @if((isset($cr->old_data['total']) || isset($cr->new_data['calculated_total'])) && $oldTotal != $newTotal)
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-1.5">
                                        <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Total Amount:</strong>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-red-500 line-through font-bold">Rs.{{ number_format($oldTotal, 2) }}</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3">
                                                </path>
                                            </svg>
                                            <strong
                                                class="text-emerald-600 font-black text-[13px]">Rs.{{ number_format($newTotal, 2) }}</strong>
                                        </div>
                                    </div>
                                @endif
                                @if((isset($cr->old_data['payment_method']) || isset($cr->new_data['payment_method'])) && $oldMethod != $newMethod)
                                    <div class="flex justify-between items-center border-b border-slate-200/50 pb-1.5">
                                        <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Payment Method:</strong>
                                        <div class="flex items-center gap-2">
                                            <span class="text-red-500 line-through font-bold">{{ ucfirst($oldMethod) }}</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3">
                                                </path>
                                            </svg>
                                            <strong class="text-emerald-600 font-black text-[13px]">{{ ucfirst($newMethod) }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($cr->type === 'items_change' || $cr->type === 'order_change')
                                    @php
                                        $oldCart = collect($cr->old_data['cart'] ?? []);
                                        $newCart = collect($cr->new_data['cart'] ?? []);
                                        $diff = [];

                                        foreach ($newCart as $newItem) {
                                            $oldItem = $oldCart->firstWhere('service_type_id', $newItem['service_type_id']);
                                            if (!$oldItem && !isset($newItem['service_type_id'])) {
                                                $oldItem = $oldCart->firstWhere('name', $newItem['name']);
                                            }

                                            if (!$oldItem) {
                                                $diff[] = ['type' => 'added', 'item' => $newItem];
                                            } elseif ($oldItem['qty'] != $newItem['qty']) {
                                                $diff[] = ['type' => 'changed', 'item' => $newItem, 'old_qty' => $oldItem['qty']];
                                            }
                                        }
                                        foreach ($oldCart as $oldItem) {
                                            $newItem = $newCart->firstWhere('service_type_id', $oldItem['service_type_id']);
                                            if (!$newItem && !isset($oldItem['service_type_id'])) {
                                                $newItem = $newCart->firstWhere('name', $oldItem['name']);
                                            }
                                            if (!$newItem) {
                                                $diff[] = ['type' => 'removed', 'item' => $oldItem];
                                            }
                                        }
                                    @endphp

                                    @if(count($diff) > 0)
                                        <div class="mt-2 text-[11px]">
                                            <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Cart Updates:</strong>
                                            <div class="mt-1 space-y-1 bg-white/50 rounded-lg p-2 border border-slate-100">
                                                @foreach($diff as $change)
                                                    @if($change['type'] === 'added')
                                                        <div class="flex items-center gap-1.5"><span
                                                                class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase">Add</span>
                                                            <span class="font-bold text-slate-800">{{ $change['item']['qty'] ?? 1 }}x
                                                                {{ $change['item']['name'] ?? 'Service' }}</span>
                                                            <span
                                                                class="text-slate-500 ml-auto">(Rs.{{ number_format((float) ($change['item']['base_price'] ?? 0) * (float) ($change['item']['qty'] ?? 1), 2) }})</span>
                                                        </div>
                                                    @elseif($change['type'] === 'changed')
                                                        <div class="flex items-center gap-1.5"><span
                                                                class="bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase">Mod</span>
                                                            <span class="text-slate-400 line-through font-bold">{{ $change['old_qty'] }}x</span>
                                                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor"
                                                                stroke-width="2.5" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3">
                                                                </path>
                                                            </svg>
                                                            <span class="font-bold text-slate-800">{{ $change['item']['qty'] }}x
                                                                {{ $change['item']['name'] ?? 'Service' }}</span>
                                                        </div>
                                                    @elseif($change['type'] === 'removed')
                                                        <div class="flex items-center gap-1.5"><span
                                                                class="bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase">Del</span>
                                                            <span class="text-red-400 line-through font-bold">{{ $change['item']['qty'] ?? 1 }}x
                                                                {{ $change['item']['name'] ?? 'Service' }}</span></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                        @if($cr->status !== 'pending' && $cr->approvedBy)
                            <div class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                                {{ ucfirst($cr->status) }} by <strong class="text-slate-700">{{ $cr->approvedBy->name }}</strong> on
                                {{ $cr->resolved_at ? $cr->resolved_at->format('M d, Y h:i A') : 'Unknown' }}
                            </div>
                        @endif
                    </div>

                    @if($cr->status === 'pending' && in_array(auth()->user()->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']))
                        <div class="flex flex-col gap-2 shrink-0">
                            <form action="{{ route('corrections.approve', $cr->id) }}" method="POST"
                                onsubmit="return confirm('Approve this request? This will permanently alter the order details.')">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[11px] uppercase tracking-wide rounded-lg shadow-sm transition-colors flex items-center justify-center gap-1.5"><svg
                                        class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                    </svg>Approve</button>
                            </form>
                            <form action="{{ route('corrections.reject', $cr->id) }}" method="POST"
                                onsubmit="return confirm('Reject this request?')">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-black text-[11px] uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"><svg
                                        class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex flex-col gap-4 items-start">

        <!-- ======= MAIN COLUMN: Status + Items ======= -->
        <div class="w-full flex flex-col gap-4">

            <!-- Status Progress -->
            <div
                style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <div class="mb-4 px-2">
                    <h3 class="text-sm font-black text-slate-800 mb-4">Order Journey</h3>
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="flex-1 relative">
                            <div class="absolute left-[15px] top-4 bottom-4 w-[2px] bg-slate-100"></div>
                            <div class="space-y-5">
                                @php
                                    $steps = collect();
                                    $history = $order->statusHistory->sortBy('created_at')->values();
                                    $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)->first();

                                    function getLog($history, $status, $noteMatch = null)
                                    {
                                        return $history->first(function ($log) use ($status, $noteMatch) {
                                            if ($noteMatch && str_contains(strtolower($log->note ?? ''), strtolower($noteMatch))) {
                                                return true;
                                            }
                                            return $log->status === $status;
                                        });
                                    }

                                    if ($order->is_delivery_order) {
                                        // 1. Customer Place Order (Appointment)
                                        $steps->push([
                                            'label' => 'Customer Order Placed',
                                            'icon' => '📅',
                                            'time' => $order->deliveryAppointment?->created_at,
                                            'user' => $order->customer->name,
                                            'active' => true
                                        ]);

                                        // 2. Order Confirm (Received)
                                        $steps->push([
                                            'label' => 'Order Confirmed',
                                            'icon' => '✅',
                                            'time' => $order->created_at,
                                            'user' => $order->createdBy?->name ?? 'System',
                                            'active' => true
                                        ]);

                                        // 3. Rider Assign (Task)
                                        $pickupTask = \App\Models\DeliveryTask::where('delivery_appointment_id', $order->delivery_appointment_id)->where('task_type', 'pickup')->first();
                                        $steps->push([
                                            'label' => 'Rider Assigned',
                                            'icon' => '🏍️',
                                            'time' => $pickupTask?->created_at,
                                            'user' => $pickupTask?->rider?->name ?? 'System',
                                            'active' => $pickupTask != null || $order->deliveryAppointment?->delivery_method === 'pickme_flash'
                                        ]);

                                        // 4. Workshop Received
                                        $steps->push([
                                            'label' => 'Workshop Received',
                                            'icon' => '📥',
                                            'time' => $dispatch?->received_at_workshop_at,
                                            'user' => $dispatch?->receivedBy?->name ?? 'System',
                                            'active' => $dispatch?->received_at_workshop_at != null
                                        ]);

                                        // 5. Payment & Start (Processing)
                                        $firstPayment = $order->paymentTransactions()->orderBy('created_at')->first();
                                        $processingLog = getLog($history, 'processing', 'processing started');

                                        $payLabel = 'Payment & Processing Started';
                                        if ($firstPayment) {
                                            $payLabel .= ' (Paid Rs.' . number_format($firstPayment->amount, 0) . ' at ' . $firstPayment->created_at->format('H:i') . ')';
                                        }

                                        $steps->push([
                                            'label' => $payLabel,
                                            'icon' => '⚙️',
                                            'time' => $processingLog?->created_at ?? $firstPayment?->created_at,
                                            'user' => $processingLog?->changedBy?->name ?? $firstPayment?->createdBy?->name ?? 'System',
                                            'active' => $processingLog != null || $order->status === 'processing' || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                        ]);

                                        // 6. Done (Workshop Completed)
                                        $doneWorkshopLog = getLog($history, 'done', 'completed at workshop');
                                        $steps->push([
                                            'label' => 'Done',
                                            'icon' => '✨',
                                            'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                            'user' => $doneWorkshopLog?->changedBy?->name ?? $dispatch?->completedBy?->name ?? 'System',
                                            'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                        ]);

                                        // 7. Pay Full Payment & Completed (Delivered)
                                        $completedLog = getLog($history, 'completed');
                                        $deliveredLog = getLog($history, 'delivered');
                                        $finalLog = $completedLog ?? $deliveredLog;

                                        $steps->push([
                                            'label' => 'Full Payment & Completed',
                                            'icon' => '🤝',
                                            'time' => $finalLog?->created_at ?? $order->delivered_at,
                                            'user' => $finalLog?->changedBy?->name ?? 'System',
                                            'active' => in_array($order->status, ['delivered', 'completed'])
                                        ]);

                                    } else {
                                        // WALK-IN ORDER FLOW

                                        // 1. Order Received
                                        $steps->push([
                                            'label' => 'Order Received',
                                            'icon' => '📥',
                                            'time' => $order->created_at,
                                            'user' => $order->createdBy?->name ?? 'System',
                                            'active' => true
                                        ]);

                                        // 2. Transfer to Workshop
                                        $dispatchLog = getLog($history, 'dispatched_to_workshop', 'dispatched');
                                        $steps->push([
                                            'label' => 'Transfer to Workshop',
                                            'icon' => '📤',
                                            'time' => $dispatchLog?->created_at ?? $dispatch?->dispatched_at,
                                            'user' => $dispatchLog?->changedBy?->name ?? $dispatch?->dispatchedBy?->name ?? 'System',
                                            'active' => $dispatch != null
                                        ]);

                                        // 3. Arrive Workshop & Washing (Processing)
                                        $arriveLog = getLog($history, 'received_at_workshop');
                                        $processingLog = getLog($history, 'processing', 'processing started');
                                        $steps->push([
                                            'label' => 'Arrive Workshop & Washing',
                                            'icon' => '⚙️',
                                            'time' => $arriveLog?->created_at ?? $processingLog?->created_at ?? $dispatch?->received_at_workshop_at,
                                            'user' => $arriveLog?->changedBy?->name ?? $processingLog?->changedBy?->name ?? $dispatch?->receivedBy?->name ?? 'System',
                                            'active' => $arriveLog != null || $processingLog != null || $dispatch?->received_at_workshop_at != null || in_array($order->status, ['processing', 'returned_from_workshop', 'done', 'delivered'])
                                        ]);

                                        // 4. Done (at Workshop)
                                        $doneWorkshopLog = getLog($history, 'done', 'completed at workshop');
                                        $steps->push([
                                            'label' => 'Done',
                                            'icon' => '✨',
                                            'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                            'user' => $doneWorkshopLog?->changedBy?->name ?? $dispatch?->completedBy?->name ?? 'System',
                                            'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                        ]);

                                        // 5. Delivery to Branch (Returning)
                                        $returnLog = getLog($history, 'returned_from_workshop', 'dispatched back');
                                        $steps->push([
                                            'label' => 'Delivery to Branch',
                                            'icon' => '🚚',
                                            'time' => $returnLog?->created_at ?? $dispatch?->returned_at,
                                            'user' => $returnLog?->changedBy?->name ?? $dispatch?->returnedBy?->name ?? 'System',
                                            'active' => $returnLog != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                        ]);

                                        // 6. Received to Branch (Done state)
                                        $doneLog = getLog($history, 'done', 'status updated to done');
                                        $steps->push([
                                            'label' => 'Received to Branch',
                                            'icon' => '🏢',
                                            'time' => $doneLog?->created_at ?? $dispatch?->received_at_branch_at,
                                            'user' => $doneLog?->changedBy?->name ?? $dispatch?->receivedAtBranchBy?->name ?? 'System',
                                            'active' => $doneLog != null || in_array($order->status, ['done', 'delivered'])
                                        ]);

                                        // 7. Delivered to Customer
                                        $completedLog = getLog($history, 'completed');
                                        $deliveredLog = getLog($history, 'delivered');
                                        $finalLog = $completedLog ?? $deliveredLog;

                                        $steps->push([
                                            'label' => 'Delivered to Customer',
                                            'icon' => '🤝',
                                            'time' => $finalLog?->created_at ?? $order->delivered_at,
                                            'user' => $finalLog?->changedBy?->name ?? 'System',
                                            'active' => in_array($order->status, ['delivered', 'completed'])
                                        ]);
                                    }
                                @endphp

                                @foreach($steps as $step)
                                    <div
                                        class="flex items-start gap-4 relative {{ !$step['active'] ? 'opacity-40 grayscale' : '' }}">
                                        <div
                                            class="w-8 h-8 rounded-full flex items-center justify-center text-sm z-10 flex-shrink-0 shadow-sm {{ $step['active'] ? 'bg-blue-600 text-white border-2 border-white ring-4 ring-blue-50' : 'bg-slate-200 text-slate-500 border-2 border-white' }}">
                                            {{ $step['icon'] }}
                                        </div>
                                        <div class="flex-1 pt-1.5 pb-5">
                                            <div class="flex items-baseline justify-between gap-2">
                                                <div
                                                    class="text-[13px] font-black {{ $step['active'] ? 'text-slate-800' : 'text-slate-500' }}">
                                                    {{ $step['label'] }}
                                                </div>
                                                @if($step['time'])
                                                    <div
                                                        class="text-[11px] font-bold whitespace-nowrap {{ $step['active'] ? 'text-blue-600' : 'text-slate-400' }}">
                                                        {{ \Carbon\Carbon::parse($step['time'])->format('d M, H:i') }}
                                                    </div>
                                                @endif
                                            </div>
                                            @if($step['active'] && $step['user'])
                                                <div class="text-[11px] text-slate-500 font-semibold mt-0.5">By
                                                    {{ $step['user'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right Side: Details -->
                        <div
                            class="w-full md:w-[260px] flex flex-col gap-6 pt-4 md:pt-0 md:pl-6 md:border-l border-slate-100">

                            <!-- Timeline Info -->
                            <div>
                                <h3
                                    style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                    Timeline</h3>
                                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span
                                            style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Order
                                            Date</span>
                                        <p style="font-weight:700;color:#0f172a;margin:0;">
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span
                                            style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup
                                            Date</span>
                                        <p style="font-weight:700;color:#0f172a;margin:0;">
                                            {{ $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('M d, Y h:i A') : 'Not specified' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div>
                                <h3
                                    style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                    Customer</h3>
                                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                                    <div>
                                        <span
                                            style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Full
                                            Name</span>
                                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                            {{ $order->customer->name ?? 'Unknown Customer' }}
                                        </p>
                                    </div>
                                    <div>
                                        <span
                                            style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Phone</span>
                                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                            {{ $order->customer->phone ?? 'N/A' }}
                                        </p>
                                    </div>
                                    @if(optional($order->customer)->address)
                                        <div>
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Address</span>
                                            <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                {{ $order->customer->address }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($order->is_delivery_order && $order->deliveryAppointment)
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                                <div>
                                    <h3
                                        style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                        Logistics & Delivery</h3>
                                    <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                                        <div>
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup
                                                Address</span>
                                            <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                {{ $order->deliveryAppointment->pickup_address ?? 'N/A' }}
                                            </p>
                                        </div>
                                        @if($order->deliveryAppointment->pickup_scheduled_at)
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup
                                                    Scheduled</span>
                                                <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                    {{ \Carbon\Carbon::parse($order->deliveryAppointment->pickup_scheduled_at)->format('M d, Y h:i A') }}
                                                </p>
                                            </div>
                                        @endif
                                        <div style="margin-top:4px;">
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                Preference</span>
                                            <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                @if(in_array($order->deliveryAppointment->delivery_preference, ['pickup_from_branch', 'pickup_from_store', 'self_pickup']))
                                                    Customer Pickup from Branch
                                                @else
                                                    Deliver to Address
                                                @endif
                                            </p>
                                        </div>
                                        @if(!in_array($order->deliveryAppointment->delivery_preference, ['pickup_from_branch', 'pickup_from_store', 'self_pickup']))
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                    Address</span>
                                                <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                    {{ $order->deliveryAppointment->delivery_address ?: ($order->deliveryAppointment->pickup_address ?? 'N/A') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if($order->deliveryAppointment->delivery_scheduled_at)
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                    Scheduled</span>
                                                <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                    {{ \Carbon\Carbon::parse($order->deliveryAppointment->delivery_scheduled_at)->format('M d, Y h:i A') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                            @else
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                            @endif

                            <!-- Payment Info -->
                            <div>
                                <h3
                                    style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                    Payment</h3>
                                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span style="color:#64748b;font-weight:600;">Total Bill</span>
                                        <span
                                            style="font-weight:800;color:#0f172a;">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span style="color:#64748b;font-weight:600;">Paid Amount</span>
                                        <span
                                            style="font-weight:800;color:#16a34a;">Rs.{{ number_format($order->paid_amount, 2) }}</span>
                                    </div>
                                    @if($pickmeCost > 0)
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <span style="color:#64748b;font-weight:600;">PickMe Expense (Shop Paid)</span>
                                            <span style="font-weight:800;color:#dc2626;">-
                                                Rs.{{ number_format($pickmeCost, 2) }}</span>
                                        </div>
                                    @endif
                                    <div
                                        style="display:flex;justify-content:space-between;align-items:center;padding-bottom:6px;border-bottom:1px solid #f1f5f9;">
                                        <span style="color:#64748b;font-weight:600;">Balance Due</span>
                                        <span
                                            style="font-weight:900;color:#dc2626;">Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span>
                                    </div>
                                    <div
                                        style="display:flex;justify-content:space-between;align-items:center;margin-top:2px;">
                                        <span style="color:#64748b;font-weight:600;">Status</span>
                                        <span
                                            style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;
                                            {{ $order->payment_status === 'paid' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;' }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </div>
                                    @if($order->payment_method)
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <span style="color:#64748b;font-weight:600;">Method</span>
                                            <span
                                                style="font-weight:700;color:#334155;text-transform:capitalize;">{{ $order->payment_method }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

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
                                        $adminDisplayRows->push([
                                            'type' => 'item',
                                            'name' => $item->serviceType ? ($item->serviceType->category ? $item->serviceType->category->name . ' - ' : '') . $item->serviceType->name : $item->item_description,
                                            'turnaround_name' => ($item->turnaroundType && !$item->turnaroundType->is_default) ? $item->turnaroundType->name : null,
                                            'extras' => $item->extras->map(fn($e) => ['name' => optional($e->extra)->name, 'qty' => $e->quantity, 'price' => $e->unit_price]),
                                            'qty' => $item->quantity,
                                            'unit_price' => $unitWithTurnaround,
                                            'total_price' => $item->total_price,
                                            'turnaround_charge' => 0,
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

                                        $adminDisplayRows->push([
                                            'type' => 'item',
                                            'name' => $svcName,
                                            'turnaround_name' => $ttName,
                                            'extras' => collect($extras),
                                            'qty' => $qty,
                                            'unit_price' => $qty > 0 ? (($lineTotal + $ttCharge) / $qty) : ($lineTotal + $ttCharge),
                                            'total_price' => $lineTotal + $ttCharge,
                                            'turnaround_charge' => 0,
                                        ]);
                                    }
                                    $cartSubtotal = $calcSub;
                                }
                            @endphp

                            @forelse($adminDisplayRows as $row)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td style="padding:10px 16px;font-weight:700;color:#0f172a;">
                                        {{ $row['name'] }}
                                        @if($row['turnaround_name'])
                                            <span
                                                style="display:inline-block;margin-left:6px;font-size:10px;font-weight:800;padding:1px 6px;border-radius:4px;background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;">
                                                ⚡ {{ $row['turnaround_name'] }}
                                            </span>
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

            <!-- ======= PAYMENT SLIPS ======= -->
            <div class="w-full flex flex-col gap-4 mt-2">

                <!-- Storage Location / Cubby -->
                @php
                    $paymentTxs = \App\Models\PaymentTransaction::where('order_id', $order->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp

                @if($paymentTxs->count() > 0)
                    <div
                        style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3
                            style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Payment Slips</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($paymentTxs as $index => $tx)
                                <div x-data="{ openReviewModal: false }"
                                    style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:8px;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div
                                                style="width:32px;height:32px;background:#eef2ff;color:#4f46e5;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </div>
                                            <div>
                                                <p style="font-size:11.5px;font-weight:800;color:#0f172a;margin:0;">
                                                    Rs.{{ number_format($tx->amount, 2) }}</p>
                                                <p style="font-size:10px;color:#64748b;margin:0;font-weight:600;">
                                                    {{ $tx->created_at->format('M d, h:i A') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($tx->status === 'pending')
                                            <span
                                                style="background:#fef9c3;color:#a16207;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fef08a;text-transform:uppercase;">Pending</span>
                                        @elseif($tx->status === 'completed')
                                            <span
                                                style="background:#f0fdf4;color:#16a34a;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #bbf7d0;text-transform:uppercase;">Approved</span>
                                        @elseif($tx->status === 'rejected')
                                            <span
                                                style="background:#fef2f2;color:#dc2626;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;text-transform:uppercase;">Rejected</span>
                                        @endif
                                    </div>

                                    <div style="display:flex;gap:6px;margin-top:4px;">
                                        @if($tx->receipt_path)
                                            <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                target="_blank"
                                                style="flex:1;text-align:center;padding:6px;background:white;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;transition:all 0.15s;"
                                                onmouseover="this.style.background='#f1f5f9'"
                                                onmouseout="this.style.background='white'">View Slip</a>
                                        @else
                                            <span
                                                style="flex:1;text-align:center;padding:6px;background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0;border-radius:6px;font-size:11px;font-weight:700;">No
                                                Slip (Card)</span>
                                        @endif

                                        @if($tx->status === 'pending')
                                            <button type="button" @click="openReviewModal = true"
                                                style="flex:1;padding:6px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(79,70,229,0.3);">Review</button>
                                        @endif
                                    </div>

                                    @if($tx->status === 'rejected')
                                        <div
                                            style="font-size:10px;color:#b91c1c;background:#fef2f2;padding:6px;border-radius:4px;margin-top:2px;">
                                            <strong
                                                style="text-transform:uppercase;letter-spacing:0.05em;font-size:9px;">Reason:</strong><br>
                                            {{ $tx->reject_reason }}
                                        </div>
                                    @endif

                                    <!-- Review Modal -->
                                    <template x-teleport="body">
                                        <div x-show="openReviewModal" style="display:none;">
                                            <div
                                                style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:9999;">
                                                <div @click.outside="openReviewModal = false"
                                                    style="background:white;border-radius:16px;padding:20px;width:100%;max-width:350px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);">
                                                    <div
                                                        style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                                        <h3 style="font-size:15px;font-weight:900;color:#0f172a;margin:0;">
                                                            Review Payment</h3>
                                                        <button @click="openReviewModal = false"
                                                            style="background:none;border:none;cursor:pointer;color:#64748b;font-size:16px;line-height:1;">✕</button>
                                                    </div>

                                                    @if($tx->receipt_path)
                                                        <!-- Scrollable Image -->
                                                        <div
                                                            style="width:100%;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;overflow-y:auto;overflow-x:hidden;max-height:220px;margin-bottom:12px;">
                                                            <img src="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                                alt="Payment Slip" style="width:100%;height:auto;display:block;">
                                                        </div>
                                                        <div style="text-align:right;margin-bottom:12px;">
                                                            <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                                target="_blank"
                                                                style="font-size:11px;color:#4f46e5;font-weight:700;text-decoration:none;"><i
                                                                    class="fa-solid fa-arrow-up-right-from-square"></i> Open in New
                                                                Tab</a>
                                                        </div>
                                                    @else
                                                        <!-- No Image Info -->
                                                        <div
                                                            style="padding:24px 20px;text-align:center;background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border-radius:16px;border:1px solid #e2e8f0;margin-bottom:16px;box-shadow:inset 0 2px 4px rgba(255,255,255,0.7), 0 4px 12px rgba(0,0,0,0.02);position:relative;overflow:hidden;">
                                                            <!-- Decorative circles -->
                                                            <div
                                                                style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);border-radius:50%;opacity:0.5;">
                                                            </div>
                                                            <div
                                                                style="position:absolute;bottom:-30px;left:-10px;width:60px;height:60px;background:linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);border-radius:50%;opacity:0.4;">
                                                            </div>

                                                            <div style="position:relative;z-index:1;">
                                                                <div
                                                                    style="width:56px;height:56px;background:linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 8px 16px rgba(79,70,229,0.25);transform:rotate(-5deg);">
                                                                    <i class="fa-solid fa-credit-card"
                                                                        style="font-size:24px;color:white;transform:rotate(5deg);"></i>
                                                                </div>
                                                                <h4
                                                                    style="font-size:16px;font-weight:900;color:#0f172a;margin:0 0 6px;letter-spacing:-0.02em;">
                                                                    Online Card Payment</h4>
                                                                <p
                                                                    style="font-size:12px;color:#64748b;margin:0;line-height:1.5;font-weight:500;padding:0 10px;">
                                                                    No physical slip was uploaded.<br>
                                                                    <span style="color:#475569;font-weight:700;">Please verify with
                                                                        your payment gateway.</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div
                                                        style="margin-bottom:12px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;font-size:12px;color:#334155;display:flex;justify-content:space-between;align-items:center;">
                                                        <div>
                                                            <span
                                                                style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Amount
                                                                Paid</span>
                                                            <span
                                                                style="font-size:16px;font-weight:900;color:#0f172a;">Rs.{{ number_format($tx->amount, 2) }}</span>
                                                        </div>
                                                        <div style="text-align:right;">
                                                            <span
                                                                style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Time</span>
                                                            <span
                                                                style="font-size:11px;font-weight:700;color:#334155;">{{ $tx->created_at->format('M d, h:i A') }}</span>
                                                        </div>
                                                    </div>

                                                    <div x-data="{ mode: 'review' }">
                                                        <!-- Action Buttons -->
                                                        <div x-show="mode === 'review'"
                                                            style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                            <form method="POST"
                                                                action="{{ route('orders.payments.approve', [$order->order_number, $tx->id]) }}"
                                                                style="margin:0;">
                                                                @csrf
                                                                <button type="submit"
                                                                    style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #16a34a, #15803d);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;"
                                                                    onmouseover="this.style.transform='translateY(-1px)'"
                                                                    onmouseout="this.style.transform='translateY(0)'">
                                                                    <i class="fa-solid fa-check"></i> Approve
                                                                </button>
                                                            </form>
                                                            <button type="button" @click="mode = 'reject'"
                                                                style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #dc2626, #b91c1c);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;"
                                                                onmouseover="this.style.transform='translateY(-1px)'"
                                                                onmouseout="this.style.transform='translateY(0)'">
                                                                <i class="fa-solid fa-xmark"></i> Reject
                                                            </button>
                                                        </div>

                                                        <!-- Reject Form -->
                                                        <form x-cloak x-show="mode === 'reject'" method="POST"
                                                            action="{{ route('orders.payments.reject', [$order->order_number, $tx->id]) }}"
                                                            style="margin-top:8px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                                                            @csrf
                                                            <div>
                                                                <label
                                                                    style="font-size:11px;font-weight:800;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:6px;"><i
                                                                        class="fa-solid fa-circle-info"></i> Rejection
                                                                    Reason</label>
                                                                <textarea name="reject_reason" required rows="2"
                                                                    placeholder="e.g. Unclear image..."
                                                                    style="width:100%;padding:10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#334155;outline:none;resize:none;"
                                                                    onfocus="this.style.borderColor='#dc2626'"
                                                                    onblur="this.style.borderColor='#cbd5e1'"></textarea>
                                                            </div>
                                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                                <button type="button" @click="mode = 'review'"
                                                                    style="width:100%;padding:12px;background:white;color:#64748b;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;">Cancel</button>
                                                                <button type="submit"
                                                                    style="width:100%;padding:12px;background:#dc2626;color:white;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);">Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($order->cubbies->count())
                    <div
                        style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3
                            style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Storage</h3>
                        @foreach($order->cubbies->sortByDesc('assigned_at') as $oc)
                            <div
                                style="display:flex;align-items:center;gap:10px;margin-bottom:8px;{{ $oc->released_at ? 'opacity:0.5;' : '' }}">
                                <div
                                    style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">
                                    {{ $oc->cubby->name ?? '?' }}
                                </div>
                                <div style="flex:1;">
                                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubby:
                                        {{ $oc->cubby->name ?? 'N/A' }}
                                    </p>
                                    <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">
                                        Assigned: {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}
                                        &bull; Loc: {{ $oc->cubby->branch->name ?? 'Unknown' }}
                                    </p>
                                </div>
                                @if($oc->released_at)
                                    <span
                                        style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Released</span>
                                @else
                                    <span
                                        style="font-size:9px;font-weight:800;color:#16a34a;background:#f0fdf4;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Active</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Order Notes Card -->
                @if($order->notes)
                    <div style="background:#fffbeb;border:1.5px solid #fef3c7;border-radius:12px;padding:12px;">
                        <span
                            style="font-size:10px;font-weight:800;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Order
                            Notes</span>
                        <p style="font-size:12px;color:#b45309;margin:0;font-weight:600;line-height:1.4;">
                            {{ $order->notes }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if($order->is_delivery_order && $order->deliveryAppointment)
            <!-- Maps Section -->
            <div x-data="orderMaps()" style="margin-top:24px;">
                <h2 class="ph-title" style="margin-bottom:12px; font-size:16px;">Logistics Overview</h2>
                <div style="display:flex;gap:24px;flex-wrap:wrap;">

                    @if($order->deliveryAppointment->latitude && $order->deliveryAppointment->longitude)
                        <!-- Pickup Map -->
                        <div
                            style="flex:1;min-width:300px;background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.02);">
                            <div
                                style="padding:12px 16px;background:#fff7ed;border-bottom:1px solid #ffedd5;display:flex;align-items:center;gap:10px;">
                                <div
                                    style="width:28px;height:28px;background:#ffedd5;color:#ea580c;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                    <i class="fa-solid fa-arrow-up"></i></div>
                                <div>
                                    <div
                                        style="font-size:10px;font-weight:900;color:#ea580c;text-transform:uppercase;letter-spacing:0.05em;">
                                        Pickup Route</div>
                                    <div style="font-size:12px;font-weight:800;color:#431407;margin-top:2px;">Customer to Branch
                                    </div>
                                </div>
                            </div>
                            <div id="pickup-route-badge"
                                style="display:none;padding:8px 14px;background:linear-gradient(135deg,#ea580c,#f97316);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                <span id="pickup-route-text">Calculating route...</span>
                            </div>
                            <div id="pickup-route-map" style="width:100%;height:300px;background:#f8fafc;z-index:1;"></div>
                        </div>
                    @endif

                    @if($order->deliveryAppointment->delivery_lat && $order->deliveryAppointment->delivery_lng)
                        <!-- Delivery Map -->
                        <div
                            style="flex:1;min-width:300px;background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.02);">
                            <div
                                style="padding:12px 16px;background:#f0fdf4;border-bottom:1px solid #dcfce7;display:flex;align-items:center;gap:10px;">
                                <div
                                    style="width:28px;height:28px;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                    <i class="fa-solid fa-arrow-down"></i></div>
                                <div>
                                    <div
                                        style="font-size:10px;font-weight:900;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">
                                        Delivery Route</div>
                                    <div style="font-size:12px;font-weight:800;color:#166534;margin-top:2px;">Branch to Customer
                                    </div>
                                </div>
                            </div>
                            <div id="route-distance-badge"
                                style="display:none;padding:8px 14px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                <span id="route-distance-text">Calculating route...</span>
                            </div>
                            <div id="delivery-route-map" style="width:100%;height:300px;background:#f8fafc;z-index:1;"></div>
                        </div>
                    @endif

                </div>
            </div>

            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('orderMaps', () => ({
                        pickupMap: null,
                        deliveryMap: null,
                        pLat: {{ $order->deliveryAppointment->latitude ?? 'null' }},
                        pLng: {{ $order->deliveryAppointment->longitude ?? 'null' }},
                        dLat: {{ $order->deliveryAppointment->delivery_lat ?? 'null' }},
                        dLng: {{ $order->deliveryAppointment->delivery_lng ?? 'null' }},
                        bLat: {{ $order->branch->latitude ?? 'null' }},
                        bLng: {{ $order->branch->longitude ?? 'null' }},
                        bName: '{{ addslashes($order->branch->name ?? "Branch") }}',
                        init() {
                            setTimeout(() => this.initMaps(), 300);
                        },
                        initMaps() {
                            let branchIcon = L.divIcon({
                                html: '<div style="background:#0f172a;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-store" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -16]
                            });
                            let pickupIcon = L.divIcon({
                                html: '<div style="background:#ea580c;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-arrow-up" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
                            });
                            let customerIcon = L.divIcon({
                                html: '<div style="background:#16a34a;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 4px 6px rgba(0,0,0,0.2);"><i class="fa-solid fa-house" style="font-size:12px;"></i></div>',
                                className: '', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32]
                            });

                            if (this.pLat && this.pLng && document.getElementById('pickup-route-map')) {
                                this.pickupMap = L.map('pickup-route-map').setView([this.pLat, this.pLng], 13);
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.pickupMap);

                                L.marker([this.pLat, this.pLng], { icon: pickupIcon }).bindPopup('<b>Customer Pickup</b>').addTo(this.pickupMap);

                                if (this.bLat && this.bLng) {
                                    L.marker([this.bLat, this.bLng], { icon: branchIcon }).bindPopup('<b>' + this.bName + '</b>').addTo(this.pickupMap);

                                    document.getElementById('pickup-route-badge').style.display = 'block';
                                    fetch(`https://router.project-osrm.org/route/v1/driving/${this.bLng},${this.bLat};${this.pLng},${this.pLat}?overview=full&geometries=geojson`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, { color: '#ea580c', weight: 4, opacity: 0.8 }).addTo(this.pickupMap);
                                                document.getElementById('pickup-route-text').innerHTML = `${this.bName} &rarr; Pickup &nbsp;|&nbsp; ${distKm} km &nbsp;|&nbsp; ~${durationMin} min`;
                                                this.pickupMap.fitBounds(L.latLngBounds(routeCoords), { padding: [25, 25] });
                                            }
                                        }).catch(() => document.getElementById('pickup-route-text').textContent = 'Route unavailable');
                                }
                            }

                            if (this.dLat && this.dLng && document.getElementById('delivery-route-map')) {
                                this.deliveryMap = L.map('delivery-route-map').setView([this.dLat, this.dLng], 13);
                                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.deliveryMap);

                                L.marker([this.dLat, this.dLng], { icon: customerIcon }).bindPopup('<b>Customer Delivery</b>').addTo(this.deliveryMap);

                                if (this.bLat && this.bLng) {
                                    L.marker([this.bLat, this.bLng], { icon: branchIcon }).bindPopup('<b>' + this.bName + '</b>').addTo(this.deliveryMap);

                                    document.getElementById('route-distance-badge').style.display = 'block';
                                    fetch(`https://router.project-osrm.org/route/v1/driving/${this.bLng},${this.bLat};${this.dLng},${this.dLat}?overview=full&geometries=geojson`)
                                        .then(r => r.json())
                                        .then(data => {
                                            if (data.routes && data.routes.length > 0) {
                                                let route = data.routes[0];
                                                let distKm = (route.distance / 1000).toFixed(1);
                                                let durationMin = Math.ceil(route.duration / 60);
                                                let routeCoords = route.geometry.coordinates.map(c => [c[1], c[0]]);
                                                L.polyline(routeCoords, { color: '#4f46e5', weight: 4, opacity: 0.8 }).addTo(this.deliveryMap);
                                                document.getElementById('route-distance-text').innerHTML = `${this.bName} &rarr; Customer &nbsp;|&nbsp; ${distKm} km &nbsp;|&nbsp; ~${durationMin} min`;
                                                this.deliveryMap.fitBounds(L.latLngBounds(routeCoords), { padding: [25, 25] });
                                            }
                                        }).catch(() => document.getElementById('route-distance-text').textContent = 'Route unavailable');
                                }
                            }
                        }
                    }));
                });
            </script>
        @endif

        <!-- Pay Now Modal -->
        <div x-data="{ openPayModal: false, payAmount: '{{ number_format(max(0, $order->total - $order->paid_amount), 2, '.', '') }}', payMethod: 'cash' }"
            x-on:open-pay-modal.window="openPayModal = true" x-show="openPayModal" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden"
                @click.away="openPayModal = false" x-show="openPayModal" x-transition.scale.origin.bottom>
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-800">Collect Payment</h3>
                    <button @click="openPayModal = false"
                        class="text-slate-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
                </div>

                <form action="{{ route('orders.collectCreditPayment', $order->id) }}" method="POST"
                    class="p-5 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label
                            class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Amount
                            (Rs.)</label>
                        <input type="number" name="amount" x-model="payAmount" step="0.01" min="0.01" required
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[15px] font-black text-slate-800 outline-none transition-all">
                        <p class="text-[10px] font-bold text-slate-400 mt-1">Balance Due:
                            Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</p>
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Payment
                            Method</label>
                        <select name="payment_method" x-model="payMethod" required
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="bank">🏦 Bank Transfer</option>
                        </select>
                    </div>

                    <div class="flex gap-2 mt-2">
                        <button type="button" @click="openPayModal = false"
                            class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl transition-colors hover:bg-slate-200 text-[13px]">Cancel</button>
                        <button type="submit"
                            class="flex-1 py-3 bg-emerald-500 text-white font-black rounded-xl transition-all hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30 text-[13px]">Confirm
                            Payment</button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>