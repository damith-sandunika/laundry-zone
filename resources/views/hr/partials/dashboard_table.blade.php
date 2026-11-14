<!-- Monthly Staff Attendance & Performance Summary Table -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" 
     x-data="{
        sortCol: 'name',
        sortAsc: true,
        staff: {{ Js::from($staffSummary) }},
        get sortedStaff() {
            return this.staff.sort((a, b) => {
                let valA = a[this.sortCol];
                let valB = b[this.sortCol];
                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }
                if (valA < valB) return this.sortAsc ? -1 : 1;
                if (valA > valB) return this.sortAsc ? 1 : -1;
                return 0;
            });
        },
        sortBy(col) {
            if (this.sortCol === col) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortCol = col;
                this.sortAsc = true;
            }
        },
        formatHM(val) {
            if (!val || val <= 0) return '0h';
            let totalMins = Math.round(val * 60);
            let h = Math.floor(totalMins / 60);
            let m = totalMins % 60;
            if (h > 0 && m > 0) return `${h}h ${m}m`;
            if (h > 0) return `${h}h`;
            return `${m}m`;
        }
     }">
    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h3 class="font-bold text-gray-800 text-base">Monthly Staff Performance Summary</h3>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Calculated based on attendance, working hours, free leaves, and overtime</p>
        </div>
        <a href="{{ route('hr.dashboard.pdf', ['branch_id' => $branchId, 'month' => $month, 'year' => $year]) }}" 
           target="_blank"
           class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-lg transition border border-gray-200">
            🖨️ Print Summary PDF
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase font-black tracking-wider border-b border-gray-200">
                    <th @click="sortBy('name')" class="p-4 cursor-pointer hover:bg-gray-200 transition">
                        <div class="flex items-center gap-1">Staff Member <span x-show="sortCol === 'name'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('branch')" class="p-4 cursor-pointer hover:bg-gray-200 transition">
                        <div class="flex items-center gap-1">Branch <span x-show="sortCol === 'branch'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('present_days')" class="p-4 text-center cursor-pointer hover:bg-gray-200 transition text-emerald-700">
                        <div class="flex items-center justify-center gap-1">Present <span x-show="sortCol === 'present_days'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('leave_days')" class="p-4 text-center cursor-pointer hover:bg-gray-200 transition text-rose-600">
                        <div class="flex items-center justify-center gap-1">Leaves <span x-show="sortCol === 'leave_days'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('total_worked_hours')" class="p-4 text-center cursor-pointer hover:bg-gray-200 transition text-blue-700">
                        <div class="flex items-center justify-center gap-1">Full Work Hrs <span x-show="sortCol === 'total_worked_hours'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('day_hours')" class="p-4 text-center cursor-pointer hover:bg-gray-200 transition text-slate-700">
                        <div class="flex items-center justify-center gap-1">Day Hrs <span x-show="sortCol === 'day_hours'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('ot_hours')" class="p-4 text-center cursor-pointer hover:bg-gray-200 transition text-indigo-600">
                        <div class="flex items-center justify-center gap-1">OT Hrs <span x-show="sortCol === 'ot_hours'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('adjusted_basic_salary')" class="p-4 text-right cursor-pointer hover:bg-gray-200 transition text-slate-800">
                        <div class="flex items-center justify-end gap-1">Regular Pay <span x-show="sortCol === 'adjusted_basic_salary'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('ot_amount')" class="p-4 text-right cursor-pointer hover:bg-gray-200 transition text-indigo-600">
                        <div class="flex items-center justify-end gap-1">OT Pay <span x-show="sortCol === 'ot_amount'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('net_adjustments')" class="p-4 text-right cursor-pointer hover:bg-gray-200 transition text-purple-600">
                        <div class="flex items-center justify-end gap-1">Allowance / Ded. <span x-show="sortCol === 'net_adjustments'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                    <th @click="sortBy('net_salary')" class="p-4 text-right cursor-pointer hover:bg-gray-200 transition text-emerald-800">
                        <div class="flex items-center justify-end gap-1">Total Net Salary <span x-show="sortCol === 'net_salary'" x-text="sortAsc ? '↑' : '↓'"></span></div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="user in sortedStaff" :key="user.id">
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="p-4 font-bold text-gray-800">
                            <a :href="'/staff/' + user.id + '?month={{ $month }}&year={{ $year }}'" class="hover:text-blue-600 transition" x-text="user.name"></a>
                            <p class="text-[10px] text-gray-400 font-semibold" x-text="user.role"></p>
                        </td>
                        <td class="p-4">
                            <span class="text-[10px] uppercase font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded" x-text="user.branch"></span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-sm font-black text-emerald-600" x-text="user.present_days + ' d'"></span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-sm font-black" :class="user.leave_days > 0 ? 'text-rose-600' : 'text-gray-400'" x-text="user.leave_days + ' d'"></span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-sm font-black text-blue-700" x-text="formatHM(user.total_worked_hours)"></span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-sm font-black text-gray-700" x-text="formatHM(user.day_hours)"></span>
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-sm font-black text-indigo-600" x-text="formatHM(user.ot_hours)"></span>
                        </td>
                        <td class="p-4 text-right">
                            <span class="text-xs font-bold text-gray-800" x-text="'Rs. ' + Number(user.adjusted_basic_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </td>
                        <td class="p-4 text-right">
                            <span class="text-xs font-bold text-indigo-600" x-text="'Rs. ' + Number(user.ot_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </td>
                        <td class="p-4 text-right">
                            <span class="text-xs font-bold" :class="user.net_adjustments >= 0 ? 'text-emerald-600' : 'text-rose-600'" 
                                  x-text="(user.net_adjustments >= 0 ? '+' : '') + 'Rs. ' + Number(user.net_adjustments).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </td>
                        <td class="p-4 text-right bg-emerald-50/40">
                            <span class="text-sm font-black text-emerald-800" x-text="'Rs. ' + Number(user.net_salary).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 text-gray-800 font-black border-t-2 border-gray-300">
                    <td colspan="2" class="p-4 text-right text-xs uppercase tracking-wider">Grand Totals:</td>
                    <td class="p-4 text-center text-sm text-emerald-700" x-text="staff.reduce((acc, u) => acc + Number(u.present_days), 0) + ' d'"></td>
                    <td class="p-4 text-center text-sm text-rose-600" x-text="staff.reduce((acc, u) => acc + Number(u.leave_days), 0) + ' d'"></td>
                    <td class="p-4 text-center text-sm text-blue-700" x-text="formatHM(staff.reduce((acc, u) => acc + Number(u.total_worked_hours), 0))"></td>
                    <td class="p-4 text-center text-sm text-slate-700" x-text="formatHM(staff.reduce((acc, u) => acc + Number(u.day_hours), 0))"></td>
                    <td class="p-4 text-center text-sm text-indigo-600" x-text="formatHM(staff.reduce((acc, u) => acc + Number(u.ot_hours), 0))"></td>
                    <td class="p-4 text-right text-sm text-gray-800" x-text="'Rs. ' + staff.reduce((acc, u) => acc + Number(u.adjusted_basic_salary), 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                    <td class="p-4 text-right text-sm text-indigo-600" x-text="'Rs. ' + staff.reduce((acc, u) => acc + Number(u.ot_amount), 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                    <td class="p-4 text-right text-sm text-purple-600" x-text="'Rs. ' + staff.reduce((acc, u) => acc + Number(u.net_adjustments), 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                    <td class="p-4 text-right text-base text-emerald-800 bg-emerald-100/50" x-text="'Rs. ' + staff.reduce((acc, u) => acc + Number(u.net_salary), 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
