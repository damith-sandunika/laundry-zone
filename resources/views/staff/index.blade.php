<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Staff Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage employees, permissions and salary payments</p>
            </div>
            <div class="flex gap-2">
                @if(in_array(auth()->user()->role, ['super_admin', 'branch_admin', 'workshop_admin', 'admin', 'hr_manager', 'accountant_and_hr_manager']))
                <a href="{{ route('branch.month_plan') }}" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-900 transition-colors flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Manage Month Plan
                </a>
                @endif
                <a href="{{ route('staff.create') }}" class="px-4 py-2 bg-blue-600 text-white text-xs font-bold rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-sm">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Staff
                </a>
            </div>
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
                            @if(in_array(auth()->user()->role, ['super_admin', 'hr_manager', 'accountant_and_hr_manager']))
                            <th class="px-6 py-3 font-semibold">Branch</th>
                            @endif
                            <th class="px-6 py-3 font-semibold">Role</th>
                            @if(!in_array(auth()->user()->role, ['super_admin', 'hr_manager', 'accountant_and_hr_manager']))
                            <th class="px-6 py-3 font-semibold text-center">Status</th>
                            @endif
                            <th class="px-6 py-3 font-semibold text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($staff as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ $employee->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                                </td>
                                @if(in_array(auth()->user()->role, ['super_admin', 'hr_manager', 'accountant_and_hr_manager']))
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold text-gray-600">{{ $employee->branch->name ?? 'Global' }}</span>
                                </td>
                                @endif
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 text-[11px] font-bold uppercase rounded-lg bg-gray-100 text-gray-700 tracking-wider">
                                        {{ str_replace('_', ' ', $employee->role) }}
                                    </span>
                                </td>
                                
                                @if(!in_array(auth()->user()->role, ['super_admin', 'hr_manager', 'accountant_and_hr_manager']))
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-lg {{ $employee->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @endif
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- View -->
                                        <a href="{{ route('staff.show', $employee) }}" title="View Profile"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-100 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <!-- Edit -->
                                        <a href="{{ route('staff.edit', $employee) }}" title="Edit"
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('staff.destroy', $employee) }}" onsubmit="return confirm('Delete this staff member?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Delete"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 border border-red-100 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>

        <!-- Add Staff Modal -->
        <div x-show="addModalOpen" class="fixed inset-0 z-[60] overflow-y-auto" style="display: none;">
            <div class="flex items-start justify-center min-h-screen px-4 pt-6 pb-10">
                <div x-show="addModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity" @click="addModalOpen = false">
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                </div>
                <div x-show="addModalOpen" x-transition class="bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-2xl relative z-10 mt-4">
                    <form action="{{ route('staff.store') }}" method="POST">
                        @csrf
                        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Add New Employee</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Fill in the details below to create a new account.</p>
                            </div>
                            <button type="button" @click="addModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="px-6 py-4">
                            <div class="space-y-3">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Full Name</label>
                                        <input type="text" name="name" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Email Address</label>
                                        <input type="email" name="email" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number</label>
                                        <input type="text" name="phone" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">System Role</label>
                                        <select name="role" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="staff">Staff</option>
                                            <option value="rider">🚗 Rider</option>
                                            <option value="workshop_admin">⚙️ Workshop Admin</option>
                                            <option value="branch_admin">🏪 Branch Admin</option>
                                            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                            <option value="super_admin">⭐ Super Admin</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div x-data="{ showPass: false }">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Login Password</label>
                                        <div class="relative">
                                            <input :type="showPass ? 'text' : 'password'" name="password" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10">
                                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- HR Details -->
                                <div class="pt-3 border-t border-gray-100">
                                    <h4 class="text-xs font-bold text-gray-800 uppercase mb-2">HR & Salary Details</h4>
                                    <div class="grid grid-cols-4 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Basic Salary (Rs.)</label>
                                            <input type="number" step="0.01" name="basic_salary" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Casual Leaves / Year</label>
                                            <input type="number" name="casual_leaves" value="14" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Work Hours / Day</label>
                                            <input type="number" step="0.5" name="standard_working_hours" value="8.0" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">OT Rate (Rs./Hour)</label>
                                            <input type="number" step="0.01" name="ot_rate_per_hour" value="0" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">NIC (Optional)</label>
                                            <input type="text" name="nic" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Date of Birth</label>
                                            <input type="date" name="dob" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Emergency Contact</label>
                                            <input type="text" name="emergency_contact" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Vehicle No (Opt)</label>
                                            <input type="text" name="vehicle_number" placeholder="WP CAD-1234" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Address</label>
                                        <textarea name="address" rows="1" class="w-full text-sm border-gray-300 rounded-lg shadow-sm"></textarea>
                                    </div>
                                </div>
                                @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'hr_manager', 'accountant_and_hr_manager']))
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Assign to Branch</label>
                                    <select name="branch_id" class="w-full text-sm border-gray-300 rounded-lg shadow-sm">
                                        <option value="">🌍 Global (All Branches)</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-all shadow-sm shadow-blue-600/30">
                                Add Staff Member
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


