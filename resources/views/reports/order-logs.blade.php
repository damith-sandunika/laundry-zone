<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Order Corrections/Refunds</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Audit trail of order corrections and refund requests</p>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
        <form action="{{ route('superadmin.order-logs') }}" method="GET" class="flex flex-wrap gap-2 md:gap-4 items-end">
            <div class="w-full md:flex-1 md:min-w-[200px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Branch</label>
                <select name="branch_id" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                    <option value="">🌍 All Branches (Company Wide)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            🏪 {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:flex-1 md:min-w-[150px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Order Number</label>
                <input type="text" name="order_number" value="{{ request('order_number') }}" placeholder="Search Order..." class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>

            <div class="flex-1 md:min-w-[130px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Start</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>
            <div class="flex-1 md:min-w-[130px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">End</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>
            <div class="flex-none flex gap-2">
                <button type="button" onclick="applyQuickDate('{{ date('Y-m-d') }}', '{{ date('Y-m-d') }}')" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center">Today</button>
                <button type="button" onclick="applyQuickDate('{{ date('Y-m-d', strtotime('-1 day')) }}', '{{ date('Y-m-d', strtotime('-1 day')) }}')" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center">Yesterday</button>
            </div>
            
            <div class="flex-none">
                <button type="submit" class="px-3 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl flex items-center gap-1 md:gap-2 shadow-lg shadow-blue-600/25 transition-all h-[34px] md:h-[46px]">
                    <svg width="14" height="14" class="md:w-[18px] md:h-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span class="hidden md:inline">Apply Filter</span>
                    <span class="md:hidden">Apply</span>
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function applyQuickDate(start, end) {
            document.querySelector('input[name="start_date"]').value = start;
            document.querySelector('input[name="end_date"]').value = end;
            document.querySelector('form[action="{{ route('superadmin.order-logs') }}"]').submit();
        }
    </script>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center w-16">No</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date & Time</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order ID</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Branch</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Details (Reason)</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Status / Approver</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-bold text-slate-400">{{ $logs->firstItem() + $index }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">
                                @if($log->order)
                                    <a href="{{ route('orders.show', $log->order->order_number) }}" class="text-blue-600 hover:underline">
                                        {{ $log->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400 italic">Deleted</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">
                                {{ $log->order && $log->order->branch ? $log->order->branch->name : 'Unknown' }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-medium text-slate-700">
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 text-xs md:text-sm">
                                    <div class="font-bold text-slate-900 mb-1">
                                        @if($log->type === 'refund')
                                            <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 rounded text-[10px] uppercase font-bold mr-1">Refund</span>
                                        @elseif($log->type === 'items_change')
                                            <span class="inline-block px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-[10px] uppercase font-bold mr-1">Edit Order</span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 bg-slate-200 text-slate-700 rounded text-[10px] uppercase font-bold mr-1">{{ str_replace('_', ' ', $log->type) }}</span>
                                        @endif
                                        <span class="text-xs text-slate-500 font-medium">Requested by: {{ $log->requestedBy->name ?? 'Unknown' }}</span>
                                    </div>
                                    {!! nl2br(e($log->reason)) !!}
                                </div>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">
                                @if($log->status === 'pending')
                                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-700 rounded-md text-xs font-bold">⏳ Pending</span>
                                @elseif($log->status === 'approved')
                                    <span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs font-bold mb-1">✅ Approved</span>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-1">
                                        <div class="w-4 h-4 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">{{ substr($log->approvedBy->name ?? 'S', 0, 1) }}</div>
                                        {{ $log->approvedBy->name ?? 'System' }}
                                    </div>
                                @elseif($log->status === 'rejected')
                                    <span class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded-md text-xs font-bold mb-1">❌ Rejected</span>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-1">
                                        <div class="w-4 h-4 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">{{ substr($log->approvedBy->name ?? 'S', 0, 1) }}</div>
                                        {{ $log->approvedBy->name ?? 'System' }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No change logs found in this date range.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

</x-app-layout>
