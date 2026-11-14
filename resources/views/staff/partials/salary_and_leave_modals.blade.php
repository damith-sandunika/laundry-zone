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
