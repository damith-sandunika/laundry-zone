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
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: avoid; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 11px; }
        tr:nth-child(even) td { background: #fdfdfd; }
        .total-row td { font-weight: bold; color: #0f172a; border-top: 2px solid #cbd5e1; background: #f8fafc; }
        .profit-row td { font-size: 14px; color: #16a34a; font-weight: 800; border-top: 2px solid #16a34a; background: #f0fdf4; }
        .expense-row td { color: #dc2626; }
        
        /* Badges */
        .badge { padding: 3px 6px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-amber { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }

        /* Custom Summary Cards for PDF */
        .cards-container { width: 100%; margin-top: 30px; display: table; }
        .card { display: table-cell; width: 33.33%; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; text-align: center; }
        .card.middle { border-left: 10px solid #fff; border-right: 10px solid #fff; } /* Spacer */
        .card-title { font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
        .card-value { font-size: 18px; font-weight: 900; margin-bottom: 6px; }
        .card-sub { font-size: 10px; color: #94a3b8; font-weight: 600; }
        
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
        <div class="laundry-name">{{ $branch->name ?? 'Smart Laundry' }}</div>
        <div class="laundry-details">
            @if($branch && $branch->tagline){{ $branch->tagline }}<br>@endif
            @if($branch && $branch->address){{ $branch->address }}<br>@endif
            @if($branch && $branch->phone)Phone: {{ $branch->phone }}@endif
        </div>
    </div>

    <!-- Title & Date -->
    <div class="report-title">
        <h1>FULL FINANCIAL SUMMARY</h1>
        <p>From: {{ \Carbon\Carbon::parse($startDate)->format('F d, Y') }} &nbsp;&nbsp; To: {{ \Carbon\Carbon::parse($endDate)->format('F d, Y') }}</p>
    </div>

    <!-- 1. Service Breakdown -->
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

    <!-- 2. Packages Detail -->
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

    <!-- 3. Expenses List -->
    @if($expenses->count() > 0)
    <h2>Expenses Log</h2>
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
                <td class="text-right" style="color: #dc2626;">Rs.{{ number_format($e->amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row expense-row">
                <td colspan="3">TOTAL EXPENSES</td>
                <td class="text-right">Rs.{{ number_format($totalExpenses, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- 4. Financial Summary Calculation -->
    <h2 style="margin-top: 30px;">Net Profit Calculation</h2>
    <table>
        <tr>
            <td style="width: 70%;">Total Service Revenue</td>
            <td class="text-right">Rs.{{ number_format($totalSales, 2) }}</td>
        </tr>
        <tr>
            <td>Total Package Sales Revenue</td>
            <td class="text-right">Rs.{{ number_format($totalPackagePayments, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL GROSS INCOME</td>
            <td class="text-right" style="color:#2563eb;">Rs.{{ number_format($totalSales + $totalPackagePayments, 2) }}</td>
        </tr>
        <tr class="expense-row">
            <td>Less: Total Inventory Billed (Cost of materials used)</td>
            <td class="text-right">- Rs.{{ number_format($totalInventoryCost, 2) }}</td>
        </tr>
        <tr class="expense-row">
            <td>Less: Total Operating Expenses (including salaries)</td>
            <td class="text-right">- Rs.{{ number_format($totalExpenses, 2) }}</td>
        </tr>
        <tr class="profit-row">
            <td>NET PROFIT</td>
            <td class="text-right">Rs.{{ number_format(($totalSales + $totalPackagePayments) - ($totalExpenses + $totalInventoryCost), 2) }}</td>
        </tr>
    </table>

    <!-- 5. Status Cards (as requested) -->
    <div class="cards-container">
        <!-- Card 1 -->
        <div class="card" style="background: #f8fafc; border-color: #cbd5e1;">
            <div class="card-title">Total Gross Income</div>
            <div class="card-value" style="color: #0f172a;">Rs.{{ number_format($totalSales + $totalPackagePayments, 2) }}</div>
            <div class="card-sub">Services + Packages</div>
        </div>

        <div style="display:table-cell; width:2%;"></div> <!-- Spacer -->

        <!-- Card 2 -->
        <div class="card" style="background: #f0fdf4; border-color: #bbf7d0;">
            <div class="card-title">Done Payments (Collected)</div>
            <div class="card-value" style="color: #16a34a;">Rs.{{ number_format($totalCollected, 2) }}</div>
            <div class="card-sub">Cash: Rs.{{ number_format($collectedCash, 2) }} | Card: Rs.{{ number_format($collectedCard, 2) }}</div>
        </div>

        <div style="display:table-cell; width:2%;"></div> <!-- Spacer -->

        <!-- Card 3 -->
        <div class="card" style="background: #fff7ed; border-color: #fed7aa;">
            <div class="card-title">Pending Collection</div>
            <div class="card-value" style="color: #ea580c;">Rs.{{ number_format($totalPending + $totalCredit, 2) }}</div>
            <div class="card-sub">Pickups & Credits</div>
        </div>
    </div>

    <div class="footer">
        {{ $branch->name ?? 'Smart Laundry' }} &bull; Report automatically generated on {{ now()->format('M d, Y h:i A') }}
    </div>
</body>
</html>
