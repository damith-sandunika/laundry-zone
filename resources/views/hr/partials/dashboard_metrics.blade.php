<!-- Top Metrics Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
    
    <!-- Total Staff -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full opacity-50"></div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Staff</p>
        <div class="flex items-end gap-2">
            <span class="text-4xl font-black text-gray-800">{{ $totalStaff }}</span>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded mb-1">Active</span>
        </div>
    </div>

    <!-- Total Salary Pay -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full opacity-50"></div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Salary Pay</p>
        <div class="flex items-end gap-2">
            <span class="text-2xl font-black text-emerald-700">Rs. {{ number_format($totalSalaryPay, 2) }}</span>
        </div>
        <p class="text-[10px] font-bold text-emerald-600 mt-2">{{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }} Net Payable</p>
    </div>

    <!-- Total OT Pay -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full opacity-50"></div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total OT Pay</p>
        <div class="flex items-end gap-2">
            <span class="text-2xl font-black text-indigo-600">Rs. {{ number_format($totalOtPay, 2) }}</span>
        </div>
        <p class="text-[10px] font-bold text-indigo-500 mt-2">Overtime Expenses</p>
    </div>

    <!-- Total Working Hours -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 flex flex-col justify-center shadow-sm relative overflow-hidden">
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-50 rounded-full opacity-50"></div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Working Hours</p>
        <div class="flex items-end gap-2">
            <span class="text-3xl font-black text-amber-600">{{ number_format($totalWorkingHours, 1) }}</span>
            <span class="text-xs font-bold text-amber-500 mb-1">Hours</span>
        </div>
        <p class="text-[10px] font-bold text-amber-500 mt-2">Total Hours Worked</p>
    </div>

</div>
