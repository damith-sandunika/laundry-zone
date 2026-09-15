<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { padding: 40px; color: #333; font-size: 11px; background: #fff; }
        
        /* Header / Letterhead */
        .letterhead { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .laundry-name { font-size: 28px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px; }
        .laundry-details { font-size: 11px; color: #64748b; line-height: 1.5; }
        
        .report-title { text-align: center; margin-bottom: 20px; }
        .report-title h1 { font-size: 16px; font-weight: bold; letter-spacing: 2px; color: #475569; margin-bottom: 5px; }
        .report-title p { font-size: 12px; color: #94a3b8; font-weight: 500; }
        
        /* Totals / Summary Grid */
        .summary-container {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .summary-box.highlight {
            border-bottom: 4px solid #1e293b;
            background: #fff;
        }
        .summary-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: 900;
        }
        
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
    <div class="no-print" style="margin-bottom:20px; text-align: right;">
        <button onclick="window.print()" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">🖨 Print / Save PDF</button>
        <button onclick="window.close()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;margin-left:10px;">Close</button>
    </div>

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="laundry-name">{{ ($isSuperAdmin && !$branchId) ? 'All Branches' : ($branch->name ?? 'Smart Laundry') }}</div>
        @if(!$isSuperAdmin || $branchId)
            <div class="laundry-details">
                @if($branch && $branch->tagline){{ $branch->tagline }}<br>@endif
                @if($branch && $branch->address){{ $branch->address }}<br>@endif
                @if($branch && $branch->phone)Phone: {{ $branch->phone }}@endif
            </div>
        @else
            <div class="laundry-details">Company Wide Report</div>
        @endif
    </div>

    <!-- Title & Date -->
    <div class="report-title">
        <h1>SALES REPORT (PAID ORDERS)</h1>
        <p>From: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &nbsp;&nbsp; To: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <!-- Summary / Totals -->
    <div class="summary-container">
        <div class="summary-box">
            <div class="summary-label">Cash Total</div>
            <div class="summary-value" style="color: #059669;">Rs.{{ number_format($totalCash, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Card Total</div>
            <div class="summary-value" style="color: #2563eb;">Rs.{{ number_format($totalCard, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Bank Transfer</div>
            <div class="summary-value" style="color: #9333ea;">Rs.{{ number_format($totalBank, 2) }}</div>
        </div>
        <div class="summary-box" style="border-bottom: 4px solid #ea580c; background:#fff7ed;">
            <div class="summary-label" style="color:#ea580c;">Credit Issued</div>
            <div class="summary-value" style="color: #ea580c;">Rs.{{ number_format($totalCredit, 2) }}</div>
        </div>
        <div class="summary-box highlight">
            <div class="summary-label">Total Sales</div>
            <div class="summary-value" style="color: #0f172a;">Rs.{{ number_format($totalSalesAmount, 2) }}</div>
        </div>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                @if($isSuperAdmin && !$branchId)
                    <th>Branch</th>
                @endif
                <th>Date</th>
                <th>Order ID</th>
                <th>Customer</th>
                <th class="text-center">QTY</th>
                <th>Payment</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $activity)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @if($isSuperAdmin && !$branchId)
                    <td>{{ $activity->order->branch->name ?? 'Unknown' }}</td>
                @endif
                <td>{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                <td style="font-weight: bold; color: #1e40af;">{{ $activity->order->order_number }}</td>
                <td>{{ $activity->order->customer->name ?? 'Walk-in' }}</td>
                <td class="text-center">{{ number_format($activity->total_qty, 1) }}</td>
                <td style="text-transform: uppercase;">
                    @if($activity->is_settlement)
                        <span style="background:#dbeafe;color:#1d4ed8;padding:3px 6px;border-radius:4px;font-size:10px;font-weight:bold;border:1px solid #93c5fd;">
                            SETTLED ({{ strtoupper($activity->payment_method) }})
                        </span>
                    @elseif($activity->payment_method === 'credit')
                        <span style="background:#ffedd5;color:#c2410c;padding:3px 6px;border-radius:4px;font-size:10px;font-weight:bold;border:1px solid #fdba74;">
                            CREDIT PAYMENT
                        </span>
                    @else
                        {{ $activity->payment_method ?? 'None' }}
                    @endif
                </td>
                <td class="text-right" style="font-weight: bold; color: #059669;">Rs.{{ number_format($activity->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 8 : 7 }}" class="text-center" style="color:#94a3b8; padding: 20px;">No sales found in this date range.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 5 : 4 }}" class="text-right" style="padding-top:12px; padding-bottom:12px; font-size:12px;">GRAND TOTAL:</td>
                <td class="text-center" style="padding-top:12px; padding-bottom:12px; font-size:12px; color: #0f172a;">{{ number_format($orders->sum('total_qty'), 1) }}</td>
                <td></td>
                <td class="text-right" style="padding-top:12px; padding-bottom:12px; font-size:12px; color: #059669;">Rs.{{ number_format($totalSalesAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ ($isSuperAdmin && !$branchId) ? 'Company Wide' : ($branch->name ?? 'Smart Laundry') }} &bull; Report automatically generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
