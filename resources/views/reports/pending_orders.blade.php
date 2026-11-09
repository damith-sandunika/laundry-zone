<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Pending Orders {{ $isWorkshop ? '(Workshop Items)' : '' }}</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">View pending items and orders</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reports.active-orders-pdf', request()->query()) }}" target="_blank"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-slate-800/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="w-full mx-auto py-8">
        <!-- Filters -->
        <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
            <form action="{{ route('reports.pending-orders') }}" method="GET" class="flex flex-wrap gap-2 md:gap-4 items-end">
                @if($isWorkshop)
                    <input type="hidden" name="workshop" value="1">
                @endif
                
                @if($isSuperAdmin)
                <div class="w-full md:flex-1 md:min-w-[200px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Branch</label>
                    <select name="branch_id" id="branch_id" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>🏪 {{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex-1 md:min-w-[150px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                </div>

                <div class="flex-1 md:min-w-[150px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                </div>

                <div class="flex-1 md:min-w-[150px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Type</label>
                    <select name="delivery_type" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">All Types</option>
                        <option value="pickup" {{ $deliveryType === 'pickup' ? 'selected' : '' }}>Walk-in / Branch Pickup</option>
                        <option value="delivery" {{ $deliveryType === 'delivery' ? 'selected' : '' }}>Delivery</option>
                    </select>
                </div>

                <div class="flex-1 md:min-w-[170px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Customer Type</label>
                    <select name="customer_type" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors" onchange="this.form.submit()">
                        <option value="">All Customer Types</option>
                        <option value="walkin" {{ request('customer_type') === 'walkin' ? 'selected' : '' }}>Walk-in Customers</option>
                        <option value="corporate" {{ request('customer_type') === 'corporate' ? 'selected' : '' }}>Corporate Customers</option>
                        <option value="online" {{ request('customer_type') === 'online' ? 'selected' : '' }}>Online Customers</option>
                    </select>
                </div>

                <div class="flex-none flex gap-2">
                    <button type="submit" class="px-3 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl flex items-center gap-1 md:gap-2 shadow-lg shadow-blue-600/25 transition-all h-[34px] md:h-[46px]">
                        <svg width="14" height="14" class="md:w-[18px] md:h-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span class="hidden md:inline">Apply Filter</span>
                        <span class="md:hidden">Apply</span>
                    </button>
                    <a href="{{ route('reports.pending-orders', $isWorkshop ? ['workshop'=>1] : []) }}" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center justify-center">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Order No</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest">Customer</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Type</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest text-center whitespace-nowrap">Created At</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest text-center whitespace-nowrap">Due Date</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest text-right whitespace-nowrap">Price</th>
                            <th class="px-4 py-3 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest text-center whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $orderOrDispatch)
                            @php
                                $order = $isWorkshop ? $orderOrDispatch->order : $orderOrDispatch;
                                $sc = ['received' => ['text-slate-600', 'bg-slate-100'], 'washing' => ['text-blue-700', 'bg-blue-50'], 'drying' => ['text-violet-700', 'bg-violet-50'], 'ready' => ['text-emerald-700', 'bg-emerald-50'], 'delivered' => ['text-teal-700', 'bg-teal-50'], 'cancelled' => ['text-red-700', 'bg-red-50']];
                                $c = $sc[$orderOrDispatch->status] ?? ['text-slate-600', 'bg-slate-100'];
                                $dueDate = $order->due_date ? \Carbon\Carbon::parse($order->due_date) : null;
                                $isOverdue = $dueDate && $dueDate->isPast();
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="{{ route('orders.show', $order->order_number ?? '') }}" class="text-[12px] font-black text-blue-600 hover:text-blue-800 transition-colors">{{ $order->order_number ?? 'N/A' }}</a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-[12px] font-bold text-slate-700">{{ $order->customer->name ?? 'N/A' }}</div>
                                    <div class="text-[10px] font-medium text-slate-500">{{ $order->customer->phone ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($order->is_delivery_order)
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Delivery
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 py-1 px-2.5 rounded-md text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-200">
                                            Pickup
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="text-[11px] font-bold text-slate-800">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] font-semibold text-slate-400">{{ $order->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if($dueDate)
                                        <div class="text-[11px] font-bold {{ $isOverdue ? 'text-red-600' : 'text-slate-800' }}">{{ $dueDate->format('M d, Y') }}</div>
                                        <div class="text-[10px] font-semibold {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">{{ $dueDate->format('h:i A') }}</div>
                                    @else
                                        <span class="text-[11px] font-semibold text-slate-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-[12px] font-bold text-slate-900">
                                    Rs.{{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest {{ $c[0] }} {{ $c[1] }}">{{ ucfirst(str_replace('_', ' ', $orderOrDispatch->status)) }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <a href="{{ route('orders.show', $order->order_number ?? '') }}" class="inline-block px-3 py-1.5 bg-white border border-slate-200 text-slate-600 hover:text-blue-700 hover:border-blue-200 hover:bg-blue-50 rounded-lg text-[10px] font-bold transition-all shadow-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm font-semibold text-slate-400">No pending orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
