<div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
    <button @click="open = !open"
        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
        <div class="flex items-center gap-3">
            <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Reports
        </div>
        <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
        <a href="{{ route('reports.index') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.index') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">General Report</a>
        <a href="{{ route('reports.sales') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.sales') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Sales Report</a>
        <a href="{{ route('reports.services') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.services') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Services Report</a>
        <a href="{{ route('reports.pending-orders') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.pending-orders') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pending Order Report</a>
        <a href="{{ route('reports.pending-payments') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.pending-payments') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pending Payment Report</a>
        <a href="{{ route('reports.credit-orders') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.credit-orders') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Credit Order Report</a>
        <a href="{{ route('reports.customers') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('reports.customers') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Customer Report</a>
    </div>
</div>
