{{-- Active Staff Attendance & Assignment --}}
        <div class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden mb-6 animate-slide-up" style="animation-delay: 400ms;">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Staff Attendance & System Assignment</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Manage today's shift access and attendance</p>
                    </div>
                </div>
                <button type="submit" form="update-attendance-form" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Update Records
                </button>
            </div>
            <div class="p-0 overflow-x-auto">
                <form id="update-attendance-form" action="{{ route('shifts.attendance.update') }}" method="POST">
                    @csrf
                    <!-- Desktop Header -->
                    <div class="hidden md:flex items-center justify-between px-6 py-3 bg-slate-50 border-b border-slate-100">
                        <div class="w-1/4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Staff Member</div>
                        <div class="flex-1 flex justify-end items-center gap-12 pr-4">
                            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest w-24 text-center">System Access</div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100/60">
                        @foreach($branchStaff as $staff)
                            @php
                                $att = $activeAttendances->get($staff->id);
                                $status = $att ? $att->status : 'hold';
                                $isAssigned = $att ? $att->is_assigned : false;
                                $clockIn = $att ? $att->clock_in : null;
                                $clockOut = $att ? $att->clock_out : null;
                            @endphp
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 md:px-6 hover:bg-slate-50/50 transition-colors gap-4">
                                
                                <!-- Staff Info -->
                                <div class="flex items-center gap-3 md:w-1/4">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm uppercase shrink-0">
                                        {{ substr($staff->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="text-[14px] font-bold text-slate-800 block leading-tight">{{ $staff->name }}</span>
                                        <span class="text-[11px] font-semibold text-slate-400">ID: #{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <input type="hidden" name="assigned_staff[{{ $att ? $att->id : 'new_'.$staff->id }}][staff_id]" value="{{ $staff->id }}">
                                </div>

                                <!-- Controls Grid -->
                                <div class="grid grid-cols-1 md:flex md:flex-row md:items-center gap-4 md:gap-12 flex-1 md:justify-end">
                                    <!-- System Access -->
                                    <div class="flex flex-col gap-1.5 items-start md:items-center md:w-24">
                                        <label class="text-[10px] font-extrabold text-slate-400 uppercase md:hidden">System Access</label>
                                        <div class="flex items-center h-[34px]">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="assigned_staff[{{ $att ? $att->id : 'new_'.$staff->id }}][is_assigned]" value="1" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 transition-all system-access-box shadow-sm" {{ $isAssigned ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
