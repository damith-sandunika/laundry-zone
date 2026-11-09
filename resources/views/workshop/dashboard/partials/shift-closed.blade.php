        {{-- ── Shift Closed Card ── --}}
        <div class="flex justify-center py-10 px-4 animate-slide-up">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-200 p-8 md:p-10 w-full max-w-4xl shadow-xl shadow-slate-200/50">
                
                @if(!empty($requiresApprovalToOpen))
                    {{-- Branch shift was closed today and strictly requires Super Admin approval to re-open --}}
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4 ring-8 ring-amber-50/50">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Today's Shift is Closed</h3>
                        <p class="text-[14px] font-medium text-slate-500 max-w-lg mx-auto">
                            The workshop shift was already closed for today. Under company policy, workshops can only open once per day. Re-opening on the same day requires <span class="font-bold text-slate-700">Super Admin Approval</span>.
                        </p>

                        {{-- Next Daily Shift Open Countdown Timer --}}
                        <div class="mt-6 inline-flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-5 shadow-lg shadow-slate-900/10 border border-slate-700/50"
                             x-data="countdownTimer()" x-init="start()">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 mb-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Next Standard Shift Opens In (Tomorrow at Midnight / 00:00)
                            </span>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-col items-center">
                                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="hours">00</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Hours</span>
                                </div>
                                <span class="text-2xl font-black text-slate-500 mb-4">:</span>
                                <div class="flex flex-col items-center">
                                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-white bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="minutes">00</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Mins</span>
                                </div>
                                <span class="text-2xl font-black text-slate-500 mb-4">:</span>
                                <div class="flex flex-col items-center">
                                    <span class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-amber-400 bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700" x-text="seconds">00</span>
                                    <span class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Secs</span>
                                </div>
                            </div>
                            <span class="text-[11px] font-medium text-slate-400 mt-2">
                                Or request Super Admin approval below to re-open immediately today.
                            </span>
                        </div>
                    </div>

                    <script>
                        function countdownTimer() {
                            return {
                                hours: '00',
                                minutes: '00',
                                seconds: '00',
                                start() {
                                    const update = () => {
                                        const now = new Date();
                                        const midnight = new Date();
                                        midnight.setHours(24, 0, 0, 0); // Next day 00:00:00
                                        const diff = Math.max(0, midnight - now);

                                        const h = Math.floor(diff / (1000 * 60 * 60));
                                        const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                        const s = Math.floor((diff % (1000 * 60)) / 1000);

                                        this.hours = String(h).padStart(2, '0');
                                        this.minutes = String(m).padStart(2, '0');
                                        this.seconds = String(s).padStart(2, '0');
                                    };
                                    update();
                                    setInterval(update, 1000);
                                }
                            }
                        }
                    </script>

                    @if(!empty($pendingReopenRequest))
                        {{-- Pending Request Notice --}}
                        <div class="bg-amber-50/80 border border-amber-200/80 rounded-2xl p-6 text-center max-w-lg mx-auto">
                            <div class="flex items-center justify-center gap-2 mb-2 text-amber-800 font-black text-base">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Approval Request Pending
                            </div>
                            <p class="text-[13px] font-medium text-amber-700 mb-3">
                                Your re-open request has been submitted to Super Admin on <strong>{{ $pendingReopenRequest->created_at->format('h:i A') }}</strong>. Please wait for Super Admin approval.
                            </p>
                            <div class="bg-white/80 rounded-xl p-3 text-left border border-amber-200 text-xs text-slate-600">
                                <strong class="text-slate-800">Submitted Reason:</strong> {{ $pendingReopenRequest->reason }}
                            </div>
                        </div>
                    @else
                        {{-- Request Form --}}
                        <form action="{{ route('shifts.reopen.request') }}" method="POST" class="max-w-xl mx-auto">
                            @csrf
                            <div class="bg-slate-50 rounded-2xl border border-slate-200 p-6 mb-6">
                                <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                    Reason for Re-opening Workshop Shift Today <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" rows="3" required placeholder="Explain why the workshop shift needs to be re-opened (e.g. Urgent evening processing, emergency machine operation)..." class="w-full text-sm bg-white border border-slate-200 rounded-xl p-3 text-slate-800 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"></textarea>
                                <p class="text-[11px] text-slate-400 mt-2 font-medium">
                                    This request will be immediately dispatched to Super Admin for authorization.
                                </p>
                            </div>

                            <div class="flex justify-center">
                                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-[14px] font-black tracking-wide rounded-xl shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    Send Request to Super Admin
                                </button>
                            </div>
                        </form>
                    @endif
                @else
                    {{-- Normal Shift Open Form (Either 1st shift of the day, OR approved by Super Admin, OR approval setting is OFF) --}}
                    @if(!empty($hasApprovedReopenRequest))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3">
                            <svg class="w-6 h-6 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <h4 class="text-sm font-black">Super Admin Approved Shift Re-Opening</h4>
                                <p class="text-xs text-emerald-700">Your re-open request has been approved. You can now start the shift with your starting cash.</p>
                            </div>
                        </div>
                    @endif

                    <div class="text-center mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4 ring-8 ring-blue-50/50">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Shift is Closed</h3>
                        <p class="text-[14px] font-medium text-slate-500 max-w-lg mx-auto">Open the branch shift register with your starting cash and assign staff for today's operations.</p>
                    </div>

                    <form action="{{ route('shifts.open') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Left Column: Cash Details --}}
                            <div class="bg-slate-50/50 rounded-2xl border border-slate-100 p-6 flex flex-col justify-center">
                                <div class="mb-5 text-center">
                                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-3">Opening Cash (Rs.)</label>
                                    <div class="relative max-w-xs mx-auto">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                            <span class="text-slate-400 font-bold sm:text-sm">Rs.</span>
                                        </div>
                                        <input type="number" step="0.01" name="opening_cash" value="{{ $suggestedOpeningCash ?? 0 }}" placeholder="e.g. 5000" required class="block w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-xl text-xl font-black text-slate-900 text-center shadow-sm focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300">
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-3 font-semibold flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Auto-filled from previous shift's closing balance.
                                    </p>
                                </div>
                            </div>

                            {{-- Right Column: Staff Assignment --}}
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                                    <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-widest">Assign Staff & Attendance</label>
                                    <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md">{{ isset($branchStaff) ? $branchStaff->count() : 0 }} Staff Available</span>
                                </div>

                                @if(isset($branchStaff) && $branchStaff->count() > 0)
                                    <div class="divide-y divide-slate-100 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                                        @foreach($branchStaff as $staff)
                                            <div class="py-3 border-b border-slate-100 last:border-0 flex items-center justify-between hover:bg-slate-50 transition-colors px-2 rounded-lg">
                                                <span class="text-[13px] font-bold text-slate-800">{{ $staff->name }}</span>
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="checkbox" name="assigned_staff[]" value="{{ $staff->id }}" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 transition-all">
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="py-8 text-center">
                                        <p class="text-[13px] font-semibold text-slate-400">No staff members found for this branch.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-center">
                            <button type="submit" class="w-full md:w-auto px-10 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[14px] font-black tracking-wide rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Confirm & Open Shift
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
