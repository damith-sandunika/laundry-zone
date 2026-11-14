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
    @include('superadmin.partials.branch_activity_tab', [
        'branches' => $branches,
        'logs' => $logs
    ])

    {{-- ================= TAB 2: RE-OPEN REQUESTS ================= --}}
    @include('superadmin.partials.reopen_requests_tab', [
        'branches' => $branches,
        'reopenRequests' => $reopenRequests
    ])

</div>
</x-app-layout>
