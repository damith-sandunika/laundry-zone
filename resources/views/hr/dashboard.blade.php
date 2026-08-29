<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight">HR Dashboard</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Company-wide Human Resources Overview</p>
            </div>
            
            <form method="GET" action="{{ route('hr.dashboard') }}" class="flex flex-wrap items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg focus:ring-blue-500 font-medium bg-white">
                    <option value="">All Branches (Global)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <select name="month" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg focus:ring-blue-500 font-medium bg-white">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                        </option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg focus:ring-blue-500 font-medium bg-white">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <!-- Total Staff -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full opacity-50"></div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Staff</p>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-gray-800">{{ $totalStaff }}</span>
                    <span class="text-xs font-semibold text-blue-500 bg-blue-50 px-2 py-0.5 rounded mb-1">Active</span>
                </div>
            </div>

            <!-- Attendance Today -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-green-50 rounded-full opacity-50"></div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Present Today</p>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-gray-800">{{ $presentCount + $halfDayCount }}</span>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded mb-1">/ {{ $totalStaff }}</span>
                </div>
                @if($lateCount > 0)
                    <p class="text-[10px] font-bold text-orange-500 mt-2">{{ $lateCount }} marked as Late</p>
                @endif
            </div>

            <!-- On Leave -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-red-50 rounded-full opacity-50"></div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">On Leave Today</p>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-gray-800">{{ $leavesToday }}</span>
                </div>
                @if($notMarkedCount > 0)
                    <p class="text-[10px] font-bold text-gray-400 mt-2">{{ $notMarkedCount }} not marked yet</p>
                @endif
            </div>

            <!-- Advances -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full opacity-50"></div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Advances (This Month)</p>
                <div class="flex items-end gap-2">
                    <span class="text-2xl font-black text-indigo-600">Rs. {{ number_format($advancesThisMonth) }}</span>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Pending Leave Requests -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                            Pending Leave Requests
                        </h3>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($pendingLeaves as $leave)
                            <div class="p-5 hover:bg-gray-50/50 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex gap-4 items-start">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($leave->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-900">{{ $leave->user->name }}</p>
                                        <p class="text-[11px] font-semibold text-gray-500 mb-1">
                                            {{ $leave->user->branch ? $leave->user->branch->name : 'Global' }} • {{ ucfirst($leave->user->role) }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $leave->type === 'sick' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                                {{ ucfirst($leave->type) }}
                                            </span>
                                            <span class="text-xs font-bold text-gray-700">
                                                {{ \Carbon\Carbon::parse($leave->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2 bg-gray-100 p-2 rounded w-full md:w-auto">"{{ $leave->reason }}"</p>
                                    </div>
                                </div>
                                <div class="flex gap-2 shrink-0 md:ml-auto">
                                    <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-white bg-green-500 hover:bg-green-600 rounded-lg shadow-sm">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('hr.leaves.update', $leave->id) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-lg">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-bold text-gray-400">No pending leave requests!</p>
                                <p class="text-xs font-medium text-gray-400 mt-1">You're all caught up.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Log -->
            <div>
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Recent Attendance Marks</h3>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($recentAttendances as $att)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ $att->user->name }}</p>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase">{{ $att->user->branch ? $att->user->branch->name : 'Global' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $att->status === 'present' ? 'bg-green-100 text-green-700' : ($att->status === 'half_day' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                        {{ str_replace('_', ' ', $att->status) }}
                                    </span>
                                    <p class="text-[10px] text-gray-400 mt-1 font-semibold">{{ $att->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-xs font-semibold text-gray-400">No recent records.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Monthly Staff Attendance Summary Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" 
             x-data="{
                sortCol: 'name',
                sortAsc: true,
                staff: {{ Js::from($staffSummary) }},
                get sortedStaff() {
                    return this.staff.sort((a, b) => {
                        let valA = a[this.sortCol];
                        let valB = b[this.sortCol];
                        
                        if (typeof valA === 'string') {
                            valA = valA.toLowerCase();
                            valB = valB.toLowerCase();
                        }
                        
                        if (valA < valB) return this.sortAsc ? -1 : 1;
                        if (valA > valB) return this.sortAsc ? 1 : -1;
                        return 0;
                    });
                },
                sortBy(col) {
                    if (this.sortCol === col) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = col;
                        this.sortAsc = true;
                    }
                }
             }">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">
                    Monthly Attendance & Performance Summary ({{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }})
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase font-extrabold tracking-wider border-b border-gray-100">
                            <th @click="sortBy('name')" class="p-4 cursor-pointer hover:bg-gray-100 transition">
                                <div class="flex items-center gap-2">Staff Name <span x-show="sortCol === 'name'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('branch')" class="p-4 cursor-pointer hover:bg-gray-100 transition">
                                <div class="flex items-center gap-2">Branch <span x-show="sortCol === 'branch'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('present')" class="p-4 text-center cursor-pointer hover:bg-gray-100 transition text-green-700">
                                <div class="flex items-center justify-center gap-2">Present <span x-show="sortCol === 'present'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('late')" class="p-4 text-center cursor-pointer hover:bg-gray-100 transition text-orange-600">
                                <div class="flex items-center justify-center gap-2">Late <span x-show="sortCol === 'late'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('half_day')" class="p-4 text-center cursor-pointer hover:bg-gray-100 transition text-blue-600">
                                <div class="flex items-center justify-center gap-2">Half Day <span x-show="sortCol === 'half_day'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('leave_days')" class="p-4 text-center cursor-pointer hover:bg-gray-100 transition text-red-600">
                                <div class="flex items-center justify-center gap-2">Leaves <span x-show="sortCol === 'leave_days'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('ot_minutes')" class="p-4 text-center cursor-pointer hover:bg-gray-100 transition text-indigo-600">
                                <div class="flex items-center justify-center gap-2">OT Time <span x-show="sortCol === 'ot_minutes'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('ot_amount')" class="p-4 text-right cursor-pointer hover:bg-gray-100 transition text-indigo-600">
                                <div class="flex items-center justify-end gap-2">OT Amount <span x-show="sortCol === 'ot_amount'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('advances')" class="p-4 text-right cursor-pointer hover:bg-gray-100 transition text-pink-600">
                                <div class="flex items-center justify-end gap-2">Advances <span x-show="sortCol === 'advances'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                            <th @click="sortBy('total_salary')" class="p-4 text-right cursor-pointer hover:bg-gray-100 transition text-green-700">
                                <div class="flex items-center justify-end gap-2">Total Salary <span x-show="sortCol === 'total_salary'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="user in sortedStaff" :key="user.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4">
                                    <a :href="'/staff/' + user.id" class="font-bold text-blue-600 hover:text-blue-800 hover:underline text-sm" x-text="user.name"></a>
                                </td>
                                <td class="p-4">
                                    <span class="text-[10px] uppercase font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded" x-text="user.branch"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-sm font-black" :class="user.present > 0 ? 'text-green-600' : 'text-gray-400'" x-text="user.present"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-sm font-black" :class="user.late > 0 ? 'text-orange-500' : 'text-gray-400'" x-text="user.late"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-sm font-black" :class="user.half_day > 0 ? 'text-blue-500' : 'text-gray-400'" x-text="user.half_day"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-sm font-black" :class="user.leave_days > 0 ? 'text-red-600' : 'text-gray-400'" x-text="user.leave_days"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="text-sm font-black text-indigo-600" x-text="Math.floor(user.ot_minutes / 60) + 'h ' + (user.ot_minutes % 60) + 'm'"></span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="text-sm font-black text-indigo-600" x-text="'Rs. ' + Number(user.ot_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </td>
                                <td class="p-4 text-right">
                                    <span class="text-sm font-black text-pink-600" x-text="user.advances > 0 ? ('Rs. ' + Number(user.advances).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})) : '-'"></span>
                                </td>
                                <td class="p-4 text-right bg-green-50/30">
                                    <span class="text-base font-black text-green-700" x-text="'Rs. ' + Number(user.total_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
