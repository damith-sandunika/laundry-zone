<!-- Summary Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3">
    <!-- Card 1: Total Order Value -->
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between hover:border-slate-200 transition-all">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Order Value</span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">Orders Gross</span>
            </div>
            <div class="text-base font-black text-slate-800 mt-0.5 leading-tight">
                Rs.{{ number_format($stats['total_order_value'], 2) }}
            </div>
        </div>
        
        <div class="mt-2 pt-1.5 border-t border-slate-100 space-y-1">
            <div class="grid grid-cols-2 gap-1 text-[10px] pb-1 border-b border-dashed border-slate-100">
                <div class="flex justify-between items-center pr-1 border-r border-slate-100">
                    <span class="text-slate-500 font-semibold truncate">🚶 Walk-in:</span>
                    <span class="font-bold text-slate-700">Rs.{{ number_format($stats['walk_in'], 0) }}</span>
                </div>
                <div class="flex justify-between items-center pl-1">
                    <span class="text-slate-500 font-semibold truncate">🚚 Deliv:</span>
                    <span class="font-bold text-slate-700">Rs.{{ number_format($stats['delivery'], 0) }}</span>
                </div>
            </div>
            @if($isWorkshop)
                <div class="space-y-0.5 text-[10px]">
                    <div class="flex justify-between items-center">
                        <span class="text-indigo-600 font-bold truncate">⚙️ From Workshop:</span>
                        <span class="font-bold text-indigo-700">Rs.{{ number_format($stats['direct_workshop_gross'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-purple-600 font-bold truncate">🏢 From Collection Points:</span>
                        <span class="font-bold text-purple-700">Rs.{{ number_format($stats['collection_points_gross'] ?? 0, 2) }}</span>
                    </div>
                </div>
            @else
                <div class="space-y-0.5 text-[10px]">
                    <div class="flex justify-between items-center">
                        <span class="text-indigo-600 font-bold truncate">🏢 Branch Gross:</span>
                        <span class="font-bold text-indigo-700">Rs.{{ number_format($stats['total_order_value'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-600 font-bold truncate">✨ Branch Net Comm.:</span>
                        <span class="font-bold text-emerald-700">Rs.{{ number_format($stats['total_branch_commission'] ?? 0, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Card 2: Income & Sharing (Role Adaptive) -->
    <div class="bg-gradient-to-br from-indigo-50/40 via-white to-purple-50/30 p-3 rounded-xl shadow-sm border border-indigo-100 flex flex-col justify-between hover:border-indigo-200 transition-all">
        @if($isWorkshop)
            <!-- Workshop View -->
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Workshop Net & Income</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700">Net Share</span>
                </div>
                <div class="text-base font-black text-purple-700 mt-0.5 leading-tight">
                    Rs.{{ number_format($stats['total_workshop_net'] ?? ($stats['total_order_value'] - ($stats['total_branch_commission'] ?? 0)), 2) }}
                </div>
            </div>

            <div class="mt-2 pt-1.5 border-t border-indigo-100/60 space-y-1">
                <div class="flex justify-between items-center text-[10px]">
                    <span class="text-slate-600 font-semibold truncate">⚙️ Direct Workshop Income:</span>
                    <span class="font-bold text-slate-800">Rs.{{ number_format($stats['direct_workshop_gross'] ?? 0, 2) }}</span>
                </div>

                <!-- From Branch Workshop Income Breakdown -->
                <div class="pt-1 border-t border-dashed border-indigo-100/80 space-y-0.5">
                    <div class="flex justify-between items-center text-[9px] font-bold text-indigo-700">
                        <span>🏢 From Collection Branches (Net):</span>
                        <span>Rs.{{ number_format(($stats['total_workshop_net'] ?? 0) - ($stats['direct_workshop_gross'] ?? 0), 2) }}</span>
                    </div>
                    @if(isset($stats['collection_point_breakdown']) && count($stats['collection_point_breakdown']) > 0)
                        <div class="max-h-12 overflow-y-auto space-y-0.5 pr-0.5">
                            @foreach($stats['collection_point_breakdown'] as $cp)
                                <div class="flex justify-between items-center text-[9px] text-slate-500">
                                    <span class="truncate max-w-[120px]">{{ $cp->branch_name }}:</span>
                                    <span class="font-semibold text-purple-700">Rs.{{ number_format($cp->workshop_net, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-[9px] text-slate-400 italic">No external branch orders</div>
                    @endif
                </div>
            </div>
        @else
            <!-- Collection Point / Branch View -->
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Branch Income (Net)</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">Commission</span>
                </div>
                <div class="text-base font-black text-emerald-700 mt-0.5 leading-tight">
                    Rs.{{ number_format($stats['total_branch_commission'] ?? 0, 2) }}
                </div>
            </div>

            <div class="mt-2 pt-1.5 border-t border-indigo-100/60 space-y-1">
                <div class="flex justify-between items-center text-[10px]">
                    <span class="text-slate-600 font-semibold truncate">🏢 Branch Net Commission:</span>
                    <span class="font-bold text-emerald-700">Rs.{{ number_format($stats['total_branch_commission'] ?? 0, 2) }}</span>
                </div>

                <!-- Workshops to pay breakdown -->
                <div class="pt-1 border-t border-dashed border-indigo-100/80 space-y-0.5">
                    <div class="flex justify-between items-center text-[9px] font-bold text-purple-700">
                        <span>🏭 Workshop Shares Payable:</span>
                        <span>Rs.{{ number_format($stats['total_workshop_net'] ?? 0, 2) }}</span>
                    </div>
                    @if(isset($stats['workshop_breakdown']) && count($stats['workshop_breakdown']) > 0)
                        <div class="max-h-12 overflow-y-auto space-y-0.5 pr-0.5">
                            @foreach($stats['workshop_breakdown'] as $wb)
                                <div class="flex justify-between items-center text-[9px] text-slate-500">
                                    <span class="truncate max-w-[120px]">{{ $wb->workshop_name }}:</span>
                                    <span class="font-semibold text-purple-700">Rs.{{ number_format($wb->workshop_share, 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-[9px] text-slate-400 italic">No workshop dispatches recorded</div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Card 3: Pending / Due (Regular & Credit) -->
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between hover:border-slate-200 transition-all">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Pending / Due</span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-600">Outstanding</span>
            </div>
            <div class="text-base font-black text-amber-600 mt-0.5 leading-tight">
                Rs.{{ number_format($stats['pending_due'], 2) }}
            </div>
        </div>

        <div class="mt-2 pt-1.5 border-t border-slate-100 space-y-1">
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-slate-500 font-semibold">Regular Pending:</span>
                <span class="font-bold text-slate-700">Rs.{{ number_format($stats['regular_pending'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-slate-500 font-semibold">Credit Balance:</span>
                <span class="font-bold text-amber-700">Rs.{{ number_format($stats['credit_orders'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-[9px] text-slate-400 pt-0.5 border-t border-dashed border-slate-100">
                <span>Total Orders:</span>
                <span class="font-bold text-slate-600">{{ $stats['order_qty'] }} orders</span>
            </div>
        </div>
    </div>

    <!-- Card 4: Collected Payments -->
    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between hover:border-slate-200 transition-all">
        <div>
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Collected Payments</span>
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Orders Paid</span>
            </div>
            <div class="text-base font-black text-emerald-600 mt-0.5 leading-tight">
                Rs.{{ number_format($stats['from_these_orders'] ?? 0, 2) }}
            </div>
        </div>

        <div class="mt-2 pt-1.5 border-t border-slate-100 space-y-1">
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-indigo-600 font-bold truncate">⚙️ From Workshop Orders:</span>
                <span class="font-bold text-indigo-700">Rs.{{ number_format($stats['workshop_direct_collected'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-[10px]">
                <span class="text-purple-600 font-bold truncate">🏢 From Collection Points:</span>
                <span class="font-bold text-purple-700">Rs.{{ number_format($stats['collection_points_collected'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
</div>

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
