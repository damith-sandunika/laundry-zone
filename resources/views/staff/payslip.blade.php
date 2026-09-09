<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - {{ $staff->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 28px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }
        .info-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 5px 0;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            width: 150px;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .salary-table th {
            background-color: #f3f4f6;
            color: #374151;
            text-align: left;
            padding: 12px;
            font-size: 14px;
            border-bottom: 2px solid #d1d5db;
        }
        .salary-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        .amount-col {
            text-align: right;
            font-family: monospace;
            font-size: 15px;
        }
        .total-row {
            background-color: #f0fdf4;
        }
        .total-row td {
            font-weight: bold;
            font-size: 16px;
            color: #166534;
            border-bottom: 2px solid #166534;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .signatures {
            margin-top: 60px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            width: 50%;
        }
        .sig-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto 5px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom:20px; text-align: right;">
        <button onclick="window.print()" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">
            🖨️ Print / Save PDF
        </button>
        <button onclick="window.close()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;margin-left:10px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>{{ config('app.name', 'Rapid Laundromat') }}</h1>
        <p>Payslip for the month of <strong>{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}</strong></p>
    </div>

    <table class="info-section">
        <tr>
            <td width="50%">
                <table class="info-table">
                    <tr><td class="info-label">Employee Name:</td><td>{{ $staff->name }}</td></tr>
                    <tr><td class="info-label">Role:</td><td>{{ ucfirst($staff->role) }}</td></tr>
                    <tr><td class="info-label">Branch:</td><td>{{ $staff->branch ? $staff->branch->name : 'Main Branch' }}</td></tr>
                </table>
            </td>
            <td width="50%">
                <table class="info-table">
                    <tr><td class="info-label">Payslip Date:</td><td>{{ date('d/m/Y') }}</td></tr>
                    <tr><td class="info-label">Working Hours:</td><td>{{ $staff->profile->standard_working_hours ?? 0 }} Hrs/Day</td></tr>
                    <tr><td class="info-label">OT Rate:</td><td>Rs. {{ number_format($staff->profile->ot_rate_per_hour ?? 0, 2) }}/Hr</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="salary-table">
        <thead>
            <tr>
                <th>Earnings & Deductions</th>
                <th class="amount-col">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td class="amount-col">{{ number_format($staff->profile->basic_salary ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime Earnings ({{ floor($payroll->ot_minutes / 60) }}h {{ $payroll->ot_minutes % 60 }}m)</td>
                <td class="amount-col" style="color: #2563eb;">+ {{ number_format($payroll->ot_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Salary Advances / Deductions</td>
                <td class="amount-col" style="color: #dc2626;">- {{ number_format($payroll->advances, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>NET PAY</td>
                <td class="amount-col">Rs. {{ number_format($payroll->net_salary, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line"></div>
                Employer Signature
            </td>
            <td>
                <div class="sig-line"></div>
                Employee Signature
            </td>
        </tr>
    </table>

    <div class="footer">
        This is a computer generated document and does not require a physical signature unless printed.
    </div>

</body>
</html>


