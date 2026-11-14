<!-- HR & Salary Details -->
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
