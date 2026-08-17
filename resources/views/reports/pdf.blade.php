<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FULL FINANCIAL REPORT</title>
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
        
        /* Summary Grid */
        .summary-grid { display: table; width: 100%; margin-bottom: 30px; }
        .summary-col { display: table-cell; width: 33.33%; padding: 15px; border: 1px solid #e2e8f0; text-align: center; background: #f8fafc; }
        .summary-col.middle { border-left: none; border-right: none; }
        .summary-val { font-size: 18px; font-weight: bold; color: #0f172a; margin-top: 5px; }
        .summary-lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        .summary-sub { font-size: 9px; color: #94a3b8; margin-top: 5px; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: avoid; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 11px; }
        tr:nth-child(even) td { background: #fdfdfd; }
        .total-row td { font-weight: bold; color: #0f172a; border-top: 2px solid #cbd5e1; background: #f8fafc; }
        .profit-row td { font-size: 14px; color: #16a34a; font-weight: 800; border-top: 2px solid #16a34a; background: #f0fdf4; }
        
        /* Badges */
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-red { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-amber { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
        
        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:20px; text-align: right;">
        <button onclick="window.print()" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">🖨 Print / Save PDF</button>
        <button onclick="window.history.back()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;margin-left:10px;">← Back</button>
    </div>

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="laundry-name">{{ $branch->name ?? 'Smart Laundry' }}</div>
        <div class="laundry-details">
            @if($branch && $branch->tagline){{ $branch->tagline }}<br>@endif
            @if($branch && $branch->address){{ $branch->address }}<br>@endif
            @if($branch && $branch->phone)Phone: {{ $branch->phone }}@endif
        </div>
    </div>

    <!-- Title & Date -->
    <div class="report-title">
        <h1>FULL FINANCIAL REPORT</h1>
        <p>From: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} &nbsp;&nbsp; To: {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
    </div>

    <!-- Highlight Metrics -->
    <div class="summary-grid">
        <div class="summary-col">
            <div class="summary-lbl">Total Gross Income</div>
            <div class="summary-val" style="color: #2563eb;">Rs.{{ number_format($totalSales + $totalPackagePayments, 2) }}</div>
            <div class="summary-sub">Services + Packages</div>
        </div>
        <div class="summary-col middle">
            <div class="summary-lbl">Done Payments (Collected)</div>
            <div class="summary-val" style="color: #16a34a;">Rs.{{ number_format($totalCollected, 2) }}</div>
            <div class="summary-sub">Cash: Rs.{{ number_format($collectedCash, 2) }} | Card: Rs.{{ number_format($collectedCard, 2) }}</div>
        </div>
        <div class="summary-col">
            <div class="summary-lbl">Pending Collection</div>
            <div class="summary-val" style="color: #ea580c;">Rs.{{ number_format($totalPending + $totalCredit, 2) }}</div>
            <div class="summary-sub">Pickups & Credits</div>
        </div>
    </div>

    <!-- Net Financial Summary -->
    <h2>Net Profit Calculation</h2>
    <table>
        <tr>
            <td style="width: 70%;">Total Service Revenue ({{ $recentOrders->count() }} Orders)</td>
            <td class="text-right">Rs.{{ number_format($totalSales, 2) }}</td>
        </tr>
        <tr>
            <td>Total Package Sales Revenue</td>
            <td class="text-right">Rs.{{ number_format($totalPackagePayments, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL GROSS REVENUE</td>
            <td class="text-right">Rs.{{ number_format($totalSales + $totalPackagePayments, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #ef4444;">Less: Total Expenses & Salaries</td>
            <td class="text-right" style="color: #ef4444;">- Rs.{{ number_format($totalExpenses, 2) }}</td>
        </tr>
        <tr class="profit-row">
            <td>NET PROFIT (Calculated)</td>
            <td class="text-right">Rs.{{ number_format(($totalSales + $totalPackagePayments) - $totalExpenses, 2) }}</td>
        </tr>
    </table>

    <!-- Service Breakdown -->
    <h2>Service Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Service Type</th>
                <th class="text-center">Total Qty</th>
                <th class="text-right">Revenue Generated</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td class="text-center">{{ number_format($s->total_qty, 1) }}</td>
                <td class="text-right">Rs.{{ number_format($s->total_revenue, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color:#94a3b8;">No services logged.</td></tr>
            @endforelse
            @if($services->count() > 0)
            <tr class="total-row">
                <td colspan="2">TOTAL SERVICE REVENUE</td>
                <td class="text-right">Rs.{{ number_format($totalSales, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Packages Detail -->
    @if($customerPackages->count() > 0)
    <h2>Package Sales</h2>
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Package Name</th>
                <th>Issue Date</th>
                <th class="text-center">Status</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customerPackages as $cp)
            <tr>
                <td>{{ $cp->customer->name ?? '—' }}</td>
                <td>{{ $cp->package->name ?? '—' }}</td>
                <td>{{ $cp->start_date->format('M d, Y') }}</td>
                <td class="text-center">
                    <span class="badge {{ $cp->payment_status === 'paid' ? 'badge-green' : 'badge-amber' }}">{{ $cp->payment_status }}</span>
                </td>
                <td class="text-right">Rs.{{ number_format($cp->package->price ?? 0, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4">TOTAL PACKAGE REVENUE</td>
                <td class="text-right">Rs.{{ number_format($totalPackagePayments, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Expenses List -->
    @if($expenses->count() > 0)
    <h2>Expenses List</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-right">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $e)
            <tr>
                <td>{{ \Carbon\Carbon::parse($e->expense_date)->format('M d, Y') }}</td>
                <td>{{ ucfirst($e->category) }}</td>
                <td>{{ $e->description ?? '—' }}</td>
                <td class="text-right" style="color: #ef4444;">Rs.{{ number_format($e->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">TOTAL EXPENSES DEDUCTED</td>
                <td class="text-right">Rs.{{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Recent Orders (Shortened) -->
    @if($recentOrders->count() > 0)
    <h2>Recent Orders (Latest {{ $recentOrders->count() }})</h2>
    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Date</th>
                <th class="text-center">Status</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentOrders as $o)
            <tr>
                <td style="font-family: monospace; font-weight: bold; color: #475569;">{{ $o->order_number }}</td>
                <td>{{ $o->customer->name ?? '—' }}</td>
                <td>{{ $o->created_at->format('M d, Y') }}</td>
                <td class="text-center">
                    @if($o->payment_status === 'paid')
                        <span class="badge badge-green">PAID</span>
                    @elseif($o->payment_status === 'partial')
                        <span class="badge badge-amber">PARTIAL</span>
                    @else
                        <span class="badge badge-red">PENDING</span>
                    @endif
                </td>
                <td class="text-right">Rs.{{ number_format($o->paid_amount, 2) }}</td>
                <td class="text-right font-bold">Rs.{{ number_format($o->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        {{ $branch->name ?? 'Smart Laundry' }} &bull; Report automatically generated by POS System on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
