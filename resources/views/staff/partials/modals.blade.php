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
                                <input type="date" name="date" required value="{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
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
                                <input type="date" name="date" required value="{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
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
                                <input type="date" name="date" required value="{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('Y-m-d') }}" class="w-full text-sm border-gray-200 rounded-lg">
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

        <!-- 6. Edit Salary Advance Modal -->
        <div x-show="editAdvanceModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="editAdvanceModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Salary Advance</h3>
                    <form x-bind:action="'/hr/advances/' + editingAdvance.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Amount (Rs.) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="amount" required x-model="editingAdvance.amount" class="w-full text-sm border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Date <span class="text-rose-500">*</span></label>
                                <input type="date" name="date" required x-model="editingAdvance.date" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Advance Reason / Note</label>
                                <input type="text" name="reason" x-model="editingAdvance.reason" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-indigo-700 uppercase mb-1">Reason for Editing <span class="text-rose-500">*</span></label>
                                <textarea name="edit_reason" required minlength="3" rows="2" placeholder="Explain why this advance is being modified..." class="w-full text-sm border-indigo-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 bg-indigo-50/30"></textarea>
                                <p class="text-[11px] text-gray-400 mt-0.5">Required for audit tracking.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="editAdvanceModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm shadow-indigo-500/30">Update Advance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 7. Edit Salary Payment (Expense) Modal -->
        <div x-show="editSalaryModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="editSalaryModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Salary Payment Record</h3>
                    <form x-bind:action="'/staff/salaries/' + editingSalary.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Amount Paid (Rs.) <span class="text-rose-500">*</span></label>
                                <input type="number" step="0.01" name="amount" required x-model="editingSalary.amount" class="w-full text-sm border-gray-200 rounded-lg focus:border-green-500 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Payment Date</label>
                                <input type="date" name="expense_date" x-model="editingSalary.expense_date" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description</label>
                                <input type="text" name="description" x-model="editingSalary.description" class="w-full text-sm border-gray-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-green-700 uppercase mb-1">Reason for Editing <span class="text-rose-500">*</span></label>
                                <textarea name="edit_reason" required minlength="3" rows="2" placeholder="Explain why this salary payment is being modified..." class="w-full text-sm border-green-200 rounded-lg focus:border-green-500 focus:ring-green-500 bg-green-50/30"></textarea>
                                <p class="text-[11px] text-gray-400 mt-0.5">Required for audit tracking.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="editSalaryModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-sm shadow-green-500/30">Update Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 8. Delete Salary Advance Modal -->
        <div x-show="deleteAdvanceModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="deleteAdvanceModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Delete Salary Advance</h3>
                            <p class="text-xs text-gray-500">This record will be archived with audit details.</p>
                        </div>
                    </div>
                    <form x-bind:action="'/hr/advances/' + deletingAdvanceId" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-4">
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-xs">
                                <strong>Notice:</strong> Deleting this advance will restore this amount to the staff member's payable salary balance.
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Reason for Deletion <span class="text-rose-500">*</span></label>
                                <textarea name="delete_reason" required minlength="3" rows="3" placeholder="Provide a detailed reason for deleting this advance record..." class="w-full text-sm border-rose-200 rounded-lg focus:border-rose-500 focus:ring-rose-500 bg-rose-50/20"></textarea>
                                <p class="text-[11px] text-gray-400 mt-0.5">A mandatory reason is required for audit logs.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="deleteAdvanceModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-sm shadow-rose-500/30">Delete Advance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 9. Delete Salary Payment Modal -->
        <div x-show="deleteSalaryModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="deleteSalaryModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Delete Salary Payment Record</h3>
                            <p class="text-xs text-gray-500">This payment will be soft-deleted and logged.</p>
                        </div>
                    </div>
                    <form x-bind:action="'/staff/salaries/' + deletingSalaryId" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-4">
                            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-amber-800 text-xs">
                                <strong>Notice:</strong> Deleting this payment will restore the amount back to the pending payable salary balance.
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Reason for Deletion <span class="text-rose-500">*</span></label>
                                <textarea name="delete_reason" required minlength="3" rows="3" placeholder="Provide a detailed reason for deleting this salary payment..." class="w-full text-sm border-rose-200 rounded-lg focus:border-rose-500 focus:ring-rose-500 bg-rose-50/20"></textarea>
                                <p class="text-[11px] text-gray-400 mt-0.5">A mandatory reason is required for audit logs.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="deleteSalaryModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 shadow-sm shadow-rose-500/30">Delete Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 10. Partial / Full Salary Payment Modal -->
        <div x-show="paySalaryModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="paySalaryModalOpen = false"></div>
                <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all max-w-md w-full relative z-10 p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-green-100 text-green-700 flex items-center justify-center font-bold text-base">
                                💸
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Disburse Salary Payment</h3>
                                <p class="text-xs text-gray-500">{{ $staff->name }} — {{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</p>
                            </div>
                        </div>
                        <button type="button" @click="paySalaryModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                    </div>

                    <form action="{{ route('staff.paySalary') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $staff->id }}">
                        <input type="hidden" name="month" value="{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}">

                        <div class="space-y-4">
                            <!-- Payable Balance Info Card -->
                            <div class="p-3.5 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl border border-emerald-200/60">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-gray-600 font-medium">Current Payable Balance:</span>
                                    <span class="font-extrabold text-emerald-800 text-base">Rs. {{ number_format($payroll->net_salary, 2) }}</span>
                                </div>
                                @if($payroll->already_paid > 0)
                                <div class="flex justify-between items-center text-[11px] text-gray-500 mt-1 pt-1 border-t border-emerald-200/40">
                                    <span>Previously Disbursed:</span>
                                    <span class="font-semibold text-orange-600">Rs. {{ number_format($payroll->already_paid, 2) }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Amount Input (Editable for Partial Payment) -->
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-xs font-bold text-gray-700 uppercase">Payment Amount (Rs.) <span class="text-rose-500">*</span></label>
                                    <button type="button" @click="paySalaryAmount = '{{ $payroll->net_salary }}'" class="text-[11px] font-bold text-emerald-600 hover:underline">
                                        Pay Full Balance
                                    </button>
                                </div>
                                <div class="relative rounded-lg shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 font-bold text-sm">
                                        Rs.
                                    </div>
                                    <input type="number" 
                                           step="0.01" 
                                           name="amount" 
                                           required 
                                           min="0.01" 
                                           max="{{ $payroll->net_salary }}"
                                           x-model="paySalaryAmount" 
                                           class="w-full pl-10 text-base font-bold text-gray-900 border-gray-300 rounded-lg focus:border-green-500 focus:ring-green-500" 
                                           placeholder="0.00">
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">
                                    You can enter any partial amount up to Rs. {{ number_format($payroll->net_salary, 2) }}. The remaining balance can be paid later.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="paySalaryModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                            <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md shadow-green-600/30 flex items-center gap-1.5">
                                <span>Confirm & Pay</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
