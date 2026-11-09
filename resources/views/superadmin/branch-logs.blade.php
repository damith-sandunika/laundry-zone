<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="ph-title">Branch Activity Logs & Shift Re-Opens</h2>
                <p class="ph-sub">Comprehensive audit trail and manager daily shift re-open authorizations across branches</p>
            </div>
        </div>
    </x-slot>

<div class="space-y-6" x-data="{ currentTab: '{{ $tab }}' }">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 shadow-sm animate-fade-in text-xs font-bold">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 shadow-sm animate-fade-in text-xs font-bold">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Navigation Tabs --}}
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2 overflow-x-auto">
        <button type="button"
            @click="currentTab = 'logs'"
            :class="currentTab === 'logs' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
            class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Activity & Shift Logs
        </button>

        <button type="button"
            @click="currentTab = 'reopen_requests'"
            :class="currentTab === 'reopen_requests' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-600' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80'"
            class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-2 whitespace-nowrap relative active:scale-95">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Re-Open Requests
            @if($pendingReopenCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">
                    {{ $pendingReopenCount }}
                </span>
            @endif
        </button>
    </div>

    {{-- ================= TAB 1: ACTIVITY LOGS ================= --}}
    <div x-show="currentTab === 'logs'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        {{-- Filters (Saman Hardware ERP Style) --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4 sm:p-5 mb-5">
            <form action="{{ route('superadmin.branch-logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="tab" value="logs">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="flex items-center gap-2 sm:col-span-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter Logs
                    </button>
                    <a href="{{ route('superadmin.branch-logs', ['tab' => 'logs']) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors h-[38px] flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Logs Table (Saman Hardware DataTable Format) --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap">Time</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Branch</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">User</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Action</th>
                            <th class="px-5 py-3.5">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $log->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-slate-700 font-bold">
                                    {{ $log->branch->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->user->name ?? 'System' }}</div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">{{ $log->user->role ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php
                                        $actionColors = [
                                            'shift_opened'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'shift_closed'     => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'reopen_requested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'reopen_approved'  => 'bg-teal-50 text-teal-700 border-teal-200',
                                            'reopen_rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'staff_login'      => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'bank_deposit'     => 'bg-purple-50 text-purple-700 border-purple-200',
                                        ];
                                        $color = $actionColors[$log->action] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider border {{ $color }}">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 max-w-md">
                                    {{ $log->description }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">No logs found</p>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your date or branch filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ================= TAB 2: RE-OPEN REQUESTS ================= --}}
    <div x-show="currentTab === 'reopen_requests'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
        {{-- Filters --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-4 sm:p-5 mb-5">
            <form action="{{ route('superadmin.branch-logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <input type="hidden" name="tab" value="reopen_requests">
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Branch</label>
                    <select name="branch_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Status</label>
                    <select name="request_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('request_status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('request_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('request_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5 h-[38px]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter Requests
                    </button>
                    <a href="{{ route('superadmin.branch-logs', ['tab' => 'reopen_requests']) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors h-[38px] flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Requests Table --}}
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap">Requested At</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Branch</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Requested By</th>
                            <th class="px-5 py-3.5">Reason</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-center">Status</th>
                            <th class="px-5 py-3.5 whitespace-nowrap text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($reopenRequests as $req)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $req->created_at->format('M d, Y') }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $req->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $req->branch->name ?? 'Unknown Branch' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $req->requestedBy->name ?? 'User #' . $req->requested_by }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $req->requestedBy->email ?? '' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-slate-700 max-w-sm">
                                    <div class="bg-slate-50 rounded-lg p-2.5 border border-slate-200/60 text-xs">
                                        {{ $req->reason }}
                                    </div>
                                    @if($req->admin_notes)
                                        <div class="mt-1 text-[11px] text-slate-500">
                                            <strong class="text-slate-700">Admin Note:</strong> {{ $req->admin_notes }}
                                            @if($req->reviewedBy)
                                                <span class="text-slate-400 font-mono">({{ $req->reviewedBy->name }} {{ $req->reviewed_at ? $req->reviewed_at->format('h:i A') : '' }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-center">
                                    @if($req->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            PENDING
                                        </span>
                                    @elseif($req->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            APPROVED
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            REJECTED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right">
                                    @if($req->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5" x-data="{ showRejectModal: false }">
                                            {{-- Approve Form --}}
                                            <form action="{{ route('superadmin.reopen-requests.approve', $req->id) }}" method="POST" onsubmit="return confirm('Approve shift re-opening for {{ $req->branch->name ?? 'this branch' }}?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- Reject Trigger --}}
                                            <button type="button" @click="showRejectModal = true" class="px-3 py-1.5 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 rounded-lg text-xs font-bold transition-all flex items-center gap-1">
                                                Reject
                                            </button>

                                            {{-- Reject Modal --}}
                                            <template x-teleport="body">
                                                <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-cloak>
                                                    <div @click.away="showRejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 text-left shadow-2xl border border-slate-100">
                                                        <h3 class="text-base font-black text-slate-900 mb-2">Decline Re-Open Request</h3>
                                                        <p class="text-xs text-slate-500 mb-4">Explain why this request is being declined.</p>
                                                        <form action="{{ route('superadmin.reopen-requests.reject', $req->id) }}" method="POST">
                                                            @csrf
                                                            <div class="mb-4">
                                                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">Reason / Note</label>
                                                                <textarea name="admin_notes" rows="3" required placeholder="Provide reason..." class="w-full text-xs border border-slate-200 rounded-xl p-3 outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                                                            </div>
                                                            <div class="flex justify-end gap-2">
                                                                <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                                                                <button type="submit" class="px-4 py-2 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl">Confirm Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Reviewed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-3 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">No reopen requests found</p>
                                    <p class="text-xs text-slate-400 mt-1">All branch shifts are operating smoothly.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reopenRequests->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $reopenRequests->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
</x-app-layout>
