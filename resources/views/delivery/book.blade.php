<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Laundry Pickup — {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: linear-gradient(135deg, #1e40af 0%, #3730a3 50%, #4f46e5 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: white; border-radius: 24px; padding: 40px; max-width: 520px; width: 100%; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
        .logo { text-align: center; margin-bottom: 28px; }
        .logo h1 { font-size: 24px; font-weight: 900; color: #1e293b; }
        .logo p { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
        input, select, textarea { width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 14px; font-family: 'Figtree', sans-serif; font-weight: 500; color: #1e293b; outline: none; transition: border-color 0.2s; background: #f8fafc; }
        input:focus, select:focus, textarea:focus { border-color: #3b82f6; background: white; }
        textarea { resize: vertical; min-height: 80px; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer; margin-top: 8px; transition: transform 0.2s, box-shadow 0.2s; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 25px rgba(37,99,235,0.4); }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .alert { padding: 12px 14px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 18px; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        @media(max-width: 480px) { .row { grid-template-columns: 1fr; } .card { padding: 28px 20px; } }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div style="font-size:36px;margin-bottom:8px;">🧺</div>
        <h1>Book Laundry Pickup</h1>
        <p>Schedule your pickup & delivery</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('delivery.store') }}">
        @csrf

        <div class="row">
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Full name" required>
            </div>
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="07X XXXXXXX" required>
            </div>
        </div>

        <div class="form-group">
            <label>Select Branch *</label>
            <select name="branch_id" required>
                <option value="">Choose nearest branch...</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Pickup Address *</label>
            <textarea name="pickup_address" placeholder="Your home or office address for pickup..." required>{{ old('pickup_address') }}</textarea>
        </div>

        <div class="form-group">
            <label>Delivery Address (if different from pickup)</label>
            <textarea name="delivery_address" placeholder="Leave blank if same as pickup address...">{{ old('delivery_address') }}</textarea>
        </div>

        <div class="form-group">
            <label>Pickup Scheduled Date & Time *</label>
            <input type="datetime-local" name="pickup_scheduled_at" value="{{ old('pickup_scheduled_at') }}"
                   min="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}" required>
        </div>

        <div class="form-group">
            <label>Special Notes (optional)</label>
            <textarea name="notes" placeholder="Any special instructions...">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="btn">🚀 Book Pickup Now</button>
    </form>
</div>
</body>
</html>


