<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <p class="text-xs text-gray-400 mb-1"><a href="{{ route('staff.index') }}" class="hover:text-blue-600">← Staff Directory</a></p>
                <h2 class="text-xl font-bold text-gray-800">{{ $staff->name }}'s Profile</h2>
            </div>
            <div class="flex items-center gap-2">
                <button @click="$dispatch('open-allowance')" class="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-lg hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer">
                    + Allowance
                </button>
                <button @click="$dispatch('open-deduction')" class="px-3.5 py-2 bg-rose-600 text-white font-bold text-xs rounded-lg hover:bg-rose-700 transition-colors shadow-sm cursor-pointer">
                    - Deduction
                </button>
                <a href="{{ route('staff.edit', $staff) }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-200 transition-colors">
                    Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ 
        attendanceModalOpen: false, 
        leaveModalOpen: false, 
        advanceModalOpen: false,
        allowanceModalOpen: false,
        deductionModalOpen: false
    }" 
    @open-allowance.window="allowanceModalOpen = true"
    @open-deduction.window="deductionModalOpen = true"
    class="max-w-6xl mx-auto space-y-6">
        
        <!-- Top Profile Card -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white flex flex-col items-center justify-center md:w-1/3">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center text-4xl font-black mb-4 shadow-inner">
                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                </div>
                <h2 class="text-2xl font-black text-center leading-tight mb-1">{{ $staff->name }}</h2>
                <span class="px-3 py-1 bg-white/20 text-white text-xs font-bold rounded-full mb-3 shadow-sm border border-white/30">
                    {{ ucfirst($staff->role) }}
                </span>
                <p class="text-blue-100 text-sm font-medium text-center">
                    {{ $staff->branch ? $staff->branch->name : 'Global / Main Branch' }}
                </p>
                @if(!$staff->is_active)
                    <span class="mt-4 px-3 py-1 bg-red-500/20 text-red-100 text-xs font-bold rounded-full shadow-sm border border-red-500/30">
                        🚫 Suspended Account
                    </span>
                @endif
            </div>

            <div class="p-8 md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-8 bg-gray-50/30">
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Email Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->email }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Phone Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->phone ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">NIC Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->nic ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Vehicle Number</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->vehicle_number ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Date of Birth</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile?->dob ? \Carbon\Carbon::parse($staff->profile->dob)->format('M d, Y') : 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Residential Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->profile->address ?? 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-red-400 mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Emergency Contact
                        </p>
                        <p class="text-sm font-bold text-gray-800">{{ $staff->profile->emergency_contact ?? 'Not Provided' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-blue-500 mb-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Monthly Free Leaves
                        </p>
                        @php
                            $monthlyAllowed = $payroll->monthly_allowed_leaves ?? ($staff->profile->casual_leaves ?? 2);
                            $unpaidTaken = $payroll->unpaid_leave_days ?? 0;
                            $freeRemaining = max(0, $monthlyAllowed - $unpaidTaken);
                            $excessTaken = $payroll->excess_leaves ?? 0;
                        @endphp
                        
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-xl font-black {{ $freeRemaining == 0 ? 'text-amber-600' : 'text-gray-800' }}">
                                {{ $freeRemaining }} / {{ $monthlyAllowed }}
                            </span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Days Free</span>
                        </div>

                        @if($excessTaken > 0)
                            <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                                ⚠️ {{ $excessTaken }} Excess Day{{ $excessTaken > 1 ? 's' : '' }} (-Rs. {{ number_format($payroll->excess_leave_deduction, 2) }})
                            </div>
                        @elseif($unpaidTaken > 0)
                            <div class="mt-1 text-[10px] font-bold text-emerald-600">
                                ✓ {{ $unpaidTaken }} Day{{ $unpaidTaken > 1 ? 's' : '' }} used within free limit
                            </div>
                        @else
                            <div class="mt-1 text-[10px] font-semibold text-gray-400">
                                Full quota available
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Basic Monthly Salary</p>
                        <p class="text-lg font-black text-gray-800">Rs. {{ number_format($staff->profile->basic_salary ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-green-500 mb-1">Net Salary ({{ date('M') }})</p>
                        <p class="text-lg font-black text-green-600">Rs. {{ number_format($payroll->net_salary, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Attendance & Leaves -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Bulk Attendance Editor -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="attendanceGrid()">
                    <div class="p-5 border-b border-gray-100 flex flex-wrap justify-between items-center bg-gray-50/50 gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800">Month Attendance & OT</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <form action="{{ route('staff.show', $staff->id) }}" method="GET" class="flex gap-2">
                                <select name="month" class="text-xs border-gray-200 rounded-lg py-1.5 px-3">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $payroll->month == $m ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 10)) }}</option>
                                    @endfor
                                </select>
                                <select name="year" class="text-xs border-gray-200 rounded-lg py-1.5 px-3">
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

                <!-- Leaves -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Leave History</h3>
                        <button @click="leaveModalOpen = true" class="text-xs font-bold px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg border border-orange-100 hover:bg-orange-100 transition">
                            Request Leave
                        </button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->leaves()->latest('start_date')->get() as $leave)
                            <div class="p-4 hover:bg-gray-50/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $leave->type === 'sick' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ ucfirst($leave->type) }} Leave
                                        </span>
                                        <p class="text-xs font-bold text-gray-800 mt-1">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $leave->status === 'approved' ? 'bg-green-100 text-green-700' : ($leave->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ $leave->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500 mb-2">{{ $leave->reason }}</p>
                                @if($leave->status === 'pending' && in_array(auth()->user()->role, ['super_admin', 'branch_admin', 'admin']))
                                    <div class="flex gap-2 mt-2 pt-2 border-t border-gray-100">
                                        <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="text-[10px] font-bold text-green-600 hover:text-green-700">✅ Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-[10px] font-bold text-red-600 hover:text-red-700">❌ Reject</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-sm font-semibold text-gray-400">No leaves requested yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Salary Advances & Payroll -->
            <div class="space-y-6">
                <!-- Salary Advances -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Salary Advances</h3>
                        <button @click="advanceModalOpen = true" class="text-[10px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 hover:bg-indigo-100">Add New</button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->salaryAdvances()->latest('date')->get() as $adv)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">{{ \Carbon\Carbon::parse($adv->date)->format('M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[120px]">{{ $adv->reason ?? 'No reason provided' }}</p>
                                </div>
                                <span class="text-sm font-black text-indigo-600">Rs. {{ number_format($adv->amount, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-xs font-semibold text-gray-400">No active salary advances.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Current Month Payroll -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-100 shadow-sm overflow-hidden p-5 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Payroll ({{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }})</h3>
                    <div class="space-y-2 mb-4 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-medium">Basic Salary:</span>
                            <span class="font-bold text-gray-800">Rs. {{ number_format($payroll->basic_salary, 2) }}</span>
                        </div>
                        @if($payroll->excess_leave_deduction > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Excess Leaves ({{ $payroll->excess_leaves }} d × Rs. {{ number_format($payroll->daily_rate, 2) }}):</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->excess_leave_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payroll->short_hours_deduction > 0)
                        <div class="flex justify-between text-amber-600">
                            <span>Short Hours ({{ $payroll->short_hours }} h × Rs. {{ number_format($payroll->hourly_rate, 2) }}):</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->short_hours_deduction, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-indigo-600">
                            <span>OT ({{ $payroll->ot_hours }} h):</span>
                            <span class="font-bold">+ Rs. {{ number_format($payroll->ot_amount, 2) }}</span>
                        </div>
                        @if($payroll->allowances > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Allowances:</span>
                            <span class="font-bold">+ Rs. {{ number_format($payroll->allowances, 2) }}</span>
                        </div>
                        @endif
                        @if($payroll->manual_deductions > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Manual Deductions:</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->manual_deductions, 2) }}</span>
                        </div>
                        @endif
                        
                        <!-- Full Total Salary Row -->
                        <div class="pt-2 pb-1 my-1 border-t border-b border-blue-100 bg-blue-50/60 px-2 rounded-lg flex justify-between items-center font-black">
                            <span class="text-slate-800">Full Total Salary:</span>
                            <span class="text-blue-700 text-sm">Rs. {{ number_format($payroll->gross_salary, 2) }}</span>
                        </div>

                        @if($payroll->advances > 0)
                        <div class="flex justify-between text-purple-600 px-1 pt-1">
                            <span class="font-semibold">Salary Advances:</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->advances, 2) }}</span>
                        </div>
                        @endif
                        
                        <!-- Payable Balance Row -->
                        <div class="pt-2 border-t border-green-200 flex justify-between items-center text-sm">
                            <span class="font-black text-slate-800">Payable Balance:</span>
                            <span class="font-black text-green-700 text-lg">Rs. {{ number_format($payroll->net_salary, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('hr.payslip.download', ['staff' => $staff->id, 'month' => $payroll->month, 'year' => $payroll->year]) }}" 
                           target="_blank"
                           class="flex-1 text-center py-2 px-3 bg-white text-gray-700 text-xs font-bold rounded-lg border border-gray-200 hover:bg-gray-50 transition shadow-sm">
                           📄 PDF Payslip
                        </a>
                        <form method="POST" action="{{ route('staff.paySalary') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $staff->id }}">
                            <input type="hidden" name="month" value="{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}">
                            <input type="hidden" name="amount" value="{{ $payroll->net_salary }}">
                            <button type="submit" class="w-full text-center py-2 px-3 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition shadow-sm shadow-green-600/30">
                                💸 Pay Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Allowances & Deductions History -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-xs">Allowances & Deductions</h3>
                        <div class="flex gap-1">
                            <button @click="allowanceModalOpen = true" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded hover:bg-emerald-100">+ Allowance</button>
                            <button @click="deductionModalOpen = true" class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded hover:bg-rose-100">- Deduction</button>
                        </div>
                    </div>
                    <div class="p-0 divide-y divide-gray-50 text-xs">
                        @forelse($staff->salaryAdjustments()->whereMonth('date', $payroll->month)->whereYear('date', $payroll->year)->latest('date')->get() as $adj)
                            <div class="p-3 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase {{ $adj->type === 'allowance' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $adj->type }}
                                    </span>
                                    <p class="text-[11px] font-bold text-gray-700 mt-1">{{ \Carbon\Carbon::parse($adj->date)->format('M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[140px]">{{ $adj->reason ?? 'No reason' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold {{ $adj->type === 'allowance' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $adj->type === 'allowance' ? '+' : '-' }} Rs. {{ number_format($adj->amount, 2) }}
                                    </span>
                                    <form method="POST" action="{{ route('hr.adjustments.destroy', $adj->id) }}" onsubmit="return confirm('Remove this adjustment?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 font-bold text-xs">&times;</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center">
                                <p class="text-xs text-gray-400 font-medium">No adjustments for this month.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <!-- Recent Payslips -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Recent Basic Salaries Paid</h3>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($salaries as $salary)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">{{ $salary->description }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $salary->created_at->format('M d, Y') }}</p>
                                </div>
                                <span class="text-sm font-black text-green-600">Rs. {{ number_format($salary->amount, 2) }}</span>
                            </div>
                        @empty
                            <div class="p-6 text-center">
                                <p class="text-xs font-semibold text-gray-400">No salary records found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- MODALS -->
        <!-- 1. Attendance Modal -->
        <div x-show="attendanceModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="attendanceModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Mark Attendance</h3>
                    <form action="{{ route('hr.attendance.store', $staff->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date</label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Clock In Time</label>
                                    <input type="time" name="clock_in" class="w-full text-sm border-gray-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Clock Out Time</label>
                                    <input type="time" name="clock_out" class="w-full text-sm border-gray-200 rounded-lg">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status</label>
                                <select name="status" class="w-full text-sm border-gray-200 rounded-lg">
                                    <option value="present">Present (Full Day)</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="late">Late</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="attendanceModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-500/30">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. Leave Modal -->
        <div x-show="leaveModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="leaveModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Request Leave</h3>
                    <form action="{{ route('hr.leaves.store', $staff->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Start Date</label>
                                    <input type="date" name="start_date" required class="w-full text-sm border-gray-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">End Date</label>
                                    <input type="date" name="end_date" required class="w-full text-sm border-gray-200 rounded-lg">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Leave Type</label>
                                <select name="type" class="w-full text-sm border-gray-200 rounded-lg">
                                    <option value="casual">Casual Leave</option>
                                    <option value="sick">Sick Leave</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason</label>
                                <textarea name="reason" rows="2" required class="w-full text-sm border-gray-200 rounded-lg"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="leaveModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-sm shadow-orange-500/30">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. Advance Modal -->
        <div x-show="advanceModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="advanceModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Add Salary Advance</h3>
                    <form action="{{ route('hr.advances.store', $staff->id) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" name="amount" required class="w-full text-sm border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date</label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason</label>
                                <input type="text" name="reason" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="advanceModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm shadow-indigo-500/30">Add Advance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 4. Allowance Modal -->
        <div x-show="allowanceModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="allowanceModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Add Salary Allowance</h3>
                    <form action="{{ route('hr.adjustments.store', $staff->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="allowance">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" name="amount" required class="w-full text-sm border-gray-200 rounded-lg focus:border-emerald-500 focus:ring-emerald-500" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date</label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason / Description</label>
                                <input type="text" name="reason" placeholder="Ex: Performance Bonus" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="allowanceModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm shadow-emerald-500/30">Save Allowance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 5. Deduction Modal -->
        <div x-show="deductionModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="deductionModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Add Manual Salary Deduction</h3>
                    <form action="{{ route('hr.adjustments.store', $staff->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="deduction">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" name="amount" required class="w-full text-sm border-gray-200 rounded-lg focus:border-rose-500 focus:ring-rose-500" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date</label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Reason / Description</label>
                                <input type="text" name="reason" placeholder="Ex: Uniform Damage Fine" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="deductionModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-sm shadow-rose-500/30">Save Deduction</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function attendanceGrid() {
            const year = {{ $payroll->year }};
            const month = {{ $payroll->month }};
            const standardWorkingHours = {{ $staff->profile->standard_working_hours ?? 8 }};
            const branchPlan = @json(optional($branchPlan)->plan_data ?? []);
            const defaultInTime = "{{ optional($branchPlan)->default_in_time ? \Carbon\Carbon::parse($branchPlan->default_in_time)->format('H:i') : '08:30' }}";
            const defaultOutTime = "{{ optional($branchPlan)->default_out_time ? \Carbon\Carbon::parse($branchPlan->default_out_time)->format('H:i') : '16:30' }}";
            const currentAttendance = @json($attendancesThisMonth->keyBy('date'));
            
            const daysInMonth = new Date(year, month, 0).getDate();
            const days = {};
            
            for (let i = 1; i <= daysInMonth; i++) {
                const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                
                let rule = branchPlan[dateStr] || { type: 'normal' };
                let att = currentAttendance[dateStr] || {};
                
                // Format times for input
                let clockIn = att.clock_in ? att.clock_in.substring(0, 5) : '';
                let clockOut = att.clock_out ? att.clock_out.substring(0, 5) : '';

                days[dateStr] = {
                    rule: rule,
                    status: att.status || (rule.type === 'holiday' ? 'holiday' : ''),
                    clock_in: clockIn,
                    clock_out: clockOut,
                    normal_minutes: att.normal_minutes || 0,
                    ot_minutes: att.ot_minutes || 0,
                };
            }

            return {
                days: days,
                submitData: '',

                formatDate(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                },
                formatDay(dateStr) {
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-US', { weekday: 'short' });
                },
                formatHours(mins) {
                    if(!mins || mins <= 0) return '-';
                    const h = Math.floor(mins / 60);
                    const m = mins % 60;
                    if (h > 0 && m > 0) return `${h}h ${m}m`;
                    if (h > 0) return `${h}h`;
                    return `${m}m`;
                },
                recalculate(dateStr) {
                    const day = this.days[dateStr];
                    
                    if (day.status !== 'present') {
                        day.normal_minutes = 0;
                        day.ot_minutes = 0;
                        return;
                    }

                    // Auto-fill default times if empty
                    if (day.status === 'present') {
                        if (!day.clock_in) day.clock_in = defaultInTime;
                        if (!day.clock_out) day.clock_out = defaultOutTime;
                    }

                    if (!day.clock_in || !day.clock_out) {
                        day.normal_minutes = 0;
                        day.ot_minutes = 0;
                        return;
                    }

                    const inParts = day.clock_in.split(':');
                    const outParts = day.clock_out.split(':');
                    let inMins = parseInt(inParts[0]) * 60 + parseInt(inParts[1]);
                    let outMins = parseInt(outParts[0]) * 60 + parseInt(outParts[1]);
                    
                    // Handle crossing midnight
                    if (outMins < inMins) outMins += 24 * 60;
                    
                    let totalWorkedMins = outMins - inMins;
                    let normalMins = 0;
                    let otMins = 0;
                    
                    if (day.rule.type === 'normal') {
                        let stdMins = standardWorkingHours * 60;
                        if (totalWorkedMins > stdMins) {
                            normalMins = stdMins;
                            otMins = totalWorkedMins - stdMins;
                        } else {
                            normalMins = totalWorkedMins;
                            otMins = 0;
                        }
                    } else if (day.rule.type === 'holiday') {
                        normalMins = 0;
                        otMins = totalWorkedMins; // Fully OT
                    } else if (day.rule.type === 'special') {
                        if (day.rule.special_type === 'fully_ot') {
                            normalMins = 0;
                            otMins = totalWorkedMins;
                        } else if (day.rule.special_type === 'custom') {
                            let stdMins = (day.rule.normal_hours || 0) * 60;
                            if (totalWorkedMins > stdMins) {
                                normalMins = stdMins;
                                otMins = totalWorkedMins - stdMins;
                            } else {
                                normalMins = totalWorkedMins;
                                otMins = 0;
                            }
                        }
                    }
                    
                    day.normal_minutes = normalMins;
                    day.ot_minutes = otMins;
                },
                prepareSubmit() {
                    this.submitData = JSON.stringify(this.days);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>


