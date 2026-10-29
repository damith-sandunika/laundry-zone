                {{-- ── BRANCH ADMIN / ADMIN / STAFF ── --}}
                @if(in_array($role, ['branch_admin']) || ($role === 'staff' && $branchType === 'branch'))
                    <a href="{{ route('pos.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('pos.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        POS System
                    </a>
                    <a href="{{ route('orders.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ (request()->routeIs('orders.*') && !request()->routeIs('orders.history')) ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Orders
                    </a>

                    <a href="{{ route('orders.history') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('orders.history') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Order History
                    </a>



                    <a href="{{ route('expenses.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Petty Cash
                    </a>

                    @if($role === 'branch_admin')
                        <a href="{{ route('staff.index') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('staff.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Staff
                        </a>
                        
                        @if($canManageConfig)
                        <a href="{{ route('settings.services') }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.services') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Services & Pricing
                        </a>
                        @endif

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

                        <div x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Reports
                                </div>
                                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
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



                        <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Settings
                                </div>
                                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" style="display: none;" class="pl-9 mt-1 space-y-1">
                                <a href="{{ route('settings.general') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.general') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">General Settings</a>
                                @if($canManageConfig)
                                    <a href="{{ route('settings.extras') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.extras') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Extras</a>
                                    <a href="{{ route('settings.turnarounds') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.turnarounds') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Turnarounds</a>
                                    @if(config('app.delivery_enabled'))
                                    <a href="{{ route('settings.delivery') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.delivery') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Delivery</a>
                                    <a href="{{ route('settings.pickup-times') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.pickup-times') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Pickup Times</a>
                                    @endif
                                    <a href="{{ route('settings.expense_categories') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.expense_categories') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Expense Categories</a>
                                @endif
                                @if($role !== 'super_admin')
                                    <a href="{{ route('settings.cubbies') }}" class="block px-3 py-2 text-xs font-semibold rounded-lg {{ request()->routeIs('settings.cubbies') ? 'text-blue-700 bg-blue-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Stock Locations</a>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
