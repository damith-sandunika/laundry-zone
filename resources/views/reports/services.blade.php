<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Service Sales Report</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Detailed breakdown of service performance</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reports.services.pdf', request()->all()) }}" target="_blank"
                   class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-slate-800/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    @include('reports.partials.filters', ['route_name' => 'reports.services'])

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center w-16">No</th>
                        @if($isSuperAdmin && !$branchId)
                            <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Branch</th>
                        @endif
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Service Name</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">QTY Sold</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Price (Revenue)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($services as $index => $serv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                            @if($isSuperAdmin && !$branchId)
                                <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $serv->branch_name ?? 'Unknown' }}</td>
                            @endif
                            <td class="px-2 py-2 md:px-6 md:py-4">
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-1.5 text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                        @if($serv->main_category_name)
                                            <span class="text-blue-500">{{ $serv->main_category_name }}</span>
                                            <svg class="w-2.5 h-2.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                            <span class="text-slate-500">{{ $serv->category_name }}</span>
                                        @else
                                            <span class="text-blue-500">{{ $serv->category_name ?? 'Uncategorized' }}</span>
                                        @endif
                                    </div>
                                    <div class="font-black text-slate-900 text-[11px] md:text-sm">
                                        {{ $serv->service_name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-black text-blue-600">{{ number_format($serv->total_qty, 1) }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-emerald-500">Rs.{{ number_format($serv->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($isSuperAdmin && !$branchId) ? 5 : 4 }}" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No services sold in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr>
                        <td colspan="{{ ($isSuperAdmin && !$branchId) ? 3 : 2 }}" class="px-2 py-3 md:px-6 md:py-5 text-right text-[10px] md:text-sm font-black text-slate-900 uppercase tracking-widest">Grand Total:</td>
                        <td class="px-2 py-3 md:px-6 md:py-5 text-center font-black text-blue-700 text-[11px] md:text-base">{{ number_format($totalQty, 1) }}</td>
                        <td class="px-2 py-3 md:px-6 md:py-5 text-right font-black text-emerald-700 text-[11px] md:text-base">Rs.{{ number_format($totalRevenue, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>


