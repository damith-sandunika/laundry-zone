<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Pending Payments Report</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">All orders with outstanding balances based on order creation date</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reports.pending-payments.pdf', request()->all()) }}" target="_blank"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-slate-800/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
        <form action="{{ route('reports.pending-payments') }}" method="GET" class="flex flex-wrap gap-2 md:gap-4 items-end">
            @if($isSuperAdmin)
                <div class="w-full md:flex-1 md:min-w-[200px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Branch</label>
                    <select name="branch_id" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">🌍 All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                🏪 {{ $branch->name }}
                            </option>
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
                <a href="{{ route('reports.pending-payments') }}" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center justify-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6 mb-4 md:mb-6">
        <div class="bg-white rounded-xl md:rounded-2xl border border-slate-200 p-4 md:p-6 shadow-sm flex flex-col justify-center relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-16 h-16 md:w-24 md:h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <p class="text-[10px] md:text-sm font-bold text-slate-500 uppercase tracking-widest relative z-10">Pending Orders</p>
            <h3 class="text-xl md:text-3xl font-black text-slate-900 mt-1 md:mt-2 relative z-10">{{ number_format($totalPendingCount) }}</h3>
        </div>
        
        <div class="bg-white rounded-xl md:rounded-2xl border border-slate-200 p-4 md:p-6 shadow-sm flex flex-col justify-center relative overflow-hidden group col-span-2 md:col-span-3">
            <div class="absolute -right-4 -top-4 w-16 h-16 md:w-24 md:h-24 bg-red-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <p class="text-[10px] md:text-sm font-bold text-slate-500 uppercase tracking-widest relative z-10">Total Outstanding Balance</p>
            <h3 class="text-xl md:text-3xl font-black text-red-600 mt-1 md:mt-2 relative z-10">Rs.{{ number_format($totalPendingAmount, 2) }}</h3>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order Info</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Total Price</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Paid</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right text-red-600">Pending</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 w-12 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                <a href="{{ route('orders.show', $order->order_number) }}" class="font-black text-blue-600 hover:text-blue-800">{{ $order->order_number }}</a>
                                <div class="text-[9px] md:text-xs text-slate-500 font-semibold mt-0.5">{{ $order->created_at->format('M d, Y h:i A') }}</div>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                <div class="font-black text-slate-900">{{ $order->customer->name ?? 'Walk-in' }}</div>
                                @if($order->customer && $order->customer->phone)
                                    <div class="text-[9px] md:text-xs text-slate-500 font-medium mt-0.5">{{ $order->customer->phone }}</div>
                                @endif
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-slate-700">
                                Rs.{{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-bold text-emerald-600">
                                Rs.{{ number_format($order->paid_amount, 2) }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-red-600 text-sm md:text-base">
                                Rs.{{ number_format($order->total - $order->paid_amount, 2) }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center">
                                <a href="{{ route('pos.index', ['order' => $order->id]) }}" class="inline-flex items-center justify-center px-3 py-1.5 md:px-4 md:py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 rounded-lg font-bold transition-colors shadow-sm text-[10px] md:text-xs">
                                    Pay
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="w-8 h-8 md:w-12 md:h-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>No pending payments found in this date range.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($orders->hasPages())
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
    @endif
</x-app-layout>
