        {{-- ── Shift Closed Card ── --}}
        <div class="flex justify-center py-10 px-4 animate-slide-up">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-200 p-8 md:p-10 w-full max-w-4xl shadow-xl shadow-slate-200/50">
                
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
            </div>
        </div>
