<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="text-2xl font-black text-gray-800 tracking-tight">HR Dashboard</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Company-wide Human Resources Overview</p>
            </div>
            
            <form method="GET" action="{{ route('hr.dashboard') }}" class="flex items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()" class="text-sm border-gray-200 rounded-lg focus:ring-blue-500 font-medium">
                    <option value="">All Branches (Global)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
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
    </div>
</x-app-layout>
