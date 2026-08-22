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
                        <p class="text-[10px] uppercase font-bold text-green-500 mb-1">Remaining This Month</p>
                        @php
                            $advancesThisMonth = $staff->salaryAdvances()->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
                            $remainingSalary = max(0, ($staff->basic_salary ?? 0) - $advancesThisMonth);
                        @endphp
                        <p class="text-lg font-black text-green-600">Rs. {{ number_format($remainingSalary, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Attendance & Leaves -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Attendance -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-gray-800">Recent Attendance</h3>
                            <p class="text-[10px] text-gray-400 font-semibold uppercase">Last 7 days</p>
                        </div>
                        <button @click="attendanceModalOpen = true" class="text-xs font-bold px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg border border-blue-100 hover:bg-blue-100 transition">
                            Mark Today
                        </button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->attendances()->latest('date')->take(7)->get() as $att)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">{{ \Carbon\Carbon::parse($att->date)->format('D, M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-500">
                                        In: <span class="font-bold">{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '--' }}</span>
                                        | Out: <span class="font-bold">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '--' }}</span>
                                    </p>
                                </div>
                                <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : ($att->status === 'half_day' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                    {{ str_replace('_', ' ', $att->status) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-semibold text-gray-400">No attendance records found yet.</p>
                            </div>
                        @endforelse
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
