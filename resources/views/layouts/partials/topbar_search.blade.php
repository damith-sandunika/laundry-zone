                    {{-- Order Quick Search Box --}}
                    @if(in_array(auth()->user()->role ?? '', ['branch_admin', 'staff', 'super_admin', 'workshop_admin', 'accountant', 'hr_manager']))
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

                                {{-- See More Button --}}
                                <div x-show="hasMore" class="p-2 border-t border-slate-100 bg-slate-50/80 text-center">
                                    <button type="button" @click.stop="loadMore()" :disabled="loadingMore"
                                        class="w-full py-1.5 px-3 text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:bg-blue-100/60 rounded-xl transition-all flex items-center justify-center gap-1.5 focus:outline-none">
                                        <span x-show="!loadingMore" class="flex items-center gap-1">
                                            <span>See More</span>
                                            <span class="text-[10px] text-slate-400 font-semibold" x-text="'(' + (total - results.length) + ' more)'"></span>
                                            <svg class="w-3.5 h-3.5 text-blue-600 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                        <span x-show="loadingMore" class="flex items-center gap-1.5 text-slate-500">
                                            <svg class="w-3.5 h-3.5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            <span>Loading...</span>
                                        </span>
                                    </button>
                                </div>

                                <div x-show="query.trim().length >= 1 && results.length === 0 && !loading" x-cloak
                                    class="px-4 py-5 text-center text-[12px] font-semibold text-slate-400">
                                    No orders found.
                                </div>
                            </div>
                        </div>
                    @endif
