<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Monthly Summary - {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1e3a8a;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 13px;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #cbd5e1;
            font-weight: bold;
        }
        td {
            padding: 8px 6px;
            border: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .footer-row td {
            border-top: 2px solid #0f172a;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 18px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px;">
            🖨️ Print / Save PDF
        </button>
        <button onclick="window.close()" style="padding: 8px 18px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; margin-left: 8px;">
            Close
        </button>
    </div>

    <div class="header">
        <h1>365 Laundry — HR Monthly Performance & Payroll Summary</h1>
        <p>Period: <strong>{{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</strong> | Branch: <strong>{{ $currentBranch ? $currentBranch->name : 'All Branches' }}</strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Branch</th>
                <th class="text-center">Present</th>
                <th class="text-center">Leaves</th>
                <th class="text-center">Total Hrs</th>
                <th class="text-center">Day Hrs</th>
                <th class="text-center">OT Hrs</th>
                <th class="text-right">Basic (Adjusted)</th>
                <th class="text-right">OT Pay</th>
                <th class="text-right">Net Adjustments</th>
                <th class="text-right">Total Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffSummary as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $s['name'] }}</strong><br><span style="color:#64748b; font-size:10px;">{{ $s['role'] }}</span></td>
                    <td>{{ $s['branch'] }}</td>
                    <td class="text-center">{{ $s['present_days'] }} d</td>
                    <td class="text-center">{{ $s['leave_days'] }} d</td>
                    <td class="text-center">{{ $s['total_worked_hours'] }} h</td>
                    <td class="text-center">{{ $s['day_hours'] }} h</td>
                    <td class="text-center">{{ $s['ot_hours'] }} h</td>
                    <td class="text-right">Rs. {{ number_format($s['adjusted_basic_salary'], 2) }}</td>
                    <td class="text-right" style="color: #2563eb;">Rs. {{ number_format($s['ot_amount'], 2) }}</td>
                    <td class="text-right" style="color: {{ $s['net_adjustments'] >= 0 ? '#16a34a' : '#dc2626' }};">
                        {{ $s['net_adjustments'] >= 0 ? '+' : '' }} Rs. {{ number_format($s['net_adjustments'], 2) }}
                    </td>
                    <td class="text-right"><strong>Rs. {{ number_format($s['net_salary'], 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="3" class="text-right">GRAND TOTALS:</td>
                <td class="text-center">{{ $staffSummary->sum('present_days') }} d</td>
                <td class="text-center">{{ $staffSummary->sum('leave_days') }} d</td>
                <td class="text-center">{{ $staffSummary->sum('total_worked_hours') }} h</td>
                <td class="text-center">{{ $staffSummary->sum('day_hours') }} h</td>
                <td class="text-center">{{ $staffSummary->sum('ot_hours') }} h</td>
                <td class="text-right">Rs. {{ number_format($staffSummary->sum('adjusted_basic_salary'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($staffSummary->sum('ot_amount'), 2) }}</td>
                <td class="text-right">Rs. {{ number_format($staffSummary->sum('net_adjustments'), 2) }}</td>
                <td class="text-right" style="color: #166534;">Rs. {{ number_format($staffSummary->sum('net_salary'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px; font-size: 11px; color: #94a3b8; text-align: center;">
        Report Generated on {{ date('d M Y, h:i A') }} — SmartLaundry HR Management System
    </div>

</body>
</html>
