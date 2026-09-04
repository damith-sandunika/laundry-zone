<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed!</title>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: linear-gradient(135deg, #1e40af, #4f46e5); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 24px; padding: 40px; max-width: 460px; width: 100%; text-align: center; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
        .icon { font-size: 64px; margin-bottom: 16px; }
        h1 { font-size: 26px; font-weight: 900; color: #1e293b; }
        p { color: #64748b; font-weight: 500; font-size: 14px; margin-top: 8px; line-height: 1.6; }
        .info-box { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 14px; padding: 20px; margin: 24px 0; text-align: left; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px solid #e0f2fe; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-weight: 700; color: #64748b; }
        .info-value { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; }
        .btn { display: inline-block; padding: 12px 28px; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; border-radius: 12px; font-weight: 800; font-size: 14px; text-decoration: none; transition: transform 0.2s; }
        .btn:hover { transform: translateY(-1px); }
        .id-badge { display: inline-block; background: #eff6ff; color: #2563eb; font-size: 13px; font-weight: 800; padding: 6px 16px; border-radius: 99px; border: 2px solid #bfdbfe; margin-top: 10px; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">✅</div>
    <h1>Booking Confirmed!</h1>
    <p>Your laundry pickup request has been received. We'll confirm shortly.</p>
    <div class="id-badge">Order #{{ $appointment->order->order_number ?? $appointment->id }}</div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">📍 Pickup From</span>
            <span class="info-value">{{ $appointment->pickup_address }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">📅 Scheduled At</span>
            <span class="info-value">{{ $appointment->pickup_scheduled_at?->format('d M Y, H:i') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">🏪 Branch</span>
            <span class="info-value">{{ $appointment->branch->name ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">📞 Contact</span>
            <span class="info-value">{{ $appointment->contact_phone }}</span>
        </div>
        @if($appointment->notes)
        <div class="info-row">
            <span class="info-label">📝 Notes</span>
            <span class="info-value">{{ $appointment->notes }}</span>
        </div>
        @endif
    </div>

    <p style="font-size:13px; color:#94a3b8;">Save your Order Number: <strong style="color:#2563eb;">#{{ $appointment->order->order_number ?? $appointment->id }}</strong> to track your order.</p>
    <br>
    <a href="{{ route('delivery.book') }}" class="btn">+ Book Another</a>
</div>
</body>
</html>

