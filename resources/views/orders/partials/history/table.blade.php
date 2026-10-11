<div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left" style="border-collapse:collapse;">
            <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                <tr>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Workshop</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Total</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Last Updated</th>
                    <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                </tr>
            </thead>
            @php
                $statusTheme = [
                    'received' => [
                        'row_bg' => '#fffbeb',
                        'row_hover' => '#fef3c7',
                        'row_border' => '#fde68a',
                        'accent' => '#f59e0b',
                        'badge_bg' => '#fef3c7',
                        'badge_text' => '#b45309',
                        'badge_border' => '#fde68a',
                    ],
                    'confirmed' => [
                        'row_bg' => '#faf5ff',
                        'row_hover' => '#f3e8ff',
                        'row_border' => '#e9d5ff',
                        'accent' => '#8b5cf6',
                        'badge_bg' => '#ede9fe',
                        'badge_text' => '#6d28d9',
                        'badge_border' => '#ddd6fe',
                    ],
                    'pending' => [
                        'row_bg' => '#fffdf0',
                        'row_hover' => '#fef9c3',
                        'row_border' => '#fef08a',
                        'accent' => '#eab308',
                        'badge_bg' => '#fef3c7',
                        'badge_text' => '#b45309',
                        'badge_border' => '#fde68a',
                    ],
                    'picked_up' => [
                        'row_bg' => '#f0f9ff',
                        'row_hover' => '#e0f2fe',
                        'row_border' => '#bae6fd',
                        'accent' => '#0284c7',
                        'badge_bg' => '#e0f2fe',
                        'badge_text' => '#0284c7',
                        'badge_border' => '#bae6fd',
                    ],
                    'dispatched_to_workshop' => [
                        'row_bg' => '#fff7ed',
                        'row_hover' => '#ffedd5',
                        'row_border' => '#fed7aa',
                        'accent' => '#ea580c',
                        'badge_bg' => '#ffedd5',
                        'badge_text' => '#c2410c',
                        'badge_border' => '#fed7aa',
                    ],
                    'processing' => [
                        'row_bg' => '#eef2ff',
                        'row_hover' => '#e0e7ff',
                        'row_border' => '#c7d2fe',
                        'accent' => '#4f46e5',
                        'badge_bg' => '#e0e7ff',
                        'badge_text' => '#4338ca',
                        'badge_border' => '#c7d2fe',
                    ],
                    'completed_at_workshop' => [
                        'row_bg' => '#f0fdfa',
                        'row_hover' => '#ccfbf1',
                        'row_border' => '#99f6e4',
                        'accent' => '#0d9488',
                        'badge_bg' => '#ccfbf1',
                        'badge_text' => '#0f766e',
                        'badge_border' => '#99f6e4',
                    ],
                    'returned_from_workshop' => [
                        'row_bg' => '#fdf4ff',
                        'row_hover' => '#fae8ff',
                        'row_border' => '#f5d0fe',
                        'accent' => '#c026d3',
                        'badge_bg' => '#f3e8ff',
                        'badge_text' => '#7e22ce',
                        'badge_border' => '#e9d5ff',
                    ],
                    'done' => [
                        'row_bg' => '#f0fdf4',
                        'row_hover' => '#dcfce7',
                        'row_border' => '#bbf7d0',
                        'accent' => '#16a34a',
                        'badge_bg' => '#dcfce7',
                        'badge_text' => '#15803d',
                        'badge_border' => '#bbf7d0',
                    ],
                    'completed' => [
                        'row_bg' => '#f0fdf4',
                        'row_hover' => '#dcfce7',
                        'row_border' => '#bbf7d0',
                        'accent' => '#16a34a',
                        'badge_bg' => '#dcfce7',
                        'badge_text' => '#15803d',
                        'badge_border' => '#bbf7d0',
                    ],
                    'delivered' => [
                        'row_bg' => '#f8fafc',
                        'row_hover' => '#f1f5f9',
                        'row_border' => '#e2e8f0',
                        'accent' => '#2563eb',
                        'badge_bg' => '#dbeafe',
                        'badge_text' => '#1d4ed8',
                        'badge_border' => '#bfdbfe',
                    ],
                    'cancelled' => [
                        'row_bg' => '#fef2f2',
                        'row_hover' => '#fee2e2',
                        'row_border' => '#fecaca',
                        'accent' => '#dc2626',
                        'badge_bg' => '#fee2e2',
                        'badge_text' => '#b91c1c',
                        'badge_border' => '#fecaca',
                    ],
                ];
            @endphp
            <tbody>
                @forelse($completedOrders as $order)
                @php 
                    $isDeleted = $order->trashed();
                    $effectiveStatus = !empty($order->status) ? $order->status : 'received';
                    $stTheme = $statusTheme[$effectiveStatus] ?? [
                        'row_bg' => '#ffffff',
                        'row_hover' => '#f8fafc',
                        'row_border' => '#f1f5f9',
                        'accent' => '#94a3b8',
                        'badge_bg' => '#f1f5f9',
                        'badge_text' => '#475569',
                        'badge_border' => '#e2e8f0',
                    ];
                    if ($isDeleted) {
                        $rowBg = '#fef2f2';
                        $rowHover = '#fee2e2';
                        $rowBorder = '#fecaca';
                        $rowAccent = '#ef4444';
                    } else {
                        $rowBg = $stTheme['row_bg'];
                        $rowHover = $stTheme['row_hover'];
                        $rowBorder = $stTheme['row_border'];
                        $rowAccent = $stTheme['accent'];
                    }
                @endphp
                <tr style="background:{{ $rowBg }};border-bottom:1px solid {{ $rowBorder }};transition:background 0.15s ease; {{ $isDeleted ? 'opacity: 0.8;' : '' }}" 
                    onmouseover="this.style.background='{{ $rowHover }}'" 
                    onmouseout="this.style.background='{{ $rowBg }}'">
                    <td style="padding:12px 20px;border-left:3.5px solid {{ $rowAccent }};">
                        <a href="{{ route('orders.show', $order->order_number ?? $order->id) }}" style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:{{ $isDeleted ? 'line-through' : 'underline' }};">
                            #{{ $order->order_number ?? $order->id }}
                        </a>
                        <div style="margin-top:2px;">
                            @if($order->is_delivery_order)
                                <span style="font-size:10px;color:#0284c7;font-weight:700;"><i class="fa-solid fa-truck text-[9px]"></i> Delivery</span>
                            @else
                                <span style="font-size:10px;color:#64748b;font-weight:600;"><i class="fa-solid fa-person-walking text-[9px]"></i> Walk-in</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding:12px 20px;font-weight:700;color:#0f172a;">
                        {{ $order->customer->name ?? 'Walk-in' }}
                        @if($order->customer?->phone)
                            <div style="font-size:11px;color:#64748b;font-weight:500;">{{ $order->customer->phone }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 20px;font-weight:700;">
                        @if($order->branch && ($order->branch->type ?? 'branch') === 'branch')
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:#eef2ff;border-radius:6px;font-size:11px;font-weight:800;color:#4338ca;border:1px solid #c7d2fe;">
                                <i class="fa-solid fa-store text-[10px]"></i> {{ $order->branch->name }}
                            </span>
                        @elseif($order->branch && $order->branch->type === 'workshop')
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:#f8fafc;border-radius:6px;font-size:11px;font-weight:700;color:#64748b;border:1px solid #e2e8f0;" title="Order originated directly at workshop without a retail branch">
                                <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i> Direct Workshop
                            </span>
                        @else
                            <span style="color:#94a3b8;font-size:11px;font-weight:500;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 20px;font-weight:700;">
                        @php
                            $workshopName = $order->workshop?->name 
                                ?? $order->latestWorkshopDispatch?->workshopBranch?->name
                                ?? (($order->branch?->type === 'workshop') ? $order->branch->name : null);
                        @endphp
                        @if($workshopName)
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:#f0fdf4;border-radius:6px;font-size:11px;font-weight:800;color:#166534;border:1px solid #bbf7d0;">
                                <i class="fa-solid fa-industry text-[10px]"></i> {{ $workshopName }}
                            </span>
                        @else
                            <span style="color:#94a3b8;font-size:11px;font-weight:500;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 20px;font-weight:800;color:#0f172a;">
                        <div>Rs. {{ number_format($order->total, 2) }}</div>
                        @if($order->payment_status === 'paid')
                            <span style="font-size:10px;color:#16a34a;font-weight:800;">● Paid</span>
                        @elseif($order->payment_status === 'partial')
                            <span style="font-size:10px;color:#ea580c;font-weight:800;">● Partial</span>
                        @else
                            <span style="font-size:10px;color:#dc2626;font-weight:800;">● Unpaid</span>
                        @endif
                    </td>
                    <td style="padding:12px 20px;">
                        <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;background:{{ $stTheme['badge_bg'] }};color:{{ $stTheme['badge_text'] }};border:1px solid {{ $stTheme['badge_border'] }};">
                            @if($isDeleted) DELETED @else {{ ucfirst(str_replace('_', ' ', $effectiveStatus)) }} @endif
                        </span>
                    </td>
                    <td style="padding:12px 20px;font-weight:600;color:#475569;">
                        <span style="display:block;">{{ $order->updated_at?->format('M d, Y') }}</span>
                        <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $order->updated_at?->format('h:i A') }}</span>
                    </td>
                    <td style="padding:12px 20px;text-align:center;">
                        @if($isDeleted && $order->deletedBy)
                            <div style="font-size:10px;color:#ef4444;font-weight:700;">By: {{ $order->deletedBy->name }} <br> <span style="color:#b91c1c;">{{ $order->deleted_at?->format('M d, Y h:i A') }}</span></div>
                        @endif
                        @if(!$isDeleted)
                        <a href="{{ route('orders.show', $order->order_number ?? $order->id) }}"
                           style="display:inline-block;padding:5px 12px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;"
                           onmouseover="this.style.background='#e2e8f0';this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9';this.style.color='#334155';">
                            View
                        </a>
                        @endif
                        @if(!$isDeleted && auth()->user()?->canDeleteOrders())
                            <button onclick="if(confirm('Are you sure you want to delete this order? It will be crossed out and removed from reports.')){ fetch('{{ route('orders.destroy', $order->id) }}', {method:'DELETE', headers:{'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}}).then(r=>r.json()).then(d=>{if(d.success) location.reload(); else alert(d.message);}); }"
                                    style="display:inline-block;padding:5px 12px;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;margin-left:4px;"
                                    onmouseover="this.style.background='#fecaca';" onmouseout="this.style.background='#fee2e2';">
                                Delete
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                        No orders found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($completedOrders->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
        {{ $completedOrders->appends(request()->query())->links() }}
    </div>
    @endif
</div>
