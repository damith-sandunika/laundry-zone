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
