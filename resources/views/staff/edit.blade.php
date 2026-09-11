<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('staff.index') }}" class="hover:text-blue-600">← Staff</a></p>
            <h2 class="text-xl font-bold text-gray-800">Edit Staff — {{ $staff->name }}</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">Account Details</h3>
                <p class="text-sm text-gray-500">Update the staff member's profile and permissions.</p>
            </div>
            
            <form action="{{ route('staff.update', $staff) }}" method="POST" autocomplete="off" class="p-6 space-y-6">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $staff->email) }}" required
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                            class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Account Role <span class="text-red-500">*</span></label>
                        <select name="role" class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="staff" {{ $staff->role === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="rider" {{ $staff->role === 'rider' ? 'selected' : '' }}>🚗 Rider</option>
                            <option value="workshop_admin" {{ $staff->role === 'workshop_admin' ? 'selected' : '' }}>⚙️ Workshop Admin</option>
                            <option value="branch_admin" {{ $staff->role === 'branch_admin' ? 'selected' : '' }}>🏪 Branch Admin</option>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin')
                            <option value="super_admin" {{ $staff->role === 'super_admin' ? 'selected' : '' }}>⭐ Super Admin</option>
                            @endif
                        </select>
                    </div>

                    @if(auth()->user()->role === 'super_admin')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Assign to Branch <span class="text-red-500">*</span></label>
                        <select name="branch_id" class="w-full text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                            <option value="">🌍 Global (All Branches)</option>
                            @foreach(\App\Models\Branch::all() as $b)
                                <option value="{{ $b->id }}" {{ $staff->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Account Status <span class="text-red-500">*</span></label>
                    <select name="is_active" class="w-full md:w-1/2 text-sm bg-gray-50 border-gray-200 rounded-lg focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        <option value="1" {{ $staff->is_active ? 'selected' : '' }}>✅ Active - Can Login</option>
                        <option value="0" {{ !$staff->is_active ? 'selected' : '' }}>🚫 Suspended - Cannot Login</option>
                    </select>
                </div>

                <!-- HR Details -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <h4 class="text-sm font-bold text-gray-700">HR & Salary Details</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Basic Salary (Rs.)</label>
                            <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', $staff->profile->basic_salary ?? 0) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Hourly Rate (Rs.)</label>
                            <input type="number" step="0.01" name="hourly_rate" value="{{ old('hourly_rate', $staff->profile->hourly_rate ?? 0) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Daily Rate (Rs.)</label>
                            <input type="number" step="0.01" name="daily_rate" value="{{ old('daily_rate', $staff->profile->daily_rate ?? 0) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Allowed Monthly Leaves (Free)</label>
                            <input type="number" name="casual_leaves" value="{{ old('casual_leaves', $staff->profile->casual_leaves ?? 2) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Standard Working Hours</label>
                            <input type="number" step="0.5" name="standard_working_hours" value="{{ old('standard_working_hours', $staff->profile->standard_working_hours ?? 8) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">OT Rate (Rs. Per Hour)</label>
                            <input type="number" step="0.01" name="ot_rate_per_hour" value="{{ old('ot_rate_per_hour', $staff->profile->ot_rate_per_hour ?? 0) }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">NIC (Optional)</label>
                            <input type="text" name="nic" value="{{ old('nic', $staff->profile->nic ?? '') }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob', $staff->profile?->dob ? \Carbon\Carbon::parse($staff->profile->dob)->format('Y-m-d') : '') }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Emergency Contact</label>
                            <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $staff->profile->emergency_contact ?? '') }}"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Vehicle Number (Optional)</label>
                            <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $staff->profile->vehicle_number ?? '') }}" placeholder="Ex: WP CAD-1234"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Address</label>
                            <textarea name="address" rows="2"
                                class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all">{{ old('address', $staff->profile->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <h4 class="text-sm font-bold text-gray-700">Change Password (Optional)</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div x-data="{ showPass: false }">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">New Password</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                                    class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all pr-10"
                                    placeholder="Leave blank to keep current">
                                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div x-data="{ showPass: false }">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Confirm Password</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly');"
                                    class="w-full text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all pr-10"
                                    placeholder="Re-type new password">
                                <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('staff.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-all shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm shadow-blue-600/30">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>


