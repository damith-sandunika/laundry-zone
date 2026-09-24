                {{-- â”€â”€ RIDER â”€â”€ --}}
                @if($role === 'rider')
                    <a href="{{ route('rider.tasks') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('rider.tasks') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        My Tasks
                    </a>
                @endif
