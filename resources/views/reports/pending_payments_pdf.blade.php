<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Payments Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { padding: 40px; color: #333; font-size: 11px; background: #fff; }

        /* Header / Letterhead */
        .letterhead { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .laundry-name { font-size: 28px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px; }
        .laundry-details { font-size: 11px; color: #64748b; line-height: 1.5; }

        .report-title { text-align: center; margin-bottom: 20px; }
        .report-title h1 { font-size: 16px; font-weight: bold; letter-spacing: 2px; color: #475569; margin-bottom: 5px; }
        .report-title p  { font-size: 12px; color: #94a3b8; font-weight: 500; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: auto; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 11px; }
        tr:nth-child(even) td { background: #fdfdfd; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }

        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <!-- Print / Close Buttons -->
    <div class="no-print" style="margin-bottom:20px; text-align: right;">
        <button onclick="window.print()" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">🖨 Print / Save PDF</button>
        <button onclick="window.close()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;margin-left:10px;">Close</button>
    </div>

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="laundry-name">{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</div>
        <div class="laundry-details">{{ $isSuperAdmin && !$branch ? 'Company Wide Report' : ($branch->name ?? 'Branch Report') }}</div>
    </div>

    <!-- Title & Period -->
    <div class="report-title">
        <h1>PENDING PAYMENTS REPORT</h1>
        <p style="margin-top:4px;">Period: <strong>{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</strong> (Created Dates)</p>
    </div>

    @php
        $grandTotalCredit = 0;
        $grandTotalOrders = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th style="width:90px;">Order No</th>
                <th>Order Date</th>
                <th>Customer</th>
                <th class="text-right">Total Price</th>
                <th class="text-right">Partial Paid</th>
                <th class="text-right" style="color: #b91c1c;">Pending Amt</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                @php
                    $pending = $order->total - ($order->paid_amount ?? 0);
                    $grandTotalCredit += $pending;
                    $grandTotalOrders++;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight:bold;color:#1e40af;">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    <td>
                        {{ $order->customer->name ?? 'Walk-in' }}<br>
                        <span style="font-size: 9px; color: #64748b;">{{ $order->customer->phone ?? '' }}</span>
                    </td>
                    <td class="text-right font-bold">Rs.{{ number_format($order->total, 2) }}</td>
                    <td class="text-right" style="color:#059669;">Rs.{{ number_format($order->paid_amount ?? 0, 2) }}</td>
                    <td class="text-right" style="font-weight:bold;color:#b91c1c;">Rs.{{ number_format($pending, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 40px; color:#64748b; font-weight:bold; font-size:14px; background:#f8fafc;">
                        No pending payments found in this date range.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($orders->count() > 0)
        <!-- Grand Total Table -->
        <table style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="font-size:14px; padding:15px; color:#1e3a8a;">GRAND TOTALS</th>
                    <th class="text-center" style="font-size:14px; padding:15px;">TOTAL PENDING ORDERS</th>
                    <th class="text-right" style="font-size:14px; padding:15px; color:#b91c1c;">TOTAL OUTSTANDING BALANCE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-weight:bold; font-size:14px; padding:15px; text-transform:uppercase;">Overall Summary</td>
                    <td class="text-center" style="font-weight:bold; font-size:16px; padding:15px; color:#2563eb;">{{ $grandTotalOrders }}</td>
                    <td class="text-right" style="font-weight:900; font-size:16px; padding:15px; color:#dc2626;">Rs.{{ number_format($grandTotalCredit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="footer">
        Generated on {{ now()->format('M d, Y h:i A') }} by {{ auth()->user()->name ?? 'System' }}<br>
        Powered by {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} Software
    </div>

    <!-- Auto Print Script -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
