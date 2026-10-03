                <!-- Bulk Attendance Editor -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="attendanceGrid()">
                    <div class="p-5 border-b border-gray-100 flex flex-wrap justify-between items-center bg-gray-50/50 gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800">Month Attendance & OT</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form action="{{ route('staff.show', $staff->id) }}" method="GET" class="flex gap-2">
                                <select name="month" class="text-xs border-gray-200 rounded-lg py-1.5 px-3 appearance-none" style="background-image: none; padding-right: 12px; text-align: center;">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $payroll->month == $m ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 10)) }}</option>
                                    @endfor
                                </select>
                                <select name="year" class="text-xs border-gray-200 rounded-lg py-1.5 px-3 appearance-none" style="background-image: none; padding-right: 12px; text-align: center;">
                                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}" {{ $payroll->year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <button type="submit" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg transition">Load</button>
                            </form>
                            
                            <form action="{{ route('hr.attendance.bulkUpdate', $staff->id) }}" method="POST" @submit="prepareSubmit">
                                @csrf
                                <input type="hidden" name="attendance_data" x-model="submitData">
                                <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-xs font-bold rounded-lg shadow-sm hover:bg-blue-700 transition">Save Month</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-gray-100 text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 font-bold w-24">Date</th>
                                    <th class="px-4 py-3 font-bold w-32">Rule</th>
                                    <th class="px-4 py-3 font-bold w-32">Status</th>
                                    <th class="px-4 py-3 font-bold">In</th>
                                    <th class="px-4 py-3 font-bold">Out</th>
                                    <th class="px-4 py-3 font-bold text-center">Work</th>
                                    <th class="px-4 py-3 font-bold text-center">OT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(day, dateStr) in days" :key="dateStr">
                                    <tr class="transition" :class="{
                                        'bg-red-50/90 text-red-900 border-l-4 border-red-500 font-medium': day.rule.type === 'holiday',
                                        'hover:bg-gray-50/50': day.rule.type !== 'holiday'
                                    }">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-800" x-text="formatDate(dateStr)"></div>
                                            <div class="text-[9px] text-gray-400 font-bold uppercase tracking-wider" x-text="formatDay(dateStr)"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <template x-if="day.rule.type === 'holiday'">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white uppercase tracking-wide inline-flex items-center gap-1 shadow-sm">
                                                    🔴 Branch Closed
                                                </span>
                                            </template>
                                            <template x-if="day.rule.type !== 'holiday'">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                                      :class="{
                                                          'bg-green-100 text-green-700': day.rule.type === 'normal',
                                                          'bg-blue-100 text-blue-700': day.rule.type === 'special'
                                                      }" x-text="day.rule.type"></span>
                                            </template>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select x-model="day.status" @change="recalculate(dateStr)" 
                                                :disabled="day.rule.type === 'holiday'"
                                                class="w-full text-xs border-gray-200 rounded py-1 px-2 font-bold disabled:bg-red-100 disabled:text-red-700" 
                                                :class="{
                                                    'text-gray-400': !day.status, 
                                                    'text-green-700': day.status === 'present',
                                                    'text-emerald-700': day.status === 'paid_leave',
                                                    'text-orange-600': day.status === 'leave',
                                                    'text-red-700 font-black': day.rule.type === 'holiday'
                                                }">
                                                <option value="" x-show="day.rule.type !== 'holiday'">-</option>
                                                <option value="holiday" x-show="day.rule.type === 'holiday'">🔴 Branch Holiday</option>
                                                <option value="present" x-show="day.rule.type !== 'holiday'">🟢 Present</option>
                                                <option value="paid_leave" x-show="day.rule.type !== 'holiday'">🟢 Leave (Paid / With Salary)</option>
                                                <option value="leave" x-show="day.rule.type !== 'holiday'">🟠 Leave (Unpaid)</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" x-model="day.clock_in" @change="recalculate(dateStr)" 
                                                class="w-24 text-xs border-gray-200 rounded py-1 px-2 disabled:bg-gray-100 disabled:text-gray-400" 
                                                :disabled="day.status !== 'present' || day.rule.type === 'holiday'">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="time" x-model="day.clock_out" @change="recalculate(dateStr)" 
                                                class="w-24 text-xs border-gray-200 rounded py-1 px-2 disabled:bg-gray-100 disabled:text-gray-400" 
                                                :disabled="day.status !== 'present' || day.rule.type === 'holiday'">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-black text-gray-700" x-text="formatHours(day.normal_minutes)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-black text-indigo-600" x-text="formatHours(day.ot_minutes)"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
