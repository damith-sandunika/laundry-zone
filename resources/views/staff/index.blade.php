<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Staff Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage employees, permissions and salary payments</p>
            </div>
            <button @click="$dispatch('open-add-staff')" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add Staff
            </button>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="display:flex;align-items:center;gap:10px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 18px;margin-bottom:24px;">
            <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <span style="font-size:13px;font-weight:600;color:#15803d;">{{ session('success') }}</span>
        </div>
    @endif

    <div x-data="{ addModalOpen: false, salaryModalOpen: false, selectedUser: null, selectedMonth: '', notComingModalOpen: false, notComingUser: null }" @open-add-staff.window="addModalOpen = true" style="display:flex;flex-direction:column;gap:20px;">

        <div>
            <!-- Staff List -->
            <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 font-semibold">Name / Email</th>
                            @if(auth()->user()->role === 'super_admin')
                            <th class="px-6 py-3 font-semibold">Branch</th>
                            @endif
                            <th class="px-6 py-3 font-semibold">Role</th>
                            @if(auth()->user()->role !== 'super_admin')
                            <th class="px-6 py-3 font-semibold text-center">Status</th>
                            <th class="px-6 py-3 font-semibold text-center bg-blue-50/50 rounded-t-lg">Today's Attendance</th>
                            @endif
                            <th class="px-6 py-3 font-semibold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($staff as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $employee->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                                </td>
                                @if(auth()->user()->role === 'super_admin')
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold text-gray-600">{{ $employee->branch->name ?? 'Global' }}</span>
                                </td>
                                @endif
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-lg bg-gray-100 text-gray-700 tracking-wider">
                                        {{ str_replace('_', ' ', $employee->role) }}
                                    </span>
                                </td>
                                
                                @if(auth()->user()->role !== 'super_admin')
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ $employee->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                
                                <!-- Quick Attendance Action -->
                                <td class="px-6 py-4 text-center bg-blue-50/20 border-x border-gray-100">
                                    @php
                                        $todayAtt = $employee->attendances()->whereDate('date', now()->format('Y-m-d'))->first();
                                    @endphp
                                    
                                    @if($todayAtt)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-lg {{ $todayAtt->status === 'present' ? 'bg-green-100 text-green-700' : ($todayAtt->status === 'late' ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                                {{ $todayAtt->status }}
                                            </span>
                                            
                                            @if($todayAtt->status !== 'absent')
                                                <div class="text-[10px] font-semibold text-gray-500 mt-0.5">
                                                    In: {{ $todayAtt->clock_in ? \Carbon\Carbon::parse($todayAtt->clock_in)->format('h:i A') : '--' }}
                                                </div>
                                                
                                                @if($todayAtt->clock_out)
                                                    <div class="text-[10px] font-semibold text-gray-500">
                                                        Out: {{ \Carbon\Carbon::parse($todayAtt->clock_out)->format('h:i A') }}
                                                    </div>
                                                @else
                                                    <form method="POST" action="{{ route('hr.attendance.update', $todayAtt) }}" class="mt-1">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="clock_out" value="{{ now()->format('H:i') }}">
                                                        <button type="submit" class="px-2 py-0.5 text-[9px] font-bold uppercase bg-gray-100 text-gray-600 rounded hover:bg-gray-200 border border-gray-300">
                                                            Mark Out
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center gap-1">
                                            <form method="POST" action="{{ route('hr.attendance.store', $employee) }}">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                                <input type="hidden" name="status" value="present">
                                                <button type="submit" class="px-2 py-1 text-[10px] font-bold uppercase bg-green-50 text-green-600 rounded hover:bg-green-100 border border-green-200" title="Present">✔️ P</button>
                                            </form>
                                            <form method="POST" action="{{ route('hr.attendance.store', $employee) }}">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                                <input type="hidden" name="status" value="late">
                                                <button type="submit" class="px-2 py-1 text-[10px] font-bold uppercase bg-orange-50 text-orange-600 rounded hover:bg-orange-100 border border-orange-200" title="Late">⏰ L</button>
                                            </form>
                                            <button @click="notComingUser = {{ $employee }}; notComingModalOpen = true" type="button" class="px-2 py-1 text-[10px] font-bold uppercase bg-red-50 text-red-600 rounded hover:bg-red-100 border border-red-200" title="Not Coming">❌ N</button>
                                        </div>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 text-center space-x-2">
                                    @if(auth()->user()->role !== 'super_admin')
                                    <button 
                                        @click="selectedUser = {{ $employee }}; selectedMonth = '{{ now()->format('F Y') }}'; salaryModalOpen = true"
                                        class="text-xs font-semibold px-2.5 py-1 bg-green-50 border border-green-200 text-green-600 rounded hover:bg-green-100">
                                        Pay Salary
                                    </button>
                                    @endif
                                    <a href="{{ route('staff.show', $employee) }}" class="text-xs font-semibold px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-600 rounded hover:bg-blue-100">
                                        View
                                    </a>
                                    <a href="{{ route('staff.edit', $employee) }}" class="text-xs font-semibold px-2.5 py-1 bg-gray-50 border border-gray-200 text-gray-600 rounded hover:bg-gray-100">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('staff.destroy', $employee) }}" onsubmit="return confirm('Delete this staff member?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold px-2.5 py-1 bg-red-50 border border-red-200 text-red-600 rounded hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>

        <!-- Add Staff Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="addModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="addModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('staff.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Employee</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" name="name" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" name="email" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="text" name="phone" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">System Role</label>
                                        <select name="role" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                            <option value="staff">Staff (Cashier / Washer)</option>
                                            <option value="workshop_admin">Workshop Operator</option>
                                            <option value="rider">Delivery Rider</option>
                                            @if(auth()->user()->role === 'super_admin')
                                            <option value="admin">Admin</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Login Password</label>
                                        <input type="password" name="password" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                    </div>
                                </div>
                                
                                <!-- HR Details -->
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase mb-3">HR & Salary Details</h4>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Basic Salary (Rs.)</label>
                                            <input type="number" step="0.01" name="basic_salary" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Casual Leaves (Per Year)</label>
                                            <input type="number" name="casual_leaves" value="14" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
                                            <input type="date" name="dob" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Emergency Contact</label>
                                            <input type="text" name="emergency_contact" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">NIC (Optional)</label>
                                            <input type="text" name="nic" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Date of Birth</label>
                                            <input type="date" name="dob" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Emergency Contact</label>
                                            <input type="text" name="emergency_contact" class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Address</label>
                                        <textarea name="address" rows="2" class="w-full text-sm border-gray-300 rounded-md shadow-sm"></textarea>
                                    </div>
                                </div>
                                @if(auth()->user()->role === 'super_admin')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Assign to Branch</label>
                                    <select name="branch_id" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Add Staff Member
                            </button>
                            <button type="button" @click="addModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pay Salary Modal -->
        <div x-show="salaryModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="salaryModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="salaryModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="salaryModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('staff.paySalary') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" x-bind:value="selectedUser?.id">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="'Record Salary Payment for ' + selectedUser?.name"></h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Salary Month</label>
                                    <input type="text" name="month" x-model="selectedMonth" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Paid Amount (Rs.)</label>
                                    <input type="number" step="0.01" name="amount" required class="w-full text-sm border-gray-300 rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Log Payment
                            </button>
                            <button type="button" @click="salaryModalOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Not Coming (Leave) Modal -->
        <div x-show="notComingModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                <div x-show="notComingModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="notComingModalOpen = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="notComingModalOpen" x-transition class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <form x-bind:action="`/hr/${notComingUser?.id}/leaves`" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">Mark as Not Coming</h3>
                            <p class="text-xs text-gray-500 mb-4" x-text="`Record leave for ${notComingUser?.name}`"></p>
                            
                            <input type="hidden" name="start_date" value="{{ now()->format('Y-m-d') }}">
                            <input type="hidden" name="end_date" value="{{ now()->format('Y-m-d') }}">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Leave Type</label>
                                    <select name="type" required class="w-full text-sm border-gray-200 rounded-lg">
                                        <option value="casual">Casual Leave</option>
                                        <option value="sick">Sick Leave</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description / Reason</label>
                                    <textarea name="reason" rows="2" required placeholder="Ex: Family matter, fever..." class="w-full text-sm border-gray-200 rounded-lg"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-3">
                            <button type="button" @click="notComingModalOpen = false" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700">
                                Save Leave
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
