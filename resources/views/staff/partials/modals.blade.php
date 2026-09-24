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
