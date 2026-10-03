<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('staff.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <p class="text-xs text-gray-400 font-medium">Staff Management</p>
                <h2 class="text-xl font-bold text-gray-800">Add New Employee</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        
        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-green-500 text-xl"></i>
                <div>
                    <h4 class="font-bold">Success!</h4>
                    <p class="text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-start gap-3 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-red-500 text-xl mt-0.5"></i>
                <div>
                    <h4 class="font-bold">Whoops! Something went wrong.</h4>
                    @if(session('error'))
                        <p class="text-sm mt-1">{{ session('error') }}</p>
                    @endif
                    @if($errors->any())
                        <ul class="text-sm list-disc list-inside mt-1 space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <form action="{{ route('staff.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left: Core Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">Basic Information</h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Phone Number <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">NIC (Optional)</label>
                                <input type="text" name="nic" value="{{ old('nic') }}"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Date of Birth</label>
                                <input type="date" name="dob" value="{{ old('dob') }}"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Emergency Contact</label>
                                <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Vehicle Number (Optional)</label>
                                <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="Ex: WP CAD-1234"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Address</label>
                                <textarea name="address" rows="2"
                                    class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">{{ old('address') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- HR & Salary -->
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">HR & Salary Details</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-5">

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Basic Salary (Rs.)</label>
                                <input type="number" step="0.01" name="basic_salary" value="{{ old('basic_salary', 0) }}"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5" placeholder="0.00">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Hourly Rate (Rs.)</label>
                                <input type="number" step="0.01" name="hourly_rate" value="{{ old('hourly_rate', 0) }}"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5" placeholder="0.00">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Allowed Monthly Leaves (Free)</label>
                                <input type="number" name="casual_leaves" value="{{ old('casual_leaves', 2) }}"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Work Hours / Day</label>
                                <input type="number" step="0.5" name="standard_working_hours" value="{{ old('standard_working_hours', 8.0) }}"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">OT Rate (Rs./Hour)</label>
                                <input type="number" step="0.01" name="ot_rate_per_hour" value="{{ old('ot_rate_per_hour', 0) }}"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Account Settings -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="font-bold text-gray-800 text-sm">Account Settings</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">System Role <span class="text-red-500">*</span></label>
                                <select name="role" class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                    <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="rider" {{ old('role') === 'rider' ? 'selected' : '' }}>🚗 Rider</option>
                                    <option value="workshop_admin" {{ old('role') === 'workshop_admin' ? 'selected' : '' }}>⚙️ Workshop Admin</option>
                                    <option value="branch_admin" {{ old('role') === 'branch_admin' ? 'selected' : '' }}>🏪 Branch Admin</option>
                                    @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                                    <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>⭐ Super Admin</option>
                                    @endif
                                </select>
                            </div>

                            @if(\Illuminate\Support\Facades\Gate::allows('is-super-admin') && \App\Models\Setting::get('centralized_management', '1') == '1')
                            <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 mt-4">
                                <label class="flex items-center cursor-pointer">
                                    <div class="relative">
                                        <input type="checkbox" name="can_manage_global_config" value="1" class="sr-only" {{ old('can_manage_global_config') ? 'checked' : '' }}>
                                        <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors"></div>
                                        <div class="dot absolute w-4 h-4 bg-white rounded-full shadow left-1 top-1 transition-transform"></div>
                                    </div>
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-gray-800">Global Configuration Manager</span>
                                        <span class="block text-xs text-gray-500 mt-0.5">Allow this user to manage central settings (Services, Packages, Extras) globally.</span>
                                    </div>
                                </label>
                                <style>
                                    input:checked ~ .dot { transform: translateX(100%); background-color: #2563eb; }
                                    input:checked ~ .w-10 { background-color: #bfdbfe; }
                                </style>
                            </div>
                            @endif

                            @if(auth()->user()->role === 'super_admin' || auth()->user()->role === 'admin')
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Assign to Branch</label>
                                <select name="branch_id" class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5">
                                    <option value="">🌍 Global (All Branches)</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div x-data="{ showPass: false }">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Login Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password" required
                                        class="w-full text-sm bg-gray-50 border-gray-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all py-2.5 px-3.5 pr-10">
                                    <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                        <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-3">
                        <button type="submit"
                            class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all shadow-sm shadow-blue-600/30 text-sm">
                            ✅ Create Staff Account
                        </button>
                        <a href="{{ route('staff.index') }}"
                            class="w-full py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 transition-all text-sm text-center">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>


