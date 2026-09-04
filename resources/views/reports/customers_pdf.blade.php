<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Sales Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { padding: 40px; color: #333; font-size: 11px; background: #fff; }
        
        /* Header / Letterhead */
        .letterhead { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .laundry-name { font-size: 28px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px; }
        .laundry-details { font-size: 11px; color: #64748b; line-height: 1.5; }
        
        .report-title { text-align: center; margin-bottom: 30px; }
        .report-title h1 { font-size: 16px; font-weight: bold; letter-spacing: 2px; color: #475569; margin-bottom: 5px; }
        .report-title p { font-size: 12px; color: #94a3b8; font-weight: 500; }
        
        h2 { font-size: 12px; font-weight: bold; color: #1e40af; margin: 25px 0 10px; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: auto; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 11px; }
        tr:nth-child(even) td { background: #fdfdfd; }
        .total-row td { font-weight: bold; color: #0f172a; border-top: 2px solid #cbd5e1; background: #f8fafc; font-size: 12px; }
        
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
        <h1>CUSTOMER SALES REPORT</h1>
        <p>From: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} &nbsp;&nbsp; To: {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">No</th>
                @if($isSuperAdmin && !$branchId)
                    <th>Branch</th>
                @endif
                <th>Customer Name</th>
                <th class="text-center">Order Count</th>
                <th class="text-right">Order Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $cust)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @if($isSuperAdmin && !$branchId)
                    <td>{{ $cust->branch_name ?? 'Unknown' }}</td>
                @endif
                <td>{{ $cust->customer_name ?? 'Walk-in / Unknown' }}</td>
                <td class="text-center">{{ $cust->order_count }}</td>
                <td class="text-right">Rs.{{ number_format($cust->total_revenue, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 5 : 4 }}" class="text-center" style="color:#94a3b8; padding: 20px;">No customers found in this date range.</td>
            </tr>
            @endforelse
        </tbody>
        @if($customers->count() > 0)
        <tfoot>
            <tr class="total-row">
                <td colspan="{{ ($isSuperAdmin && !$branchId) ? 3 : 2 }}" class="text-right">GRAND TOTAL:</td>
                <td class="text-center" style="color: #2563eb;">{{ $totalOrders }}</td>
                <td class="text-right" style="color: #16a34a;">Rs.{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        {{ ($isSuperAdmin && !$branchId) ? 'Company Wide' : ($branch->name ?? 'Smart Laundry') }} &bull; Report automatically generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>

