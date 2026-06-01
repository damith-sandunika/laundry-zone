<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report {{ $startDate }} to {{ $endDate }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; }
        h1 { font-size: 20px; font-weight: bold; color: #1e40af; margin-bottom: 2px; }
        h2 { font-size: 13px; font-weight: bold; margin: 20px 0 8px; color: #374151; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; border-bottom: 2px solid #1e40af; padding-bottom: 12px; }
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; }
        .stat { background: #f3f4f6; border-radius: 6px; padding: 10px; text-align: center; }
        .stat .val { font-size: 16px; font-weight: bold; color: #1e40af; }
        .stat .lbl { font-size: 10px; color: #6b7280; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        th { background: #eff6ff; color: #1e40af; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; }
        tr:nth-child(even) td { background: #f9fafb; }
        .total-row td { font-weight: bold; border-top: 2px solid #d1d5db; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-amber { background: #fef3c7; color: #d97706; }
        .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">🖨 Print / Save PDF</button>
        <button onclick="window.history.back()" style="padding:8px 20px;background:#e5e7eb;color:#374151;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">← Back</button>
    </div>

    <div class="header">
        <div>
            <h1>{{ $branch->name ?? 'Smart Laundry' }}</h1>
            @if($branch && $branch->tagline)<p style="color:#6b7280;font-size:11px;">{{ $branch->tagline }}</p>@endif
            @if($branch && $branch->address)<p style="color:#6b7280;font-size:11px;">{{ $branch->address }}</p>@endif
        </div>
        <div style="text-align:right;">
            <p style="font-weight:bold;font-size:14px;color:#374151;">SALES REPORT</p>
            <p style="color:#6b7280;">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
            <p style="color:#9ca3af;font-size:10px;">Generated: {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stat-grid">
        <div class="stat"><div class="val">Rs.{{ number_format($totalSales, 2) }}</div><div class="lbl">Total Sales</div></div>
        <div class="stat"><div class="val">{{ $orders->count() }}</div><div class="lbl">Total Orders</div></div>
        <div class="stat"><div class="val">Rs.{{ number_format($totalSalaries, 2) }}</div><div class="lbl">Staff Salaries</div></div>
        <div class="stat"><div class="val">Rs.{{ number_format($totalExpenses, 2) }}</div><div class="lbl">Total Expenses</div></div>
    </div>

    <!-- Service Breakdown -->
    <h2>Service Breakdown</h2>
    <table>
        <tr><th>Service</th><th>Qty</th><th>Revenue</th></tr>
        @foreach($services as $s)
        <tr><td>{{ $s->name }}</td><td>{{ $s->total_qty }}</td><td>Rs.{{ number_format($s->total_revenue, 2) }}</td></tr>
        @endforeach
        <tr class="total-row"><td colspan="2">TOTAL</td><td>Rs.{{ number_format($totalSales, 2) }}</td></tr>
    </table>

    <!-- Order List -->
    <h2>Order List</h2>
    <table>
        <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Paid</th><th>Status</th><th>Date</th></tr>
        @foreach($orders as $o)
        <tr>
            <td style="font-family:monospace;">{{ $o->order_number }}</td>
            <td>{{ $o->customer->name ?? '—' }}</td>
            <td>Rs.{{ number_format($o->total, 2) }}</td>
            <td>Rs.{{ number_format($o->paid_amount, 2) }}</td>
            <td><span class="badge {{ $o->payment_status === 'paid' ? 'badge-green' : 'badge-amber' }}">{{ ucfirst($o->payment_status) }}</span></td>
            <td>{{ $o->created_at->format('M d') }}</td>
        </tr>
        @endforeach
    </table>

    <!-- Expenses -->
    @if($expenses->count())
    <h2>Expenses</h2>
    <table>
        <tr><th>Category</th><th>Description</th><th>Amount</th><th>Date</th></tr>
        @foreach($expenses as $e)
        <tr><td>{{ ucfirst($e->category) }}</td><td>{{ $e->description }}</td><td>Rs.{{ number_format($e->amount, 2) }}</td><td>{{ $e->expense_date }}</td></tr>
        @endforeach
        <tr class="total-row"><td colspan="2">TOTAL EXPENSES</td><td>Rs.{{ number_format($totalExpenses, 2) }}</td><td></td></tr>
    </table>
    @endif

    <!-- Net Summary -->
    <h2>Net Summary</h2>
    <table style="max-width:320px;">
        <tr><td>Total Revenue</td><td><b>Rs.{{ number_format($totalSales, 2) }}</b></td></tr>
        <tr><td>Total Expenses</td><td>Rs.{{ number_format($totalExpenses, 2) }}</td></tr>
        <tr class="total-row"><td>NET PROFIT</td><td>Rs.{{ number_format($totalSales - $totalExpenses, 2) }}</td></tr>
    </table>

    <div class="footer">{{ $branch->name ?? 'Smart Laundry' }} &bull; Report generated by Smart Laundry POS</div>
</body>
</html>
