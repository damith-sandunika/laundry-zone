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
