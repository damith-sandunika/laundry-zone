<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shift Close Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { margin: 0; size: 80mm auto; }
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #1f2937; padding: 4mm 3mm; width: 74mm; max-width: 100%; margin: 0 auto; }
        h1 { font-size: 16px; text-align: center; font-weight: bold; margin-bottom: 2px; }
        .sub { text-align: center; color: #6b7280; font-size: 10px; margin-bottom: 12px; }
        .divider { border-top: 2px dashed #374151; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; padding: 2px 0; gap: 4px; }
        .row span:first-child { flex: 1; word-break: break-word; }
        .row span:last-child { white-space: nowrap; text-align: right; }
        .section-head { font-weight: bold; background: #f3f4f6; padding: 4px 8px; margin: 8px 0 4px; border-left: 3px solid #2563eb; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .total { font-weight: bold; font-size: 12px; border-top: 2px solid #000; padding-top: 4px; margin-top: 4px; }
        .net { font-size: 14px; font-weight: bold; text-align: center; padding: 8px; border: 2px solid #000; margin: 10px 0; }
        .footer { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 16px; }
        table { width: 100%; font-size: 10px; margin: 6px 0; border-collapse: collapse; }
        td { padding: 2px 4px; word-break: break-word; }
        td:last-child { text-align: right; white-space: nowrap; }
        @media print { body { width: 74mm; padding: 2mm 3mm; margin: 0 auto; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:16px; text-align:center;">
        <button onclick="window.print()" style="padding:8px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:13px;">🖨 Print Report</button>
        <button onclick="window.history.back()" style="padding:8px 20px;background:#e5e7eb;color:#374151;border:none;border-radius:4px;cursor:pointer;font-size:13px;margin-left:8px;">← Back</button>
    </div>

    <h1>{{ $branch->name ?? 'Smart Laundry' }}</h1>
    <div class="sub">SHIFT CLOSING REPORT</div>
    <div class="sub">
        {{ $session ? \Carbon\Carbon::parse($session->opened_at)->format('M d, Y h:i A') : $startDate }}
        — {{ now()->format('M d, Y h:i A') }}
    </div>

    <div class="divider"></div>

    <!-- Shift Info -->
    <div class="section-head">Shift Information</div>
    <div class="row"><span>Opened By</span><span>{{ $session && $session->openedBy ? $session->openedBy->name : 'N/A' }}</span></div>
    <div class="row"><span>Opening Cash</span><span>Rs.{{ number_format($openingBalance, 2) }}</span></div>
    <div class="row"><span>Date</span><span>{{ $startDate }}</span></div>

    <div class="divider"></div>

    <!-- Sales -->
    <div class="section-head">Sales Summary ({{ $orders->count() }} Orders)</div>
    <table>
        @foreach($orders as $o)
        <tr>
            <td style="font-size:10px; color:#6b7280;">{{ $o->order_number }}</td>
            <td>{{ $o->customer->name ?? '—' }}</td>
            <td>Rs.{{ number_format($o->paid_amount, 2) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="row total"><span>Total Cash In</span><span>Rs.{{ number_format($totalCashIn, 2) }}</span></div>

    <div class="divider"></div>

    <!-- Expenses -->
    <div class="section-head">Expenses ({{ $expenses->count() }})</div>
    @if($expenses->isEmpty())
        <div style="color:#9ca3af; font-size:11px; padding:4px;">No expenses recorded. — Rs.0.00</div>
    @else
    <table>
        @foreach($expenses as $e)
        <tr>
            <td style="font-size:10px; color:#6b7280;">{{ ucfirst($e->category) }}</td>
            <td>{{ $e->description }}</td>
            <td>Rs.{{ number_format($e->amount, 2) }}</td>
        </tr>
        @endforeach
    </table>
    @endif
    <div class="row total"><span>Total Expenses Out</span><span>Rs.{{ number_format($totalExpenses, 2) }}</span></div>

    <div class="divider"></div>

    <!-- Final Balance -->
    <div class="section-head">Cash Drawer Balance</div>
    <div class="row"><span>Opening Balance</span><span>Rs.{{ number_format($openingBalance, 2) }}</span></div>
    <div class="row"><span>+ Cash Collected</span><span>Rs.{{ number_format($totalCashIn, 2) }}</span></div>
    <div class="row"><span>- Expenses Paid</span><span>Rs.{{ number_format($totalExpenses, 2) }}</span></div>

    <div class="net">
        CLOSING BALANCE: Rs.{{ number_format($closingBalance, 2) }}
    </div>

    @if($totalSales > $totalCashIn)
    <div style="text-align:center; font-size:11px; color:#d97706; margin-bottom:8px;">
        ⚠️ Pending Collections: Rs.{{ number_format($totalSales - $totalCashIn, 2) }} (Pay on Pickup)
    </div>
    @endif

    @if(isset($inventoryItems) && $inventoryItems->count() > 0)
    <div class="divider"></div>
    <div class="section-head">📦 Stock Remaining (End of Day)</div>
    <table>
        @foreach($inventoryItems as $inv)
        <tr>
            <td>{{ $inv->name }}</td>
            <td style="text-align:right; font-weight:bold;">{{ $inv->current_stock }} {{ $inv->unit }}
                @if($inv->current_stock <= $inv->min_stock)
                    ⚠️
                @endif
            </td>
        </tr>
        @endforeach
    </table>
    @endif

    <div class="footer">
        <div style="font-weight:bold; color:#1f2937; margin-bottom:4px;">Thank you! Come again</div>
        <div style="margin-bottom:4px;">Generated: {{ now()->format('M d, Y h:i A') }}</div>
        <div>Software by DSRZone</div>
    </div>
</body>
</html>
