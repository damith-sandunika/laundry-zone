<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Laundry Pickup — {{ $branch->name ?? 'SmartLaundry' }}</title>
    <meta name="description" content="Book your laundry pickup online. Fast, easy and convenient.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#1d4ed8 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
        .card{background:white;border-radius:24px;box-shadow:0 30px 60px rgba(0,0,0,0.3);width:100%;max-width:560px;overflow:hidden;}
        .card-header{background:linear-gradient(135deg,#1d4ed8,#4f46e5);padding:32px;text-align:center;position:relative;}
        .card-header .icon{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;}
        .card-header h1{font-size:26px;font-weight:900;color:white;margin:0 0 6px;letter-spacing:-0.03em;}
        .card-header p{font-size:14px;color:rgba(147,197,253,0.8);font-weight:500;margin:0;}
        .branch-pill{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);border-radius:100px;padding:6px 14px;margin-top:14px;}
        .branch-pill span{color:rgba(255,255,255,0.85);font-size:12px;font-weight:700;}
        .card-body{padding:32px;}
        .form-group{margin-bottom:20px;}
        label{display:block;font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;}
        input,select,textarea{width:100%;padding:13px 16px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:500;color:#111827;font-family:'Inter',sans-serif;outline:none;transition:all 0.2s;background:#f9fafb;}
        input:focus,select:focus,textarea:focus{border-color:#3b82f6;background:white;box-shadow:0 0 0 4px rgba(59,130,246,0.08);}
        input::placeholder,textarea::placeholder{color:#9ca3af;font-weight:400;}
        textarea{resize:vertical;min-height:90px;}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        @media(max-width:480px){.row{grid-template-columns:1fr;}}
        .btn{width:100%;padding:16px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);color:white;font-size:15px;font-weight:800;border:none;border-radius:14px;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 4px 16px rgba(29,78,216,0.35);letter-spacing:-0.01em;margin-top:8px;}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(29,78,216,0.45);}
        .btn:active{transform:translateY(0);}
        .error-box{background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:12px 16px;margin-bottom:20px;font-size:13px;font-weight:600;color:#dc2626;}
        .footer-note{text-align:center;font-size:12px;color:#94a3b8;margin-top:20px;font-weight:500;}
        .dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite;}
        @keyframes pulse{0%,100%{opacity:1;}50%{opacity:0.4;}}
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="icon">
                <svg width="28" height="28" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h1>Book a Laundry Pickup</h1>
            <p>Schedule your pickup — we'll handle the rest</p>
            @if($branch)
            <div class="branch-pill">
                <span class="dot"></span>
                <span>{{ $branch->name }}</span>
            </div>
            @endif
        </div>

        <div class="card-body">
            @if($errors->any())
            <div class="error-box">
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('booking.store') }}" method="POST">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id ?? 1 }}">

                <div class="row">
                    <div class="form-group">
                        <label>Your Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Kamal Perera" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="077 123 4567" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="kamal@gmail.com">
                </div>

                <div class="form-group">
                    <label>Pickup Address</label>
                    <input type="text" name="customer_address" value="{{ old('customer_address') }}" placeholder="No. 12, Main Street, Colombo">
                </div>

                <div class="form-group">
                    <label>What services do you need? *</label>
                    <textarea name="service_description" placeholder="e.g. 5 shirts, 2 trousers, 1 bedsheet for wash &amp; iron..." required>{{ old('service_description') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Preferred Pickup Date *</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label>Additional Notes</label>
                    <textarea name="notes" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Confirm Booking
                </button>
            </form>

            <p class="footer-note">📞 We'll send a confirmation to your WhatsApp number shortly.</p>
        </div>
    </div>
</body>
</html>
