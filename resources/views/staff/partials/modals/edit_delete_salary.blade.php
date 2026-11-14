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

