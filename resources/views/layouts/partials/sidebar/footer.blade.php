            <!-- Footer -->
            <div class="sidebar-footer p-5 border-t border-slate-100/80 text-center bg-white/40"
                :class="sidebarCollapsed ? 'px-2' : ''">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        :class="sidebarCollapsed ? 'p-3 rounded-full justify-center text-transparent font-[0px] gap-0' : 'p-2.5 rounded-xl gap-2 text-xs'"
                        class="flex items-center justify-center w-full bg-red-50/50 border border-red-100 text-red-600 hover:text-red-700 hover:bg-red-100 hover:border-red-200 transition-all duration-200 font-bold tracking-wide shadow-sm overflow-hidden whitespace-nowrap p-2.5 rounded-xl gap-2 text-xs">
                        <svg :class="sidebarCollapsed ? 'w-5 h-5 text-red-600 m-0 flex-shrink-0' : 'w-4 h-4 text-red-600'" class="w-4 h-4 text-red-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="ml-1">Sign Out</span>
                    </button>
                </form>
                <div x-show="!sidebarCollapsed"
                    class="text-[10px] font-extrabold text-slate-400/80 tracking-widest mt-4 uppercase whitespace-nowrap">
                    Powered by DSRZone</div>
            </div>
