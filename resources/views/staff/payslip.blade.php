<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $staff->name }} ({{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }})</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            padding: 24px;
            font-size: 13px;
            line-height: 1.5;
        }
        .payslip-card {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            padding: 36px;
            position: relative;
        }
        .no-print {
            max-width: 850px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            padding: 10px 20px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-close {
            padding: 10px 20px;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .company-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .company-sub {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
        }
        .payslip-badge {
            text-align: right;
        }
        .badge-title {
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-period {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }
        .badge-ref {
            font-size: 11px;
            color: #94a3b8;
            font-family: monospace;
            margin-top: 4px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .info-box {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            padding: 16px;
        }
        .info-box-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 10px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }

        /* Payment Audit Status Box */
        .payment-status-box {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-status-box.paid {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .payment-status-box.pending {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            color: #92400e;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-badge.paid {
            background-color: #166534;
            color: #ffffff;
        }
        .status-badge.pending {
            background-color: #d97706;
            color: #ffffff;
        }

        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .statement-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .statement-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-weight: 700;
        }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
        .text-amber { color: #d97706; }
        .text-indigo { color: #4f46e5; }
        .text-purple { color: #9333ea; }
        
        .net-salary-box {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px -5px rgba(30, 58, 138, 0.3);
        }
        .net-label {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #93c5fd;
        }
        .net-sub {
            font-size: 11px;
            color: #cbd5e1;
            margin-top: 2px;
        }
        .net-amount {
            font-size: 28px;
            font-weight: 900;
            font-family: 'SFMono-Regular', Consolas, monospace;
            letter-spacing: -0.5px;
        }

        /* Worked Days Section */
        .worked-days-box {
            margin-bottom: 30px;
        }
        .worked-days-title {
            font-size: 12px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .days-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            font-size: 11px;
        }
        .days-table th {
            background: #f8fafc;
            padding: 8px 12px;
            text-align: left;
            color: #64748b;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
        }
        .days-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .sig-block {
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #cbd5e1;
            margin: 40px auto 8px;
            width: 70%;
        }
        .sig-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .footer-watermark {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .payslip-card {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Print / Download PDF</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>

    <div class="payslip-card">
        <!-- Header -->
        <div class="header">
            <div>
                <div class="company-title">{{ config('app.name', '365 Laundry') }}</div>
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

        <!-- Detailed Statement Table -->
        <table class="statement-table">
            <thead>
                <tr>
                    <th>Earnings & Deductions Description</th>
                    <th>Rate / Basis</th>
                    <th class="text-right">Earnings (LKR)</th>
                    <th class="text-right">Deductions (LKR)</th>
                </tr>
            </thead>
            <tbody>
                <!-- Regular Pay -->
                <tr>
                    <td><strong>Regular Worked Hours Pay</strong></td>
                    <td>{{ $payroll->total_regular_hours_for_pay_formatted }} @ Rs. {{ number_format($payroll->hourly_rate, 2) }}/hr</td>
                    <td class="text-right font-mono text-blue-600">+ {{ number_format($payroll->base_pay, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>

                <!-- Overtime Pay -->
                @if($payroll->ot_amount > 0)
                <tr>
                    <td class="text-indigo">Overtime (OT) Earnings</td>
                    <td>{{ $payroll->ot_hours_formatted }} @ Rs. {{ number_format($staff->profile->ot_rate_per_hour ?? 0, 2) }}/hr</td>
                    <td class="text-right font-mono text-indigo">+ {{ number_format($payroll->ot_amount, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>
                @endif

                <!-- Allowances -->
                @if($payroll->allowances > 0)
                <tr>
                    <td class="text-green">Allowances & Performance Bonuses</td>
                    <td>Ad-hoc Allowances</td>
                    <td class="text-right font-mono text-green">+ {{ number_format($payroll->allowances, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>
                @endif

                <!-- Manual Deductions -->
                @if($payroll->manual_deductions > 0)
                <tr>
                    <td class="text-red">Manual Salary Deductions / Fines</td>
                    <td>Ad-hoc Deductions</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono text-red">- {{ number_format($payroll->manual_deductions, 2) }}</td>
                </tr>
                @endif

                <!-- Full Total Salary Row -->
                <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                    <td colspan="2" style="font-size: 13px; color: #0f172a;"><strong>FULL TOTAL SALARY (Gross Earnings)</strong></td>
                    <td class="text-right font-mono" style="color: #1e40af; font-size: 14px;">Rs. {{ number_format($payroll->gross_salary, 2) }}</td>
                    <td class="text-right font-mono">-</td>
                </tr>

                <!-- Salary Advances -->
                @if($payroll->advances > 0)
                <tr>
                    <td class="text-purple">Salary Advances Collected</td>
                    <td>Month Advances Deducted</td>
                    <td class="text-right font-mono">-</td>
                    <td class="text-right font-mono text-purple">- {{ number_format($payroll->advances, 2) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Net Payable Salary Box -->
        <div class="net-salary-box">
            <div>
                <div class="net-label">Payable Balance</div>
                <div class="net-sub">Net Remaining Payable Salary (After Advances) for {{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</div>
            </div>
            <div class="net-amount">Rs. {{ number_format($payroll->net_salary, 2) }}</div>
        </div>

        <!-- Worked Days Breakdown Log -->
        @if(isset($attendances) && $attendances->count() > 0)
        <div class="worked-days-box">
            <div class="worked-days-title">Daily Attendance & Work Log</div>
            <table class="days-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th class="text-right">Worked Hours</th>
                        <th class="text-right">OT Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $att)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($att->date)->format('l') }}</td>
                        <td>
                            @if($att->status === 'present')
                                <span style="color:#16a34a; font-weight:700;">🟢 Present</span>
                            @elseif($att->status === 'paid_leave')
                                <span style="color:#059669; font-weight:700;">🟢 Paid Leave</span>
                            @elseif($att->status === 'leave')
                                <span style="color:#ea580c; font-weight:700;">🟠 Unpaid Leave</span>
                            @else
                                <span style="color:#64748b;">{{ ucfirst($att->status) }}</span>
                            @endif
                        </td>
                        <td>{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('h:i A') : '-' }}</td>
                        <td>{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('h:i A') : '-' }}</td>
                        <td class="text-right font-mono">
                            @if($att->normal_minutes > 0)
                                {{ \App\Services\PayrollCalculator::formatHoursAndMinutes($att->normal_minutes, true) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-right font-mono text-indigo">
                            @if($att->ot_minutes > 0)
                                + {{ \App\Services\PayrollCalculator::formatHoursAndMinutes($att->ot_minutes, true) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f8fafc; font-weight: 800; border-top: 2px solid #e2e8f0;">
                        <td colspan="5" class="text-right" style="font-size: 11px; text-transform: uppercase; color: #475569; padding: 10px 12px;">
                            <strong>Month Total Hours:</strong>
                        </td>
                        <td class="text-right font-mono" style="color: #1e3a8a; font-size: 12px; padding: 10px 12px;">
                            <strong>{{ $payroll->total_regular_hours_formatted }}</strong>
                        </td>
                        <td class="text-right font-mono" style="color: #4f46e5; font-size: 12px; padding: 10px 12px;">
                            <strong>+ {{ $payroll->ot_hours_formatted }}</strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-title">Prepared & Authorized By</div>
            </div>
            <div class="sig-block">
                <div class="sig-line"></div>
                <div class="sig-title">Employee Signature & Date</div>
            </div>
        </div>

        <div class="footer-watermark">
            Computer Generated Document — Verified by 365laundry.lk HR & Payroll System
        </div>
    </div>

</body>
</html>
