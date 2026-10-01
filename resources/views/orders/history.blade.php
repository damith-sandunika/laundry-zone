<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">All Orders History</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Log of all orders for this branch</p>
            </div>
            <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                    <tr>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Last Updated</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedOrders as $order)
                    <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding:12px 20px;">
                            <a href="{{ route('orders.show', $order->order_number ?? $order->id) }}" style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:underline;">
                                #{{ $order->order_number ?? $order->id }}
                            </a>
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#0f172a;">
                            {{ $order->customer->name ?? 'Walk-in' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                            {{ $order->branch->name ?? '—' }}
                        </td>
                        <td style="padding:12px 20px;">
                            @php
                                $statusColors = [
                                    'pending' => ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a'],
                                    'processing' => ['bg' => '#e0e7ff', 'text' => '#4338ca', 'border' => '#c7d2fe'],
                                    'completed' => ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
                                    'delivered' => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                                    'cancelled' => ['bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca'],
                                ];
                                $st = $statusColors[$order->status] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'];
                            @endphp
                            <span style="display:inline-block;font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;background:{{ $st['bg'] }};color:{{ $st['text'] }};border:1px solid {{ $st['border'] }};">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </td>
                        <td style="padding:12px 20px;font-weight:600;color:#475569;">
                            <span style="display:block;">{{ $order->updated_at?->format('M d, Y') }}</span>
                            <span style="font-size:11px;color:#94a3b8;font-weight:500;">{{ $order->updated_at?->format('h:i A') }}</span>
                        </td>
                        <td style="padding:12px 20px;text-align:center;">
                            <a href="{{ route('orders.show', $order->order_number) }}"
                               style="display:inline-block;padding:5px 12px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;"
                               onmouseover="this.style.background='#e2e8f0';this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9';this.style.color='#334155';">
                                View Order
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
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
</x-app-layout>
