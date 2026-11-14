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
        @include('staff.partials.add_staff_modal', ['branches' => $branches])

        <!-- Salary and Leave Modals -->
        @include('staff.partials.salary_and_leave_modals')
    </div>
</x-app-layout>


