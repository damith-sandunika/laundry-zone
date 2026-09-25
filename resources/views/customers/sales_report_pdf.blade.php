<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Sales Report — {{ $customer->name }} — {{ $label }}</title>
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

        /* Summary Grid */
        .summary-container { display: table; width: 100%; margin-bottom: 25px; }
        .summary-box {
            display: table-cell; width: 25%;
            padding: 15px; text-align: center;
            border: 1px solid #e2e8f0; background: #f8fafc;
        }
        .summary-box.highlight { border-bottom: 4px solid #1e293b; background: #fff; }
        .summary-label { font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .summary-value { font-size: 16px; font-weight: 900; }

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
        <div class="laundry-details">Customer Sales Report</div>
    </div>

    <!-- Title & Period -->
    <div class="report-title">
        <h1>CUSTOMER SALES REPORT</h1>
        <p>{{ $customer->name }}  &bull;  {{ $customer->phone }}{{ $customer->address ? '  &bull;  ' . $customer->address : '' }}</p>
        <p style="margin-top:4px;">Period: <strong>{{ $label }}</strong></p>
    </div>

    <!-- Summary Boxes -->
    <div class="summary-container">
        <div class="summary-box">
            <div class="summary-label">Total Orders</div>
            <div class="summary-value" style="color:#2563eb;">{{ $orders->count() }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Total Qty</div>
            <div class="summary-value" style="color:#0f172a;">{{ number_format($totalQty, 0) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Total Paid</div>
            <div class="summary-value" style="color:#059669;">Rs.{{ number_format($orders->sum('paid_amount'), 2) }}</div>
        </div>
        <div class="summary-box highlight">
            <div class="summary-label">Total Bill</div>
            <div class="summary-value" style="color:#0f172a;">Rs.{{ number_format($totalBill, 2) }}</div>
        </div>
    </div>

    <!-- Orders Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:40px;">No</th>
                <th>Date</th>
                <th>Order ID</th>
                <th class="text-center">QTY</th>
                <th class="text-right">Bill</th>
                <th class="text-right">Paid</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $ord)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $ord->created_at->format('Y-m-d H:i') }}</td>
                <td style="font-weight:bold;color:#1e40af;">{{ $ord->order_number }}</td>
                <td class="text-center">{{ number_format($ord->items->sum('quantity'), 1) }}</td>
                <td class="text-right" style="font-weight:bold;">
                    {{ $ord->total == 0 && $ord->isPackageOrder() ? 'Package' : 'Rs.'.number_format($ord->total, 2) }}
                </td>
                <td class="text-right" style="color:#059669;font-weight:bold;">Rs.{{ number_format($ord->paid_amount, 2) }}</td>
                <td class="text-center">
                    @php
                        $sc = in_array($ord->status, ['delivered','completed'])
                            ? ['#d1fae5','#065f46']
                            : ($ord->status === 'received' ? ['#fef3c7','#92400e'] : ['#f1f5f9','#475569']);
                    @endphp
                    <span style="background:{{ $sc[0] }};color:{{ $sc[1] }};padding:3px 7px;border-radius:4px;font-size:10px;font-weight:bold;">
                        {{ ucfirst(str_replace('_',' ',$ord->status)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color:#94a3b8;padding:20px;">No orders found for this period.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;font-weight:bold;border-top:2px solid #cbd5e1;">
                <td colspan="3" class="text-right" style="padding-top:12px;padding-bottom:12px;font-size:12px;">GRAND TOTAL:</td>
                <td class="text-center" style="padding-top:12px;padding-bottom:12px;font-size:12px;color:#0f172a;">{{ number_format($totalQty, 1) }}</td>
                <td class="text-right" style="padding-top:12px;padding-bottom:12px;font-size:12px;color:#0f172a;">Rs.{{ number_format($totalBill, 2) }}</td>
                <td class="text-right" style="padding-top:12px;padding-bottom:12px;font-size:12px;color:#059669;">Rs.{{ number_format($orders->sum('paid_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} &bull; Customer Sales Report &bull; {{ $customer->name }} &bull; {{ $label }} &bull; Generated on {{ now()->format('M d, Y h:i A') }}
    </div>

</body>
</html>
