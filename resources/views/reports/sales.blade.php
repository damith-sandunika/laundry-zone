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

    <!-- Data Table (Saman Hardware ERP DataTable) -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden mb-6 flex flex-col justify-between">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12 whitespace-nowrap">No</th>
                        @if($isSuperAdmin && !$branchId)
                            <th class="px-4 py-3.5 whitespace-nowrap">Branch</th>
                        @endif
                        <th class="px-4 py-3.5 whitespace-nowrap">Date</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Order ID</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Customer Name</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">QTY</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Payment Method</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($orders as $index => $activity)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 text-center font-mono text-slate-400">{{ $orders->firstItem() + $index }}</td>
                            @if($isSuperAdmin && !$branchId)
                                <td class="px-4 py-3.5 font-bold text-slate-900">{{ optional(optional($activity->order)->branch)->name ?? 'Unknown' }}</td>
                            @endif
                            <td class="px-4 py-3.5 font-mono text-slate-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3.5">
                                @if(optional($activity->order)->order_number)
                                <a href="{{ route('orders.show', optional($activity->order)->order_number) }}" class="font-bold text-blue-600 hover:text-blue-800 font-mono">
                                    #{{ optional($activity->order)->order_number }}
                                </a>
                                @else
                                <span class="text-slate-400 font-mono">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900">{{ optional(optional($activity->order)->customer)->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3.5 text-center font-bold text-blue-600 font-mono">{{ number_format($activity->total_qty ?? 0, 1) }}</td>
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $pm = $activity->payment_method ?? '';
                                    $labels = ['cash'=>'Cash','card'=>'Card','bank'=>'Bank Transfer','credit'=>'Credit'];
                                    $label = $labels[$pm] ?? strtoupper($pm) ?: 'None';
                                    $colors = [
                                        'cash'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'card'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'bank'   => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'credit' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    ];
                                    $color = $colors[$pm] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $color }}">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-black font-mono {{ $activity->amount < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                Rs. {{ number_format($activity->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isSuperAdmin && !$branchId) ? 8 : 7 }}" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">No sales transactions found</p>
                                <p class="text-xs text-slate-400 mt-1">Try expanding your date range filter.</p>
                            </td>
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
