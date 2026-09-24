        <!-- Info Grid -->
        <div class="grid-2">
            <!-- Employee Info -->
            <div class="info-box">
                <div class="info-box-title">Employee Details</div>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">{{ $staff->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role / Position:</span>
                    <span class="info-value">{{ ucfirst($staff->role) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Branch:</span>
                    <span class="info-value">{{ $staff->branch ? $staff->branch->name : 'Global / Main Branch' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIC Number:</span>
                    <span class="info-value">{{ $staff->profile->nic ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Attendance & Work Summary -->
            <div class="info-box">
                <div class="info-box-title">Attendance & Work Breakdown</div>
                <div class="info-row">
                    <span class="info-label">Days Present:</span>
                    <span class="info-value text-green">{{ $payroll->present_days }} Days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Paid Leaves (With Salary):</span>
                    <span class="info-value text-green">{{ $payroll->paid_leave_days ?? 0 }} Days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Unpaid Leaves:</span>
                    <span class="info-value {{ ($payroll->leave_days - $payroll->approved_paid_leaves) > 0 ? 'text-red' : 'text-slate-600' }}">{{ max(0, $payroll->leave_days - $payroll->approved_paid_leaves) }} Days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Worked Hours:</span>
                    <span class="info-value">{{ $payroll->total_regular_hours_formatted }}</span>
                </div>

                @if($payroll->ot_hours > 0)
                <div class="info-row">
                    <span class="info-label">Overtime Hours:</span>
                    <span class="info-value text-indigo">{{ $payroll->ot_hours_formatted }}</span>
                </div>
                @endif
            </div>
        </div>
