<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Sales Report</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Detailed breakdown of sales</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reports.sales.pdf', request()->all()) }}" target="_blank"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-slate-800/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
                <a href="{{ route('reports.sales.csv', request()->all()) }}"
                   class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    @include('reports.partials.filters', ['route_name' => 'reports.sales'])


    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-4 md:mb-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">💵 Cash</p>
            <p class="text-lg md:text-2xl font-black text-emerald-600">Rs.{{ number_format($totalCash, 2) }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">💳 Card</p>
            <p class="text-lg md:text-2xl font-black text-blue-600">Rs.{{ number_format($totalCard, 2) }}</p>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">🏦 Bank Transfer</p>
            <p class="text-lg md:text-2xl font-black text-purple-600">Rs.{{ number_format($totalBank, 2) }}</p>
        </div>
        <div class="bg-white border border-orange-200 rounded-2xl p-4 shadow-sm">
            <p class="text-[9px] md:text-xs font-bold text-orange-500 uppercase tracking-widest mb-1">⏱️ Credit Issued</p>
            <p class="text-lg md:text-2xl font-black text-orange-600">Rs.{{ number_format($totalCredit, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm col-span-2 md:col-span-1">
            <p class="text-[9px] md:text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">⚡ Total</p>
            <p class="text-lg md:text-2xl font-black text-white">Rs.{{ number_format($totalSalesAmount, 2) }}</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center w-16">No</th>
                        @if($isSuperAdmin && !$branchId)
                            <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Branch</th>
                        @endif
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order ID</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer Name</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">QTY</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Payment Method</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $index => $activity)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-bold text-slate-400">{{ $orders->firstItem() + $index }}</td>
                            @if($isSuperAdmin && !$branchId)
                                <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $activity->order->branch->name ?? 'Unknown' }}</td>
                            @endif
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">
                                <a href="{{ route('orders.show', $activity->order->order_number) }}" class="text-blue-600 hover:underline">
                                    {{ $activity->order->order_number }}
                                </a>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $activity->order->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-black text-blue-600">{{ number_format($activity->total_qty, 1) }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold uppercase">
                                @php
                                    $pm = $activity->payment_method ?? '';
                                    $labels = ['cash'=>'Cash','card'=>'Card','bank'=>'Bank Transfer','credit'=>'Credit'];
                                    $label = $labels[$pm] ?? strtoupper($pm) ?: 'None';
                                    $colors = [
                                        'cash'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'card'   => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'bank'   => 'bg-purple-100 text-purple-700 border-purple-200',
                                        'credit' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    ];
                                    $color = $colors[$pm] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-[10px] md:text-xs font-bold border {{ $color }}">{{ $label }}</span>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black {{ $activity->amount < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                Rs.{{ number_format($activity->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isSuperAdmin && !$branchId) ? 8 : 7 }}" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No sales found in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $orders->appends(request()->query())->links() }}
    </div>

</x-app-layout>
