<!-- Secondary Bar: Quantities + Collection Points Detailed Breakdown -->
<div class="mb-4 space-y-2">
    <!-- Quick Counts Bar -->
    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200/80">
        <div class="flex items-center justify-between px-3 py-1 bg-white rounded-lg border border-slate-100">
            <span class="text-[11px] font-bold text-slate-500">📦 Total Orders:</span>
            <span class="text-sm font-black text-indigo-700">{{ $stats['order_qty'] }}</span>
        </div>
        <div class="flex items-center justify-between px-3 py-1 bg-white rounded-lg border border-slate-100">
            <span class="text-[11px] font-bold text-slate-500">👕 Total Qty:</span>
            <span class="text-sm font-black text-violet-700">{{ $stats['item_qty'] + 0 }}</span>
        </div>
        <div class="flex items-center justify-between px-3 py-1 bg-white rounded-lg border border-slate-100">
            <span class="text-[11px] font-bold text-slate-500">👥 Customers:</span>
            <span class="text-sm font-black text-blue-700">{{ $stats['total_customer_count'] }}</span>
        </div>
    </div>

    <!-- Collection Points Breakdown Accordion / Widget -->
    @if(isset($stats['collection_point_breakdown']) && count($stats['collection_point_breakdown']) > 0)
        <div x-data="{ open: true }" class="bg-white rounded-xl border border-indigo-100 shadow-sm overflow-hidden">
            <button type="button" @click="open = !open" 
                    class="w-full flex items-center justify-between px-4 py-2.5 bg-indigo-50/50 hover:bg-indigo-50/80 transition-colors text-left border-b border-indigo-100/60">
                <div class="flex items-center gap-2">
                    <span class="text-xs">🏢</span>
                    <span class="text-xs font-black text-indigo-900 uppercase tracking-wider">Collection Points & Commission Breakdown</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-200 text-indigo-800">
                        {{ count($stats['collection_point_breakdown']) }} {{ count($stats['collection_point_breakdown']) === 1 ? 'Point' : 'Points' }}
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-black text-indigo-700 hidden sm:inline">
                        Total Comm: Rs.{{ number_format($stats['total_branch_commission'] ?? 0, 2) }}
                    </span>
                    <svg class="w-4 h-4 text-indigo-600 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </button>

            <div x-show="open" class="p-3 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            <th class="py-1.5 px-2">Collection Point</th>
                            <th class="py-1.5 px-2 text-center">Comm. Rate</th>
                            <th class="py-1.5 px-2 text-center">Orders</th>
                            <th class="py-1.5 px-2 text-right">Gross Total</th>
                            <th class="py-1.5 px-2 text-right text-indigo-700">Branch Commission</th>
                            <th class="py-1.5 px-2 text-right text-purple-700">Workshop Net Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($stats['collection_point_breakdown'] as $cp)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-2 px-2 font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                                    {{ $cp->branch_name }}
                                </td>
                                <td class="py-2 px-2 text-center font-bold text-slate-600">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px]">{{ number_format($cp->commission_rate, 0) }}%</span>
                                </td>
                                <td class="py-2 px-2 text-center font-semibold text-slate-600">{{ $cp->order_count }}</td>
                                <td class="py-2 px-2 text-right font-bold text-slate-700">Rs.{{ number_format($cp->gross_total, 2) }}</td>
                                <td class="py-2 px-2 text-right font-black text-indigo-600">Rs.{{ number_format($cp->branch_commission, 2) }}</td>
                                <td class="py-2 px-2 text-right font-black text-purple-700">Rs.{{ number_format($cp->workshop_net, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
