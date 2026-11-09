{{-- ══════════ ACCOUNTANT MENU ══════════ --}}
@if($role === 'accountant' || $role === 'accountant_and_hr_manager')
    <a href="{{ route('accountant.dashboard') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('accountant.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        Account Dashboard
    </a>

    {{-- Customers --}}
    <div x-data="{ open: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
            <div class="flex items-center gap-3">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Customers
            </div>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
            <a href="{{ route('customers.index') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('customers.index') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">All Customers</a>
            <a href="{{ route('customers.top') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('customers.top') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Top Customers</a>
        </div>
    </div>

    {{-- Reports (All) --}}
    <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
            <div class="flex items-center gap-3">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Reports
            </div>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
            <a href="{{ route('reports.index') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.index') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">General Report</a>
            <a href="{{ route('reports.sales') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.sales') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Sales Report</a>
            <a href="{{ route('reports.services') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.services') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Services Report</a>
            <a href="{{ route('reports.pending-orders') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.pending-orders') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pending Order Report</a>
            <a href="{{ route('reports.pending-payments') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.pending-payments') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pending Payment Report</a>
            <a href="{{ route('reports.credit-orders') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.credit-orders') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Credit Order Report</a>
            <a href="{{ route('reports.customers') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.customers') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Customer Report</a>
        </div>
    </div>

    {{-- Petty Cash --}}
    <a href="{{ route('expenses.index') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Petty Cash
    </a>

    {{-- Expense Categories --}}
    <a href="{{ route('settings.expense_categories') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.expense_categories') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        Expense Categories
    </a>

    {{-- Order Corrections --}}
    <a href="{{ route('superadmin.order-logs') }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('superadmin.order-logs') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Order Corrections
    </a>

    @if($role === 'accountant_and_hr_manager')
        {{-- HR Management Section for Dual Role --}}
        <div class="pt-3 mt-3 border-t border-slate-100">
            <span class="block px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-400">HR Operations</span>
            <a href="{{ route('hr.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('hr.dashboard*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                HR Dashboard
            </a>

            <a href="{{ route('staff.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('staff.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                All Staff
            </a>

            <a href="{{ route('branch.month_plan') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('branch.month_plan*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Branch Monthly Plan
            </a>
        </div>
    @endif
@endif

