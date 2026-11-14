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

@include('orders.partials.history.collection_points')
