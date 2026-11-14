    <div x-data="{ open: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }">
        <button @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
            <div class="flex items-center gap-3">
                <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Customers
            </div>
            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
            <a href="{{ route('customers.index') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('customers.index') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">All
                Customers</a>
            <a href="{{ route('customers.top') }}"
                class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('customers.top') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Top
                Customers</a>
        </div>
    </div>
