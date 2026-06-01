<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 0; size: 80mm auto; }
        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            font-size: 12px; 
            width: 74mm; 
            max-width: 100%;
            margin: 0 auto; 
            padding: 4mm 3mm; 
            color: #000;
            background: #fff;
        }
        .center { text-align: center; }
        .bold { font-weight: 800; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; }
        .divider { border-top: 1.5px dashed #cbd5e1; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; padding: 3px 0; gap: 4px; }
        .row span:first-child { flex: 1; text-align: left; word-break: break-word; }
        .row span:last-child { text-align: right; white-space: nowrap; }
        .total-row { font-weight: 900; font-size: 15px; border-top: 2px solid #000; padding-top: 6px; margin-top: 6px; }
        .badge { display: inline-block; border: 1.5px solid #000; padding: 4px 10px; margin-top: 6px; font-size: 12px; font-weight: 800; border-radius: 6px; }
        .item-name { font-weight: 700; font-size: 12px; display: block; word-break: break-word; }
        .item-details { font-size: 11px; color: #475569; padding-left: 2px; }
        
        @media print {
            body { width: 74mm; padding: 2mm 3mm; margin: 0 auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="center" style="margin-bottom: 12px;">
        <!-- PRINT LOGO: Replace the file public/images/print_logo.png with your thermal printer logo -->
        <img src="{{ asset('images/print_logo.png') }}" alt="Logo" style="max-width: 140px; height: auto;" onerror="this.style.display='none'">
    </div>
    
    <div class="center bold" style="font-size:18px; letter-spacing:-0.03em; margin-bottom: 2px;">{{ $branch->name ?? 'SMART LAUNDRY' }}</div>
    @if($branch && $branch->tagline)
        <div class="center" style="font-size:10px; margin-bottom:2px;">{{ $branch->tagline }}</div>
    @endif
    @if($branch && $branch->phone)
        <div class="center" style="font-size:10px;">📞 {{ $branch->phone }}</div>
    @endif
    @if($branch && $branch->address)
        <div class="center" style="font-size:10px;">{{ $branch->address }}</div>
    @endif

    <div class="divider"></div>

    <div class="row" style="font-size: 12px;"><span>Bill No:</span><span class="bold">{{ $order->order_number }}</span></div>
    <div class="row" style="font-size: 12px;"><span>Date:</span><span class="bold">{{ $order->created_at->format('d M Y h:i A') }}</span></div>
    <div class="row" style="font-size: 12px;"><span>Customer:</span><span class="bold">{{ $order->customer->name }}</span></div>
    <div class="row" style="font-size: 12px;"><span>Phone:</span><span class="bold">{{ $order->customer->phone }}</span></div>

    <div class="divider"></div>

    <div class="bold uppercase" style="margin-bottom:6px; color:#475569;">Order Items</div>
    @foreach($order->items as $item)
    <div class="row" style="margin-bottom: 2px;">
        <span class="item-name">{{ $item->serviceType->name ?? $item->item_description }}</span>
        <span class="bold">Rs.{{ number_format($item->total_price, 2) }}</span>
    </div>
    <div class="item-details" style="margin-bottom: 6px;">
        {{ $item->quantity }} x Rs.{{ number_format($item->unit_price, 2) }}
    </div>
    @endforeach

    <div class="divider"></div>

    @if($order->discount > 0)
    <div class="row"><span>Subtotal</span><span>Rs.{{ number_format($order->subtotal, 2) }}</span></div>
    <div class="row"><span>Discount</span><span>- Rs.{{ number_format($order->discount, 2) }}</span></div>
    @endif

    <div class="row total-row">
        <span>TOTAL</span>
        <span>Rs.{{ number_format($order->total, 2) }}</span>
    </div>

    <div class="row" style="margin-top:4px;">
        <span>Paid</span>
        <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
    </div>
    @if($order->total - $order->paid_amount > 0)
    <div class="row bold">
        <span>Balance Due</span>
        <span>Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</span>
    </div>
    @endif

    <div class="divider"></div>

    <div class="center" style="margin: 8px 0;">
        <span class="badge">
            @if($order->paid_amount >= $order->total) ✓ PAID IN FULL @else 🕒 PAY ON PICKUP @endif
        </span>
    </div>

    <div class="divider"></div>
    <div class="center bold" style="font-size:13px; margin-top:8px;">Thank you! Come again</div>
    @if($branch && $branch->whatsapp_number)
        <div class="center" style="font-size:11px; margin-top:2px; color:#475569;">WhatsApp: {{ $branch->whatsapp_number }}</div>
    @endif
    
    <div class="center" style="font-size:10px; margin-top:8px; color:#9ca3af;">
        Software by DSRZone
    </div>

    <div class="no-print" style="margin-top:16px; text-align:center;">
        <button onclick="window.print()" style="padding:8px 20px; background:#2563eb; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
            🖨 Print
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:13px; margin-left:8px;">
            Close
        </button>
    </div>
    <script>window.onload = function() { window.print(); }</script>
</body>
</html>
