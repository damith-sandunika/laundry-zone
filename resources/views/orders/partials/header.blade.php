<x-slot name="header">
    @include('orders.partials.header.styles')

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <a href="{{ route('orders.index') }}"
                    style="font-size:12px;color:#94a3b8;text-decoration:none;font-weight:700;"
                    onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#94a3b8'">← Orders</a>
                <span style="color:#cbd5e1;font-size:11px;">/</span>
                <span style="font-size:12px;color:#64748b;font-weight:700;">{{ $order->order_number }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h2 class="ph-title" style="margin:0;">Order Details</h2>
                @php
                    $statusColors = [
                        'received' => ['bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd'],
                        'confirmed' => ['bg' => '#ede9fe', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
                        'pending' => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a'],
                        'picked_up' => ['bg' => '#e0f2fe', 'text' => '#0284c7', 'border' => '#bae6fd'],
                        'dispatched_to_workshop' => ['bg' => '#ffedd5', 'text' => '#c2410c', 'border' => '#fed7aa'],
                        'received_at_workshop' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'processing' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'border' => '#c7d2fe'],
                        'completed_at_workshop' => ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
                        'returned_from_workshop' => ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'],
                        'done' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
                        'completed' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
                        'delivered' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                        'cancelled' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca'],
                    ];
                    $st = $statusColors[$order->status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'];
                @endphp
                <span style="display:inline-block;font-size:11px;font-weight:800;padding:3px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;background:{{ $st['bg'] }};color:{{ $st['text'] }};border:1px solid {{ $st['border'] }};">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
            <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                Customer: <span
                    style="font-weight:700;color:#334155;">{{ $order->customer->name ?? 'Unknown Customer' }}</span>
                &bull; {{ $order->created_at->format('M d, Y h:i A') }}
            </p>
        </div>

        {{-- ── Icon Buttons with Hover Tooltip ── --}}
        <div style="display:flex;align-items:center;gap:8px;">
            @include('orders.partials.header.actions')
        </div>
    </div>
</x-slot>
