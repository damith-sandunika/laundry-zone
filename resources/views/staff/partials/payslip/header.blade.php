    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Print / Download PDF</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="payslip-card">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="company-title">{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</div>
                <div class="company-sub">Payroll & Salary Statement</div>
            </div>
            <div class="payslip-badge">
                <div class="badge-title">Official Payslip</div>
                <div class="badge-period">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</div>
                <div class="badge-ref">REF: PAY-{{ $payroll->year }}{{ sprintf('%02d', $payroll->month) }}-{{ sprintf('%03d', $staff->id) }}</div>
            </div>
        </div>

        <!-- Payment Verification Status Box -->
        @if($salaryPayment)
        <div class="payment-status-box paid">
            <div>
                <span class="status-badge paid">✅ SALARY PAID</span>
                <div style="margin-top:6px; font-size:12px; font-weight:600;">
                    Disbursed by: <strong>{{ $salaryPayment->createdBy->name ?? 'System Admin' }}</strong>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:12px; font-weight:700;">Paid Date & Time:</div>
                <div style="font-size:13px; font-weight:800; font-family:monospace;">{{ $salaryPayment->created_at->format('M d, Y — h:i A') }}</div>
            </div>
        </div>
        @else
        <div class="payment-status-box pending">
            <div>
                <span class="status-badge pending">⏳ PENDING PAYMENT</span>
                <div style="margin-top:6px; font-size:12px; font-weight:600;">
                    Salary calculated and ready for disbursement.
                </div>
            </div>
            <div style="text-align:right; font-size:12px; font-weight:700;">
                Status: Pending Approval
            </div>
        </div>
        @endif
