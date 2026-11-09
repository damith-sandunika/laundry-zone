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
        @if(isset($salaryPayments) && $salaryPayments->count() > 0)
            @php
                $isFullyPaid = ($payroll->net_salary <= 0);
            @endphp
            <div class="payment-status-box {{ $isFullyPaid ? 'paid' : 'partial' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed {{ $isFullyPaid ? '#86efac' : '#93c5fd' }}; padding-bottom: 10px; margin-bottom: 10px;">
                    <div>
                        @if($isFullyPaid)
                            <span class="status-badge paid">✅ SALARY FULLY PAID</span>
                        @else
                            <span class="status-badge partial">⏳ PARTIALLY PAID ({{ $salaryPayments->count() }} Payouts)</span>
                        @endif
                        <div style="margin-top:6px; font-size:12px; font-weight:700;">
                            Total Disbursed: <span style="font-family:monospace; font-size:13px; font-weight:900;">Rs. {{ number_format($payroll->already_paid ?? 0, 2) }}</span>
                            @if(!$isFullyPaid)
                                &nbsp;|&nbsp; <span style="color:#b91c1c;">Remaining Balance: Rs. {{ number_format($payroll->net_salary, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Payment Status</div>
                        <div style="font-size:13px; font-weight:800; font-family:monospace;">{{ $isFullyPaid ? 'Settled in Full' : 'Partial Payouts Recorded' }}</div>
                    </div>
                </div>

                <!-- Multiple Payout List -->
                <div style="font-size:11.5px; display:flex; flex-direction:column; gap:6px;">
                    <div style="font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:0.3px; opacity:0.85;">
                        Disbursement History ({{ $salaryPayments->count() }} {{ $salaryPayments->count() === 1 ? 'Transaction' : 'Installments' }}):
                    </div>
                    @foreach($salaryPayments as $idx => $sp)
                        <div style="display:flex; justify-content:space-between; align-items:center; background: rgba(255,255,255,0.65); padding: 6px 12px; border-radius: 6px;">
                            <div>
                                <strong>#{{ $salaryPayments->count() - $idx }}. Rs. {{ number_format($sp->amount, 2) }}</strong>
                                <span style="opacity:0.75; margin-left:6px;">— Disbursed by {{ $sp->createdBy->name ?? 'System Admin' }}</span>
                            </div>
                            <div style="font-family:monospace; font-weight:700; font-size:11px;">
                                {{ $sp->created_at ? $sp->created_at->format('M d, Y — h:i A') : ($sp->expense_date ? \Carbon\Carbon::parse($sp->expense_date)->format('M d, Y') : '-') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
        <div class="payment-status-box pending" style="display: flex; justify-content: space-between; align-items: center;">
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
