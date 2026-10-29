<!-- ══════════ METRICS CARDS (LIGHT THEME) ══════════ -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <!-- 1. Total Companies -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Companies</span>
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">{{ $totalTenants }}</div>
        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span>{{ $activeTenants }} Active</span>
            @if($suspendedTenants > 0)
                <span class="text-rose-500 font-semibold">({{ $suspendedTenants }} Suspended)</span>
            @endif
        </div>
    </div>

    <!-- 2. MRR (Monthly Recurring Revenue) -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total MRR</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">Rs. {{ number_format($totalMrr, 0) }}</div>
        <div class="text-[11px] text-emerald-600 font-semibold mt-1">Monthly Subscription</div>
    </div>

    <!-- 3. Active Tenants -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Tenants</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">{{ $activeTenants }} <span class="text-xs font-semibold text-slate-400">/ {{ $totalTenants }}</span></div>
        <div class="text-[11px] text-emerald-600 font-semibold mt-1">100% Operational Uptime</div>
    </div>

    <!-- 4. Tenant Databases Online -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Databases</span>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">{{ $totalTenants }}</div>
        <div class="text-[11px] text-purple-600 font-semibold mt-1">Dedicated DB Per Tenant</div>
    </div>

    <!-- 5. Registered Owners -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Owners</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">{{ $totalOwners }}</div>
        <div class="text-[11px] text-slate-500 mt-1">Master Accounts</div>
    </div>

    <!-- 6. Total Branches -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Branches</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
            </div>
        </div>
        <div class="text-2xl font-black text-slate-900 mt-3">{{ $totalBranches ?? $totalTenants }}</div>
        <div class="text-[11px] text-slate-500 mt-1">Across All Tenants</div>
    </div>
</div>
