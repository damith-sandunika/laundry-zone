            <!-- Topbar -->
            <header
                class="py-2 lg:py-0 px-3 lg:px-8 bg-white/70 backdrop-blur-xl border-b border-slate-200/60 flex flex-col lg:flex-row lg:items-center justify-between sticky top-0 z-30 shadow-sm gap-y-3 lg:gap-y-0 lg:h-16">

                <!-- Top Row (Mobile & Desktop) -->
                <div class="flex items-center justify-between w-full lg:w-auto relative">
                    <!-- Mobile Toggle -->
                    <button @click="sidebarOpen=!sidebarOpen"
                        class="p-1.5 sm:p-2 -ml-1 sm:-ml-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden transition-colors flex-shrink-0 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Title (Centered on mobile, left on desktop) -->
                    <div
                        class="absolute left-1/2 -translate-x-1/2 lg:static lg:transform-none flex flex-col text-center lg:text-left pointer-events-none lg:pointer-events-auto">
                        @php 
                                                        $branchId = auth()->user()->branch_id ?? 1;
                            $branch = \App\Models\Branch::find($branchId); 
                        @endphp
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            @if(auth()->user()->role === 'rider')
                                <div
                                    class="text-[14px] lg:text-[15px] font-bold text-slate-800 tracking-tight leading-none">
                                    Rider Dashboard</div>
                            @else
                                <div
                                    class="text-[14px] lg:text-[15px] font-bold text-slate-800 tracking-tight leading-none">
                                    {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</div>
                            @endif


                        </div>
                        <div
                            class="text-[9px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] mt-1">
                            @if(auth()->user()->role !== 'rider' && $branch)
                                {{ $branch->name }}
                            @else
                                Laundry Management
                            @endif
                        </div>
                    </div>

                    <!-- Profile Dropdown (Mobile only in this row, hidden on desktop) -->
                    <div class="relative lg:hidden z-10" x-data="{ profileOpenMobile: false }">
                        <button @click="profileOpenMobile = !profileOpenMobile" @click.away="profileOpenMobile = false"
                            class="flex items-center p-1 rounded-full hover:bg-slate-100 transition-colors">
                            <div
                                class="w-[30px] h-[30px] rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-[12px] font-black text-white shadow-md">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </button>
                        <!-- Profile Menu -->
                        <div x-show="profileOpenMobile" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            class="absolute right-0 top-[42px] w-[180px] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-[60] origin-top-right"
                            x-cloak>
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-3 text-[12.5px] font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 transition-colors pointer-events-auto">
                                <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 pointer-events-auto">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2.5 w-full px-4 py-3 text-[12.5px] font-bold text-red-600 hover:bg-red-50/50 hover:text-red-700 transition-colors text-left">
                                    <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Side Actions (2nd Row on Mobile) -->
                <div
                    class="flex flex-row items-center w-full lg:w-auto justify-between lg:justify-end gap-1.5 sm:gap-3 lg:gap-6 ml-auto border-t border-slate-100/80 lg:border-none pt-2 lg:pt-0">
                    {{-- Order Quick Search Box --}}
                    @if(in_array(auth()->user()->role ?? '', ['branch_admin', 'staff', 'super_admin', 'workshop_admin']))
                        <div class="block relative" x-data="orderSearch()" @click.away="close()">
                            <div class="relative">
                                <svg class="absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 w-3.5 sm:w-4 h-3.5 sm:h-4 text-slate-400 pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8" />
                                    <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                                </svg>
                                <input type="text" x-model="query" @input.debounce.300ms="fetchResults()"
                                    @focus="if(results.length > 0) open = true" @keydown.escape="close()"
                                    placeholder="Search..."
                                    class="w-24 sm:w-52 pl-7 sm:pl-9 pr-2 sm:pr-4 py-1.5 sm:py-2 text-[11px] sm:text-[12.5px] font-semibold bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:bg-white focus:w-36 sm:focus:w-72 transition-all duration-300 placeholder-slate-400">
                                <div x-show="loading" x-cloak class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-slate-400 animate-spin" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Dropdown Results --}}
                            <div x-show="open && results.length > 0" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute right-0 top-[calc(100%+6px)] w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden">
                                <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/60">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Quick
                                        Results</span>
                                </div>
                                <div class="max-h-72 overflow-y-auto">
                                    <template x-for="order in results" :key="order.id">
                                        <a :href="order.url" @click="close()"
                                            class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-blue-50 border-b border-slate-50 last:border-0 transition-colors group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                        stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <div class="text-[13px] font-bold text-slate-800 group-hover:text-blue-700"
                                                        x-text="order.order_number"></div>
                                                    <div class="text-[11px] text-slate-400 font-medium truncate"
                                                        x-text="order.customer"></div>
                                                </div>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <div class="text-[12px] font-black text-slate-700"
                                                    x-text="'LKR ' + order.total"></div>
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" :class="{
                                                        'bg-green-100 text-green-700': order.status === 'done' || order.status === 'delivered',
                                                        'bg-blue-100 text-blue-700': order.status === 'received',
                                                        'bg-purple-100 text-purple-700': order.status === 'processing' || order.status === 'dispatched_to_workshop',
                                                        'bg-amber-100 text-amber-700': order.status === 'returned_from_workshop',
                                                        'bg-slate-100 text-slate-500': order.status === 'cancelled',
                                                    }" x-text="order.status_label">
                                                </span>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                                <div x-show="query.length >= 2 && results.length === 0 && !loading" x-cloak
                                    class="px-4 py-5 text-center text-[12px] font-semibold text-slate-400">
                                    No orders found.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Manual Refresh Button -->
                    <button id="manual-refresh-btn" onclick="window.location.reload()" title="Refresh Page"
                        x-data="{ spinning: false }" @click="spinning = true"
                        class="relative p-2 rounded-full hover:bg-slate-100 transition-colors focus:outline-none group">
                        <div
                            class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 flex items-center justify-center transition-colors">
                            <svg :class="{ 'animate-spin': spinning }" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                    </button>

                    <!-- Notifications Dropdown -->
                    @if(auth()->check())
                        <x-alert-system />
                    @endif

                    <!-- Profile Dropdown (Desktop) -->
                    <div class="relative hidden lg:block">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" @click.away="profileOpen = false"
                            class="flex items-center p-1 rounded-full hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="w-[34px] h-[34px] rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-[13px] font-black text-white shadow-md">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span
                                    class="hidden lg:block text-[13px] font-bold text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="hidden lg:block w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>

                        <!-- Profile Menu -->
                        <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-[52px] w-[180px] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-50 origin-top-right"
                            x-cloak>
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-3 text-[12.5px] font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 transition-colors">
                                <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2.5 w-full px-4 py-3 text-[12.5px] font-bold text-red-600 hover:bg-red-50/50 hover:text-red-700 transition-colors text-left">
                                    <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>