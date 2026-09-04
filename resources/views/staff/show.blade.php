<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-400 mb-1"><a href="{{ route('staff.index') }}" class="hover:text-blue-600">← Staff Directory</a></p>
                <h2 class="text-xl font-bold text-gray-800">{{ $staff->name }}'s Profile</h2>
            </div>
            <a href="{{ route('staff.edit', $staff) }}" class="px-4 py-2 bg-blue-50 text-blue-600 font-bold text-xs rounded-lg hover:bg-blue-100 transition-colors">
                Edit Profile
            </a>
        </div>
    </x-slot>

    <div x-data="{ 
        attendanceModalOpen: false, 
        leaveModalOpen: false, 
        advanceModalOpen: false 
    }" class="max-w-6xl mx-auto space-y-6">
        
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
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->nic ?? 'Not Provided' }}</p>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Date of Birth</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('M d, Y') : 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Residential Address</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $staff->address ?? 'Not Provided' }}</p>
                </div>
                <div class="md:col-span-2 pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-red-400 mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Emergency Contact
                        </p>
                        <p class="text-sm font-bold text-gray-800">{{ $staff->emergency_contact ?? 'Not Provided' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-blue-500 mb-1">Casual Leaves Left</p>
                        @php
                            $approvedLeavesCount = $staff->leaves()->where('type', 'casual')->where('status', 'approved')
                                ->whereYear('start_date', now()->year)->sum(\Illuminate\Support\Facades\DB::raw('DATEDIFF(end_date, start_date) + 1'));
                            $leavesLeft = max(0, ($staff->casual_leaves ?? 0) - $approvedLeavesCount);
                        @endphp
                        <p class="text-lg font-black text-gray-800">{{ $leavesLeft }} / {{ $staff->casual_leaves ?? 0 }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-500 mb-1">Basic Monthly Salary</p>
                        <p class="text-lg font-black text-gray-800">Rs. {{ number_format($staff->basic_salary ?? 0, 2) }}</p>
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
                <!-- Attendance Calendar -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-visible">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-gray-800">Attendance Calendar</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <!-- Legends -->
                            <div class="hidden md:flex items-center gap-3 mr-4 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-400"></span> Present</span>
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400"></span> Absent</span>
                                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400"></span> Late</span>
                            </div>
                            <button @click="attendanceModalOpen = true" class="text-xs font-bold px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg border border-blue-100 hover:bg-blue-100 transition">
                                Mark Today
                            </button>
                        </div>
                    </div>
                    <div class="p-5">
                        @php
                            $startOfMonth = \Carbon\Carbon::createFromDate($payroll->year, $payroll->month, 1);
                            $endOfMonth = $startOfMonth->copy()->endOfMonth();
                            $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
                            $attendanceMap = $attendancesThisMonth->keyBy('date');
                        @endphp
                        
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">{{ $day }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-2 text-center">
                            @for($i = 0; $i < $startDayOfWeek; $i++)
                                <div class="p-2 border border-transparent"></div>
                            @endfor
                            
                            @for($day = 1; $day <= $endOfMonth->day; $day++)
                                @php
                                    $currentDateStr = $startOfMonth->copy()->addDays($day - 1)->toDateString();
                                    $isToday = $currentDateStr === now()->toDateString();
                                    $att = $attendanceMap->get($currentDateStr);
                                    
                                    // Default (No Record / Future / Weekend)
                                    $statusColor = 'bg-gray-50 border-gray-100 text-gray-400 hover:bg-gray-100'; 
                                    
                                    if($att) {
                                        if($att->status === 'present') {
                                            $statusColor = 'bg-green-50 border-green-200 text-green-700 shadow-sm shadow-green-100 hover:bg-green-100';
                                        } elseif($att->status === 'hold') {
                                            $statusColor = 'bg-red-50 border-red-200 text-red-700 shadow-sm shadow-red-100 hover:bg-red-100';
                                        } elseif($att->status === 'late') {
                                            $statusColor = 'bg-orange-50 border-orange-200 text-orange-700 shadow-sm shadow-orange-100 hover:bg-orange-100';
                                        } elseif($att->status === 'half_day') {
                                            $statusColor = 'bg-yellow-50 border-yellow-200 text-yellow-700 shadow-sm shadow-yellow-100 hover:bg-yellow-100';
                                        }
                                    }
                                @endphp
                                <div class="aspect-square flex flex-col items-center justify-center rounded-xl border transition-all relative group cursor-pointer {{ $statusColor }} {{ $isToday ? 'ring-2 ring-blue-500 ring-offset-2' : '' }}">
                                    <span class="text-sm font-black {{ $isToday && !$att ? 'text-blue-600' : '' }}">{{ $day }}</span>
                                    
                                    @if($att)
                                        <span class="text-[9px] font-bold uppercase mt-0.5 tracking-tighter hidden md:block">
                                            {{ $att->status === 'hold' ? 'Absent' : str_replace('_', ' ', $att->status) }}
                                        </span>
                                        
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] py-1.5 px-3 rounded-lg opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity z-10 shadow-xl">
                                            <div class="font-bold border-b border-gray-600 pb-1 mb-1">{{ \Carbon\Carbon::parse($currentDateStr)->format('M d, Y') }}</div>
                                            <div class="text-gray-300">Status: <span class="text-white font-bold">{{ ucfirst(str_replace('_', ' ', $att->status)) }}</span></div>
                                            @if($att->clock_in)
                                                <div class="text-gray-300">In: <span class="text-white">{{ \Carbon\Carbon::parse($att->clock_in)->format('h:i A') }}</span></div>
                                            @endif
                                            @if($att->clock_out)
                                                <div class="text-gray-300">Out: <span class="text-white">{{ \Carbon\Carbon::parse($att->clock_out)->format('h:i A') }}</span></div>
                                            @endif
                                            <!-- Tooltip Arrow -->
                                            <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-l-[4px] border-r-[4px] border-t-[4px] border-l-transparent border-r-transparent border-t-gray-800"></div>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
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
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Basic Salary:</span>
                            <span class="font-bold text-gray-800">Rs. {{ number_format($staff->basic_salary ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">OT ({{ floor($payroll->ot_minutes / 60) }}h {{ $payroll->ot_minutes % 60 }}m):</span>
                            <span class="font-bold text-indigo-600">+ Rs. {{ number_format($payroll->ot_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Advances:</span>
                            <span class="font-bold text-red-500">- Rs. {{ number_format($payroll->advances, 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-green-200/50 flex justify-between">
                            <span class="font-black text-gray-800">Net Pay:</span>
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

    </div>
</x-app-layout>

