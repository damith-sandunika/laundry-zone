<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — {{ $branch->name ?? 'SmartLaundry' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1d4ed8 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .card{background:white;border-radius:24px;box-shadow:0 30px 60px rgba(0,0,0,0.3);width:100%;max-width:480px;text-align:center;padding:48px 32px;}
        .icon-box{width:80px;height:80px;border-radius:50%;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;box-shadow:0 0 0 10px rgba(22,163,74,0.1);}
        h1{font-size:32px;font-weight:900;color:#0f172a;margin-bottom:8px;letter-spacing:-0.03em;}
        p{font-size:15px;color:#64748b;font-weight:500;margin-bottom:32px;line-height:1.5;}
        .details{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:16px;padding:24px;margin-bottom:32px;text-align:left;}
        .detail-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
        .detail-row:last-child{margin-bottom:0;}
        .detail-label{font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;}
        .detail-val{font-size:14px;font-weight:800;color:#0f172a;}
        .btn{display:inline-block;padding:16px 32px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:white;text-decoration:none;font-weight:800;border-radius:14px;box-shadow:0 4px 16px rgba(29,78,216,0.35);transition:all 0.2s;}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(29,78,216,0.45);}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-box">
            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1>Booking Received!</h1>
        <p>Thank you, <strong>{{ $booking->customer_name }}</strong>. Your laundry pickup has been scheduled successfully.</p>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Booking ID</span>
                <span class="detail-val text-blue-600">#{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date</span>
                <span class="detail-val">{{ $booking->preferred_date->format('d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-val" style="color:#eab308;">Pending Confirmation</span>
            </div>
        </div>

        <a href="{{ route('booking.page') }}" class="btn">Book Another Order</a>
        <p style="margin-top:24px;font-size:13px;color:#94a3b8;margin-bottom:0;">You will receive a WhatsApp message shortly.</p>
    </div>
</body>
</html>
