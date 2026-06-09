<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Smart Laundry') }} — Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background:#f8fafc; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', system-ui, -apple-system, sans-serif !important; font-weight: 800 !important; }
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
        }
        .input-field {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #111827;
            outline: none;
            transition: all 0.2s;
            background: #fafafa;
        }
        .input-field:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.08);
        }
        .input-field::placeholder { color: #9ca3af; font-weight: 400; }
        .btn-primary {
            width: 100%;
            padding: 14px 24px;
            background: #1d4ed8;
            color: white;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }
        .btn-primary:hover {
            background: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(29,78,216,0.35);
        }
        .btn-primary:active { transform: translateY(0); }
    </style>
</head>
<body style="background:#fff; margin:0; min-height:100vh; display:flex; flex-direction:row;">

    <!-- ──────── LEFT PANEL ──────── -->
    <div style="
        display: none;
        width: 55%;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        background: linear-gradient(150deg, #0f172a 0%, #1e3a5f 50%, #1d4ed8 100%);
    " class="lg-panel">
        <!-- Grid overlay -->
        <div style="position:absolute;inset:0;background-image:radial-gradient(circle at 1px 1px, rgba(255,255,255,0.05) 1px, transparent 0);background-size:32px 32px;"></div>
        <!-- Glowing blobs -->
        <div style="position:absolute;top:-10%;right:-5%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle, rgba(59,130,246,0.35) 0%, transparent 70%);"></div>
        <div style="position:absolute;bottom:-10%;left:-5%;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle, rgba(99,102,241,0.25) 0%, transparent 70%);"></div>

        <div style="position:relative;z-index:10;display:flex;flex-direction:column;justify-content:space-between;padding:56px;height:100%;box-sizing:border-box;">

            <!-- Top: Logo -->
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <span style="color:white;font-weight:800;font-size:18px;letter-spacing:-0.02em;">SmartLaundry</span>
            </div>

            <!-- Middle: Hero -->
            <div>
                <div style="display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:100px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);margin-bottom:32px;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#34d399;display:inline-block;animation:pulse 2s infinite;"></span>
                    <span style="color:rgba(255,255,255,0.75);font-size:13px;font-weight:600;">System Online</span>
                </div>
                <h2 style="font-size:58px;font-weight:900;color:white;line-height:1.08;letter-spacing:-0.03em;margin:0 0 24px 0;">
                    Laundry<br>
                    <span class="gradient-text">Management</span><br>
                    System
                </h2>
                <p style="color:rgba(147,197,253,0.7);font-size:17px;font-weight:500;line-height:1.6;max-width:340px;">
                    Complete POS, order tracking, inventory control, and customer management — all in one platform.
                </p>
            </div>

            <!-- Bottom: Feature pills -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
                <div class="glass-card" style="border-radius:16px;padding:18px;text-align:center;">
                    <p style="font-size:20px;font-weight:900;color:white;margin:0 0 4px 0;">POS</p>
                    <p style="font-size:11px;font-weight:700;color:rgba(147,197,253,0.6);text-transform:uppercase;letter-spacing:0.08em;margin:0;">Point of Sale</p>
                </div>
                <div class="glass-card" style="border-radius:16px;padding:18px;text-align:center;">
                    <p style="font-size:20px;font-weight:900;color:white;margin:0 0 4px 0;">Track</p>
                    <p style="font-size:11px;font-weight:700;color:rgba(147,197,253,0.6);text-transform:uppercase;letter-spacing:0.08em;margin:0;">Live Orders</p>
                </div>
                <div class="glass-card" style="border-radius:16px;padding:18px;text-align:center;">
                    <p style="font-size:20px;font-weight:900;color:white;margin:0 0 4px 0;">WA</p>
                    <p style="font-size:11px;font-weight:700;color:rgba(147,197,253,0.6);text-transform:uppercase;letter-spacing:0.08em;margin:0;">WhatsApp</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ──────── RIGHT PANEL: Form ──────── -->
    <div style="flex:1;display:flex;align-items:center;justify-content:center;background:white;padding:40px 32px;">
        <div style="width:100%;max-width:380px;">

            <!-- Mobile brand -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:40px;" class="mobile-brand">
                <div style="width:36px;height:36px;border-radius:10px;background:#1d4ed8;display:flex;align-items:center;justify-content:center;">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <span style="font-weight:900;font-size:17px;color:#111827;letter-spacing:-0.01em;">SmartLaundry</span>
            </div>

            <!-- Heading -->
            <div style="margin-bottom:36px;">
                <h1 style="font-size:32px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 8px 0;">Welcome back</h1>
                <p style="font-size:14px;color:#6b7280;font-weight:500;margin:0;">Sign in to access your management dashboard</p>
                
                <button id="installPwaBtn" style="display:none;margin-top:16px;width:100%;padding:12px;background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;border-radius:12px;font-weight:800;font-size:14px;cursor:pointer;align-items:center;justify-content:center;gap:8px;transition:all 0.2s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Install SmartLaundry App
                </button>
            </div>

            {{ $slot }}

            <p style="text-align:center;color:#d1d5db;font-size:12px;font-weight:500;margin-top:40px;">
                &copy; {{ date('Y') }} SmartLaundry &middot; SL Digital System
            </p>
        </div>
    </div>

    <style>
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
        @media (min-width: 1024px) {
            .lg-panel { display: flex !important; }
            .mobile-brand { display: none !important; }
        }
    </style>
    
    <script>
        let deferredPrompt;
        const installBtn = document.getElementById('installPwaBtn');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.style.display = 'flex';
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installBtn.style.display = 'none';
                }
                deferredPrompt = null;
            }
        });
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(err => console.error(err));
        }
    </script>
</body>
</html>
