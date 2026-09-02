<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Branch Logs</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Monitor all activity across branches</p>
            </div>
        </div>
    </x-slot>

<div class="max-w-7xl mx-auto">
    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-4 mb-6">
        <form action="{{ route('superadmin.branch-logs') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Branch</label>
                <select name="branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
            </div>
            <div>
                <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-bold rounded-lg hover:bg-slate-800 transition-colors">Filter Logs</button>
            </div>
            <div>
                <a href="{{ route('superadmin.branch-logs') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-bold rounded-lg hover:bg-slate-50 transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Time</th>
                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Branch</th>
                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">User</th>
                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Action</th>
                        <th class="px-6 py-4 text-[11px] font-extrabold text-slate-400 uppercase tracking-widest">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-[13px] font-bold text-slate-800">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-0.5">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-[13px] font-semibold text-slate-700">
                                {{ $log->branch->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-[13px] font-bold text-slate-800">{{ $log->user->name ?? 'System' }}</div>
                                <div class="text-[11px] font-semibold text-slate-500 mt-0.5">{{ $log->user->role ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $actionColors = [
                                        'shift_opened' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
                                        'shift_closed' => 'bg-red-50 text-red-700 border border-red-200/60',
                                        'staff_login' => 'bg-blue-50 text-blue-700 border border-blue-200/60',
                                        'bank_deposit' => 'bg-violet-50 text-violet-700 border border-violet-200/60',
                                    ];
                                    $color = $actionColors[$log->action] ?? 'bg-slate-50 text-slate-700 border border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-widest {{ $color }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-[13px] font-medium text-slate-600 max-w-md">
                                {{ $log->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-[14px] font-bold text-slate-600 mb-1">No logs found</p>
                                <p class="text-[12px] font-medium text-slate-400">Try adjusting your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
</x-app-layout>

