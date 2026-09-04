<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        @page { size: A4; margin: 0; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #e2e8f0; 
            -webkit-print-color-adjust: exact !important; 
            print-color-adjust: exact !important; 
            padding: 40px; 
            display: flex;
            justify-content: center;
        }
        .invoice-box { 
            background: white; 
            width: 210mm; 
            min-height: 297mm; 
            padding: 50px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }
        /* Stylish Header Shape */
        .header-shape {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 12px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; margin-top: 20px; }
        .logo-section h1 { font-size: 28px; font-weight: 900; color: #0f172a; margin-bottom: 4px; letter-spacing: -0.03em; }
        .logo-section p { font-size: 13px; color: #64748b; font-weight: 500; line-height: 1.5; }
        
        .invoice-title { text-align: right; }
        .invoice-title h2 { font-size: 36px; font-weight: 900; color: #e2e8f0; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
        .invoice-title p { font-size: 14px; font-weight: 700; color: #3b82f6; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-box { background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .info-box h3 { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 12px; }
        .info-box p { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .info-box span { font-size: 13px; color: #64748b; font-weight: 500; display: block; line-height: 1.5; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #0f172a; color: white; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 14px 16px; text-align: left; letter-spacing: 0.05em; }
        .items-table th.right { text-align: right; }
        .items-table th.center { text-align: center; }
        .items-table th:first-child { border-radius: 8px 0 0 8px; }
        .items-table th:last-child { border-radius: 0 8px 8px 0; }
        
        .items-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; font-weight: 600; }
        .items-table td.right { text-align: right; }
        .items-table td.center { text-align: center; }
        .items-table tr:last-child td { border-bottom: none; }
        
        .totals-section { display: flex; justify-content: flex-end; }
        .totals-box { width: 320px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #475569; font-weight: 600; }
        .total-row.grand-total { border-top: 2px solid #e2e8f0; margin-top: 8px; padding-top: 12px; font-size: 18px; color: #0f172a; font-weight: 900; }
        .total-row.paid { color: #10b981; }
        .total-row.balance { background: #eff6ff; padding: 12px; border-radius: 8px; color: #2563eb; font-weight: 800; margin-top: 8px; }

        .footer { position: absolute; bottom: 50px; left: 50px; right: 50px; display: flex; justify-content: space-between; align-items: center; border-top: 1.5px solid #f1f5f9; padding-top: 20px; }
        .footer p { font-size: 12px; color: #94a3b8; font-weight: 500; }
        .badge { background: #10b981; color: white; padding: 6px 12px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; }

        @media print {
            body { background: white; padding: 0; display: block; }
            .invoice-box { box-shadow: none; width: 100%; min-height: 100vh; padding: 15mm; border-radius: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="background: #3b82f6; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.3);">🖨 Print / Save as PDF</button>
        <button onclick="window.close()" style="background: white; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Close</button>
    </div>

    <div class="invoice-box">
        <div class="header-shape"></div>
        
        <div class="header">
            <div class="logo-section">
                <h1>{{ \App\Models\Setting::get('company_name', 'RAPID Laundromat') }}</h1>
                <p>{{ $order->branch->name ?? 'Main Branch' }}</p>
                <p>{{ $order->branch->phone ?? '011 234 5678' }}</p>
                <p>{{ $order->branch->address ?? 'Colombo, Sri Lanka' }}</p>
            </div>
            <div class="invoice-title">
                <h2>INVOICE</h2>
                <p>#{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Bill To</h3>
                <p>{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                <span>{{ $order->customer->phone ?? 'No Phone' }}</span>
                <span>{{ $order->customer->email ?? '' }}</span>
            </div>
            <div class="info-box">
                <h3>Order Details</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color:#0f172a; font-weight:700;">Date:</span>
                    <span style="color:#475569;">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color:#0f172a; font-weight:700;">Status:</span>
                    <span style="color:#475569; text-transform: capitalize;">{{ str_replace('_', ' ', $order->status) }}</span>
                </div>
                @if($order->due_date)
                <div style="display: flex; justify-content: space-between;">
                    <span style="color:#0f172a; font-weight:700;">Ready By:</span>
                    <span style="color:#475569;">{{ \Carbon\Carbon::parse($order->due_date)->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item / Service</th>
                    <th class="center">Qty</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->serviceType->name ?? $item->item_description }}
                        @if($item->turnaroundType && $item->turnaroundType->name !== 'Standard')
                            <small style="color:#64748b; margin-left: 5px;">[{{ $item->turnaroundType->name }}]</small>
                        @endif
                        @if($item->extras->count() > 0)
                            @foreach($item->extras as $e)
                            <br><small style="color:#64748b;">+ {{ $e->extra->name }} (x{{ $e->quantity }} @ Rs.{{ number_format($e->unit_price, 2) }})</small>
                            @endforeach
                        @endif
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">Rs.{{ number_format(($item->total_price - $item->extras->sum('total_price')) / $item->quantity, 2) }}</td>
                    <td class="right" style="color: #0f172a; font-weight: 800;">Rs.{{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rs.{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->delivery_fee > 0)
                <div class="total-row">
                    <span>Delivery Fee</span>
                    <span>Rs.{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @endif
                @if($order->discount > 0)
                <div class="total-row" style="color: #ef4444;">
                    <span>Discount</span>
                    <span>- Rs.{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total Amount</span>
                    <span>Rs.{{ number_format($order->total, 2) }}</span>
                </div>
                <div class="total-row paid">
                    <span>Amount Paid</span>
                    <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
                </div>
                @if($order->total - $order->paid_amount > 0)
                <div class="total-row balance">
                    <span>Balance Due</span>
                    <span>Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business! 🤍</p>
            @if($order->paid_amount >= $order->total && $order->total > 0)
                <div class="badge">FULLY PAID</div>
            @elseif($order->total - $order->paid_amount > 0)
                <div class="badge" style="background:#f59e0b;">PAYMENT PENDING</div>
            @endif
        </div>
    </div>
    
    <script>
        // Auto print when opened from the admin
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
