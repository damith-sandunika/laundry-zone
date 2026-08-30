<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }} — Track</title>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: linear-gradient(135deg, #0f172a, #1e293b); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 24px; padding: 36px; max-width: 440px; width: 100%; box-shadow: 0 30px 60px rgba(0,0,0,0.4); }
        .badge { display: inline-block; padding: 5px 14px; border-radius: 99px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }
        .status-step { display: flex; align-items: center; gap: 12px; padding: 10px 0; }
        .dot { width: 14px; height: 14px; border-radius: 50%; flex-shrink: 0; }
        .dot-done { background: #22c55e; }
        .dot-current { background: #3b82f6; box-shadow: 0 0 0 4px rgba(59,130,246,0.2); animation: pulse 1.5s infinite; }
        .dot-pending { background: #e2e8f0; }
        @keyframes pulse { 0%, 100% { box-shadow: 0 0 0 4px rgba(59,130,246,0.2); } 50% { box-shadow: 0 0 0 8px rgba(59,130,246,0.1); } }
        h1 { font-size: 22px; font-weight: 900; color: #1e293b; margin-bottom: 4px; }
        .subtitle { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .info-label { color: #94a3b8; font-weight: 600; }
        .info-value { font-weight: 700; color: #1e293b; }
        .section-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin: 20px 0 10px; }
    </style>
</head>
<body>
<div class="card">
    <div style="text-align:center; margin-bottom: 20px;">
        <div style="font-size: 40px; margin-bottom: 8px;">🧺</div>
        <h1>Order #{{ $order->id }}</h1>
        <p class="subtitle">{{ $order->branch->name ?? '360 Laundry' }}</p>
    </div>

    <div class="section-title">Order Info</div>
    <div class="info-row">
        <span class="info-label">Customer</span>
        <span class="info-value">{{ $order->customer->name ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Total</span>
        <span class="info-value">Rs.{{ number_format($order->total, 2) }}</span>
    </div>
    @if($order->due_date)
    <div class="info-row">
        <span class="info-label">Ready By</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($order->due_date)->format('d M Y') }}</span>
    </div>
    @endif
    <div class="info-row">
        <span class="info-label">Placed On</span>
        <span class="info-value">{{ $order->created_at->format('d M Y, H:i') }}</span>
    </div>

    <div class="section-title">Order Status</div>
    @php
        $statuses = [
            'received'                 => '📦 Received',
            'processing'               => '⚙️ Processing',
            'washing'                  => '🫧 Washing',
            'drying'                   => '💨 Drying',
            'folding'                  => '👕 Folding',
            'dispatched_to_workshop'   => '📤 Sent to Workshop',
            'at_workshop'              => '🏭 At Workshop',
            'returned_from_workshop'   => '📥 Returned from Workshop',
            'done'                     => '✅ Ready for Pickup',
            'out_for_delivery'         => '🚚 Out for Delivery',
            'delivered'                => '🎉 Delivered',
        ];
        $statusKeys = array_keys($statuses);
        $currentIndex = array_search($order->status, $statusKeys);
    @endphp
    <div style="padding: 4px 0;">
        @foreach($statuses as $key => $label)
        @php
            $idx = array_search($key, $statusKeys);
            $isDone = $idx < $currentIndex;
            $isCurrent = $key === $order->status;
        @endphp
        <div class="status-step">
            <div class="dot {{ $isDone ? 'dot-done' : ($isCurrent ? 'dot-current' : 'dot-pending') }}"></div>
            <span style="font-size: 13px; font-weight: {{ $isCurrent ? '800' : '600' }}; color: {{ $isCurrent ? '#1e293b' : ($isDone ? '#22c55e' : '#94a3b8') }}">
                {{ $label }}
                @if($isCurrent) <span style="font-size: 10px; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 99px; margin-left: 6px;">Current</span> @endif
            </span>
        </div>
        @endforeach
    </div>

    <div style="margin-top: 20px; padding: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; text-align: center; font-size: 12px; color: #16a34a; font-weight: 700;">
        Thank you for choosing us! 🙏
    </div>
</div>
</body>
</html>

