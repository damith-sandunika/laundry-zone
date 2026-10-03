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

    <div class="mb-4 flex flex-wrap gap-3 items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isSuperAdmin())
                <div>
                    <select name="branch_id"
                        class="border-slate-200 rounded-md text-xs font-bold w-[150px] shadow-sm py-1.5 text-slate-600">
                        <option value="">All Branches</option>
                        @foreach($allBranches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                    class="border-slate-200 rounded-md text-xs font-bold w-[130px] shadow-sm py-1.5 text-slate-600">
            </div>
            <div>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                    class="border-slate-200 rounded-md text-xs font-bold w-[130px] shadow-sm py-1.5 text-slate-600">
            </div>
            <button type="submit"
                class="px-3 py-1.5 bg-slate-800 text-white rounded-md text-xs font-bold shadow-sm hover:bg-slate-700 transition-colors">
                Filter
            </button>

            <div class="flex items-center gap-1.5 ml-2 border-l border-slate-200 pl-3">
                @php 
                    $isToday = request('start_date') == now()->format('Y-m-d') && request('end_date') == now()->format('Y-m-d');
                    $isYesterday = request('start_date') == now()->subDay()->format('Y-m-d') && request('end_date') == now()->subDay()->format('Y-m-d');
                @endphp
                <a href="{{ route('orders.history', ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')]) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                    Today
                </a>
                <a href="{{ route('orders.history', ['start_date' => now()->subDay()->format('Y-m-d'), 'end_date' => now()->subDay()->format('Y-m-d')]) }}"
                    class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isYesterday ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                    Yesterday
                </a>
            </div>

            @if (request('start_date') || request('end_date') || request('branch_id'))
                <a href="{{ route('orders.history') }}"
                    class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-md text-xs font-bold hover:bg-slate-200 transition-colors ml-1">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Order Value</span>
            <span
                class="text-lg font-black text-slate-800 mt-1">Rs.{{ number_format($stats['total_order_value'], 2) }}</span>
            <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">🚶 Walk-in</span>
                    <span
                        class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['walk_in'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">🚚 Delivery</span>
                    <span
                        class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['delivery'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending / Due</span>
            <span class="text-lg font-black text-amber-600 mt-1">Rs.{{ number_format($stats['pending_due'], 2) }}</span>
            <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">Regular</span>
                    <span
                        class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['regular_pending'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">Credit</span>
                    <span
                        class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['credit_orders'], 2) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Collected Payments</span>
            <span
                class="text-lg font-black text-emerald-600 mt-1">Rs.{{ number_format($stats['collected_payments'], 2) }}</span>
            <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
                <div class="flex justify-between items-center mb-0.5">
                    <span
                        class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded uppercase">From
                        These Orders</span>
                    <span
                        class="text-[10px] font-black text-emerald-600">Rs.{{ number_format($stats['collected_payments'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">💵 Cash</span>
                    <span class="text-[11px] font-black text-slate-700">{{ number_format($stats['cash'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">💳 Card</span>
                    <span class="text-[11px] font-black text-slate-700">{{ number_format($stats['card'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-bold text-slate-500">🏦 Bank</span>
                    <span class="text-[11px] font-black text-slate-700">{{ number_format($stats['bank'], 0) }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between gap-1.5">
            <div
                class="bg-indigo-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-indigo-100/50 flex-1">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Total Orders</span>
                <span class="text-lg font-black text-indigo-700">{{ $stats['order_qty'] }}</span>
            </div>
            <div
                class="bg-violet-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-violet-100/50 flex-1">
                <span class="text-[10px] font-black text-violet-500 uppercase tracking-widest">Total Qty</span>
                <span class="text-lg font-black text-violet-700">{{ $stats['item_qty'] + 0 }}</span>
            </div>
            <div
                class="bg-blue-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-blue-100/50 flex-1">
                <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Customers</span>
                <span class="text-lg font-black text-blue-700">{{ $stats['total_customer_count'] }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                    <tr>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Total</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Last Updated</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completedOrders as $order)
                    @php $isDeleted = $order->trashed(); @endphp
                    <tr style="background:{{ $isDeleted ? '#fef2f2' : 'white' }};border-bottom:1px solid #f1f5f9;transition:all 0.1s; {{ $isDeleted ? 'opacity: 0.8;' : '' }}" onmouseover="this.style.background='{{ $isDeleted ? '#fee2e2' : '#f8fafc' }}'" onmouseout="this.style.background='{{ $isDeleted ? '#fef2f2' : 'white' }}'">
                        <td style="padding:12px 20px;">
                            <a href="{{ route('orders.show', $order->order_number ?? $order->id) }}" style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:{{ $isDeleted ? 'line-through' : 'underline' }};">
                                #{{ $order->order_number ?? $order->id }}
                            </a>
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#0f172a;">
                            {{ $order->customer->name ?? 'Walk-in' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                            {{ $order->branch->name ?? '—' }}
                        </td>
                        <td style="padding:12px 20px;font-weight:800;color:#0f172a;">
                            Rs. {{ number_format($order->total, 2) }}
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
                                @if($isDeleted) DELETED @else {{ ucfirst(str_replace('_', ' ', $order->status)) }} @endif
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
                            <a href="{{ route('orders.show', $order->order_number) }}"
                               style="display:inline-block;padding:5px 12px;background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.1s;"
                               onmouseover="this.style.background='#e2e8f0';this.style.color='#0f172a';" onmouseout="this.style.background='#f1f5f9';this.style.color='#334155';">
                                View
                            </a>
                            @endif
                            @if(!$isDeleted && auth()->user() && in_array(auth()->user()->role, ['super_admin', 'branch_admin', 'workshop_admin']))
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
