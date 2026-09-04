<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shift Close Receipt</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { margin: 0; size: 80mm auto; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11px; 
            color: #000; 
            padding: 5mm; 
            width: 80mm; 
            max-width: 100%; 
            margin: 0 auto; 
            background: #fff;
        }
        h1 { font-size: 18px; text-align: center; font-weight: bold; margin-bottom: 2px; text-transform: uppercase; }
        .sub { text-align: center; font-size: 10px; margin-bottom: 8px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .divider-thick { border-top: 2px solid #000; margin: 8px 0; }
        
        .row { display: flex; justify-content: space-between; padding: 2px 0; gap: 4px; }
        .row span:first-child { flex: 1; word-break: break-word; }
        .row span:last-child { white-space: nowrap; text-align: right; }
        
        .section-head { font-weight: bold; margin: 8px 0 4px; font-size: 11px; text-transform: uppercase; text-decoration: underline; text-align: center; }
        
        .total-row { font-weight: bold; font-size: 12px; }
        
        .net-box { text-align: center; padding: 6px; border: 2px solid #000; margin: 8px 0; font-weight: bold; font-size: 13px; }
        
        table { width: 100%; font-size: 11px; margin: 6px 0; border-collapse: collapse; }
        td { padding: 2px 0; word-break: break-word; vertical-align: top; }
        td.right { text-align: right; white-space: nowrap; }
        th { text-align: left; border-bottom: 1px dashed #000; padding-bottom: 2px; }
        th.right { text-align: right; }
        
        .footer { text-align: center; font-size: 10px; margin-top: 12px; }
        
        @media print { 
            body { width: 80mm; padding: 2mm; margin: 0 auto; } 
            .no-print { display: none; } 
        }
    </style>
</head>
<body>
    @if(request('action') === 'print')
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    @endif

    <div class="no-print" style="margin-bottom:12px; text-align:center;">
        <button onclick="window.print()" style="padding:8px 16px;background:#000;color:#fff;border:none;border-radius:4px;cursor:pointer;font-family:monospace;font-size:12px;font-weight:bold;">PRINT RECEIPT</button>
        <button onclick="window.close()" style="padding:8px 16px;background:#f3f4f6;color:#000;border:1px solid #d1d5db;border-radius:4px;cursor:pointer;font-family:monospace;font-size:12px;margin-left:8px;">CLOSE</button>
    </div>

    <h1 style="margin-bottom: 2px;">{{ \App\Models\Branch::first()->name ?? 'SMART LAUNDRY' }}</h1>
    @if($branch && $branch->id !== (\App\Models\Branch::first()->id ?? 1))
        <h2 style="font-size: 14px; margin: 0 0 4px 0;">{{ $branch->name }}</h2>
    @endif
    <div class="sub">SHIFT CLOSING SUMMARY</div>
    <div class="sub">
        {{ $session ? \Carbon\Carbon::parse($session->opened_at)->format('M d, Y h:i A') : $startDate }}<br>
        TO<br>
        {{ now()->format('M d, Y h:i A') }}
    </div>

    <div class="divider"></div>
    <div class="row"><span>Cashier:</span><span>{{ $session && $session->openedBy ? $session->openedBy->name : 'N/A' }}</span></div>
    <div class="row"><span>Date:</span><span>{{ now()->format('M d, Y') }}</span></div>
    <div class="divider"></div>

    <div class="section-head">SALES REVENUE</div>
    @if(isset($totalDiscounts) && $totalDiscounts > 0)
    <div class="row"><span>Base Service Income</span><span>Rs.{{ number_format($totalSales + $totalDiscounts, 2) }}</span></div>
    <div class="row" style="color: #dc2626;"><span>Discounts Given</span><span>- Rs.{{ number_format($totalDiscounts, 2) }}</span></div>
    <div class="row"><span>Net Service Income</span><span>Rs.{{ number_format($totalSales, 2) }}</span></div>
    @else
    <div class="row"><span>Service Income</span><span>Rs.{{ number_format($totalSales, 2) }}</span></div>
    @endif
    <div class="row"><span>Package Income</span><span>Rs.{{ number_format($totalPackageIncome ?? 0, 2) }}</span></div>
    <div class="divider"></div>
    <div class="row total-row"><span>FULL GROSS SALE</span><span>Rs.{{ number_format($totalSales + ($totalPackageIncome ?? 0), 2) }}</span></div>
    
    @if(isset($totalBranchProfit) && isset($totalWorkshopProfit) && ($totalBranchProfit > 0 || $totalWorkshopProfit > 0))
    <div class="divider"></div>
    <div class="section-head">PROFIT SPLIT (SERVICE ONLY)</div>
    <div class="row"><span>Branch Share</span><span>Rs.{{ number_format($totalBranchProfit, 2) }}</span></div>
    <div class="row"><span>Workshop Payable</span><span>Rs.{{ number_format($totalWorkshopProfit, 2) }}</span></div>
    @endif
    
    <div class="section-head">EXPENSES OUT</div>
    @if($expenses->isEmpty())
        <div class="row"><span>No expenses</span><span>Rs.0.00</span></div>
    @else
        @foreach($expenses as $e)
        <div class="row">
            <span style="font-size: 10px;">{{ Str::limit($e->description ?: ucfirst($e->category), 20) }}</span>
            <span>Rs.{{ number_format($e->amount, 2) }}</span>
        </div>
        @endforeach
    @endif
    <div class="divider"></div>
    <div class="row total-row"><span>TOTAL EXPENSES</span><span>Rs.{{ number_format($totalExpenses, 2) }}</span></div>

    <div class="divider-thick"></div>

    <div class="section-head">NON-CASH COLLECTIONS</div>
    <div class="row"><span>Card Payments</span><span>Rs.{{ number_format($cardPaymentOnly ?? 0, 2) }}</span></div>
    <div class="row"><span>Bank Transfers</span><span>Rs.{{ number_format($bankPaymentOnly ?? 0, 2) }}</span></div>
    
    <div class="divider-thick"></div>

    <div class="section-head">CASH DRAWER BALANCE</div>
    <div class="row"><span>Opening Cash</span><span>Rs.{{ number_format($openingBalance, 2) }}</span></div>
    <div class="row"><span>(+) Cash In (All)</span><span>Rs.{{ number_format($cashPaymentOnly ?? 0, 2) }}</span></div>
    <div class="row"><span>(-) Expenses Out</span><span>Rs.{{ number_format($totalExpenses, 2) }}</span></div>
    
    <div class="net-box">
        DRAWER CASH: Rs.{{ number_format($openingBalance + ($cashPaymentOnly ?? 0) - $totalExpenses, 2) }}
    </div>
    
    <div class="divider-thick"></div>

    @if(isset($serviceBreakdown) && $serviceBreakdown->count() > 0)
    <div class="section-head">SERVICE BREAKDOWN</div>
    <table>
        <tr>
            <th>Service</th>
            <th class="right">Qty</th>
            <th class="right">Value</th>
        </tr>
        @foreach($serviceBreakdown as $sb)
        <tr>
            <td>{{ Str::limit($sb->name, 12) }}</td>
            <td class="right">{{ rtrim(rtrim(number_format($sb->total_qty, 2), '0'), '.') }}</td>
            <td class="right">Rs.{{ number_format($sb->total_revenue, 0) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="divider-thick"></div>
    @endif

    <div class="footer">
        <div>*** END OF REPORT ***</div>
        <div style="margin-top:4px;">Gen: {{ now()->format('Y-m-d H:i') }}</div>
        <div style="font-size:9px; margin-top:2px;">Software by DSRZone</div>
    </div>
</body>
</html>

