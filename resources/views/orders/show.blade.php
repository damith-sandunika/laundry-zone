<x-app-layout>
    <x-slot name="header">
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
            <div style="display:flex;align-items:center;gap:10px;">
                <a href="{{ route('orders.edit', $order->order_number) }}"
                    style="display:flex;align-items:center;gap:7px;padding:9px 16px;background:white;color:#1d4ed8;border:1.5px solid #bfdbfe;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,0.04);transition:all 0.15s;"
                    onmouseover="this.style.background='#eff6ff';this.style.borderColor='#93c5fd'"
                    onmouseout="this.style.background='white';this.style.borderColor='#bfdbfe'">
                    ✏️ Edit Order
                </a>
                <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank"
                    style="display:flex;align-items:center;gap:7px;padding:9px 16px;background:white;color:#dc2626;border:1.5px solid #fecaca;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,0.04);transition:all 0.15s;"
                    onmouseover="this.style.background='#fef2f2';this.style.borderColor='#fca5a5'"
                    onmouseout="this.style.background='white';this.style.borderColor='#fecaca'">
                    📄 Download PDF
                </a>
                <a href="{{ route('orders.print', $order->order_number) }}" target="_blank"
                    style="display:flex;align-items:center;gap:7px;padding:9px 16px;background:white;color:#334155;border:1.5px solid #e2e8f0;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,0.04);transition:all 0.15s;"
                    onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1'"
                    onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                    🖨️ Print Bill
                </a>
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
    <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;
        {{ $order->status === 'received' ? 'background:#fffbeb;color:#d97706;border:1.5px solid #fde68a;' :
    ($order->status === 'done' ? 'background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;' :
        ($order->status === 'delivered' ? 'background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;' :
            'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;')) }}">
        <span>Order Status:</span>
        <span style="font-size:16px;font-weight:900;">{{ $order->status }}</span>
    </div>

    @if($order->correctionRequests->count() > 0)
        @foreach($order->correctionRequests->sortByDesc('created_at') as $cr)
            <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;font-size:13px;
                        {{ $cr->status === 'pending' ? 'background:#fffbeb;color:#92400e;border:1.5px solid #fde68a;' :
                    ($cr->status === 'approved' ? 'background:#f0fdf4;color:#166534;border:1.5px solid #bbf7d0;' :
                        'background:#fef2f2;color:#991b1b;border:1.5px solid #fecaca;') }}">
                <div>
                    <strong style="text-transform:uppercase;font-size:11px;">{{ ucfirst($cr->status) }} Correction Request
                        ({{ str_replace('_', ' ', $cr->type) }})</strong><br>
                    Requested by <strong>{{ $cr->requestedBy->name ?? 'Unknown' }}</strong> on
                    {{ $cr->created_at->format('M d, Y h:i A') }}<br>
                    <span style="font-style:italic;">"{{ $cr->reason }}"</span>

                    @if(is_array($cr->old_data) && is_array($cr->new_data))
                        @php
                            $oldTotal = $cr->old_data['total'] ?? $order->total;
                            $newTotal = $cr->new_data['calculated_total'] ?? $cr->old_data['total'] ?? $order->total;
                            $oldMethod = $cr->old_data['payment_method'] ?? $order->payment_method;
                            $newMethod = $cr->new_data['payment_method'] ?? $cr->old_data['payment_method'] ?? $order->payment_method;
                        @endphp
                        <div
                            style="margin-top:6px;background:rgba(255,255,255,0.6);padding:8px 12px;border-radius:8px;font-size:11.5px;border:1px solid rgba(0,0,0,0.06);display:inline-block;width:100%;">
                            @if((isset($cr->old_data['total']) || isset($cr->new_data['calculated_total'])) && $oldTotal != $newTotal)
                                <div
                                    style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,0.04);padding-bottom:4px;margin-bottom:4px;">
                                    <strong style="color:#475569;">Total Amount:</strong>
                                    <span>
                                        <span
                                            style="color:#dc2626;text-decoration:line-through;">Rs.{{ number_format($oldTotal, 2) }}</span>
                                        <i class="fa-solid fa-arrow-right" style="font-size:9px;margin:0 6px;color:#94a3b8;"></i>
                                        <strong style="color:#16a34a;">Rs.{{ number_format($newTotal, 2) }}</strong>
                                    </span>
                                </div>
                            @endif
                            @if((isset($cr->old_data['payment_method']) || isset($cr->new_data['payment_method'])) && $oldMethod != $newMethod)
                                <div
                                    style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,0.04);padding-bottom:4px;margin-bottom:4px;">
                                    <strong style="color:#475569;">Payment Method:</strong>
                                    <span>
                                        <span style="color:#dc2626;text-decoration:line-through;">{{ ucfirst($oldMethod) }}</span>
                                        <i class="fa-solid fa-arrow-right" style="font-size:9px;margin:0 6px;color:#94a3b8;"></i>
                                        <strong style="color:#16a34a;">{{ ucfirst($newMethod) }}</strong>
                                    </span>
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
                                    <div style="margin-top:6px; font-size:11px;">
                                        <strong style="color:#475569;">Cart Updates:</strong>
                                        <ul style="padding-left:15px; margin-top:4px; color:#334155; list-style-type:circle;">
                                            @foreach($diff as $change)
                                                @if($change['type'] === 'added')
                                                    <li><span style="color:#16a34a;font-weight:bold;">[+]</span> {{ $change['item']['qty'] ?? 1 }}x
                                                        {{ $change['item']['name'] ?? 'Service' }}
                                                        (Rs.{{ number_format((float) ($change['item']['base_price'] ?? 0) * (float) ($change['item']['qty'] ?? 1), 2) }})
                                                    </li>
                                                @elseif($change['type'] === 'changed')
                                                    <li><span style="color:#ca8a04;font-weight:bold;">[~]</span> <span
                                                            style="text-decoration:line-through;color:#94a3b8;">{{ $change['old_qty'] }}x</span> <i
                                                            class="fa-solid fa-arrow-right" style="font-size:8px;"></i>
                                                        {{ $change['item']['qty'] }}x {{ $change['item']['name'] ?? 'Service' }}</li>
                                                @elseif($change['type'] === 'removed')
                                                    <li><span style="color:#dc2626;font-weight:bold;">[-]</span> <span
                                                            style="text-decoration:line-through;color:#94a3b8;">{{ $change['item']['qty'] ?? 1 }}x
                                                            {{ $change['item']['name'] ?? 'Service' }}</span></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endif
                    @if($cr->status !== 'pending' && $cr->approvedBy)
                        <div style="margin-top:4px;font-size:11px;">
                            {{ ucfirst($cr->status) }} by <strong>{{ $cr->approvedBy->name }}</strong> on
                            {{ $cr->resolved_at ? $cr->resolved_at->format('M d, Y h:i A') : 'Unknown' }}
                        </div>
                    @endif
                </div>

                @if($cr->status === 'pending' && in_array(auth()->user()->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']))
                    <div style="display:flex;gap:8px;">
                        <form action="{{ route('corrections.approve', $cr->id) }}" method="POST"
                            onsubmit="return confirm('Approve this request? This will permanently alter the order details.')">
                            @csrf
                            <button type="submit"
                                style="padding:6px 12px;background:#16a34a;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Approve</button>
                        </form>
                        <form action="{{ route('corrections.reject', $cr->id) }}" method="POST"
                            onsubmit="return confirm('Reject this request?')">
                            @csrf
                            <button type="submit"
                                style="padding:6px 12px;background:#dc2626;color:white;border:none;border-radius:6px;font-weight:bold;cursor:pointer;">Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
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
                                                    {{ $step['label'] }}</div>
                                                @if($step['time'])
                                                    <div
                                                        class="text-[11px] font-bold whitespace-nowrap {{ $step['active'] ? 'text-blue-600' : 'text-slate-400' }}">
                                                        {{ \Carbon\Carbon::parse($step['time'])->format('d M, H:i') }}</div>
                                                @endif
                                            </div>
                                            @if($step['active'] && $step['user'])
                                                <div class="text-[11px] text-slate-500 font-semibold mt-0.5">By
                                                    {{ $step['user'] }}</div>
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
                                            {{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}</p>
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
                                            {{ $order->customer->name ?? 'Unknown Customer' }}</p>
                                    </div>
                                    <div>
                                        <span
                                            style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Phone</span>
                                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                            {{ $order->customer->phone ?? 'N/A' }}</p>
                                    </div>
                                    @if(optional($order->customer)->address)
                                        <div>
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Address</span>
                                            <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                {{ $order->customer->address }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

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
                                        {{ $row['qty'] }}</td>
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
                                            {{ $row['qty'] }}</td>
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
                                            {{ $e['name'] }}</td>
                                        <td style="padding:6px 16px;text-align:center;font-size:11px;color:#6366f1;">
                                            {{ $e['qty'] }}</td>
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
                        ->whereNotNull('receipt_path')
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
                                                    {{ $tx->created_at->format('M d, h:i A') }}</p>
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
                                        <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                            target="_blank"
                                            style="flex:1;text-align:center;padding:6px;background:white;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;transition:all 0.15s;"
                                            onmouseover="this.style.background='#f1f5f9'"
                                            onmouseout="this.style.background='white'">View Slip</a>

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
                                                            Review Slip</h3>
                                                        <button @click="openReviewModal = false"
                                                            style="background:none;border:none;cursor:pointer;color:#64748b;font-size:16px;line-height:1;">✕</button>
                                                    </div>

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
                                        {{ $oc->cubby->name ?? 'N/A' }}</p>
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
                            {{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

</x-app-layout>