@php
    $tenant = \App\Services\Tenancy\TenantManager::getTenant();
    $companyName = $tenant ? $tenant->name : \App\Models\Setting::get('company_name', 'Laundry Zone');
    $defaultLogo = asset('images/laundry_main_logo.png');
    $companyLogo = ($tenant && !empty($tenant->logo_path)) 
        ? asset($tenant->logo_path) 
        : $defaultLogo;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $companyName }} — Sign In</title>
    <link rel="icon" type="image/png" href="{{ $defaultLogo }}">
    <link rel="apple-touch-icon" href="{{ $defaultLogo }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.gtm')
</head>
<body class="font-sans antialiased bg-slate-900 min-h-screen flex m-0">
    @include('layouts.partials.gtm-body')

    <!-- ──────── LEFT PANEL ──────── -->
    <div class="hidden lg:flex flex-col justify-between w-[55%] min-h-screen relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 px-14 py-14">
        <!-- Grid overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.05)_1px,transparent_0)] bg-[size:32px_32px]"></div>
        <!-- Glowing blobs -->
        <div class="absolute -top-[10%] -right-[5%] w-[500px] h-[500px] rounded-full bg-[radial-gradient(circle,rgba(59,130,246,0.35)_0%,transparent_70%)] animate-pulse"></div>
        <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] rounded-full bg-[radial-gradient(circle,rgba(99,102,241,0.25)_0%,transparent_70%)] animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative z-10 flex flex-col justify-between h-full">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-lg p-1.5 overflow-hidden">
                    <img src="{{ $companyLogo }}" alt="Logo" class="w-full h-full object-contain" onerror="this.src='{{ asset('images/laundry_main_logo.png') }}'">
                </div>
                <span class="text-white font-black text-lg tracking-tight">{{ $companyName }}</span>
            </div>

            <!-- Middle: Hero -->
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 mb-8 backdrop-blur-sm shadow-lg shadow-black/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-white/80 text-[13px] font-bold">System Online</span>
                </div>
                <h2 class="text-[58px] font-black text-white leading-[1.08] tracking-tight mb-6">
                    {{ Str::before($companyName, ' ') }}<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">{{ Str::after($companyName, ' ') ?: 'Management' }}</span><br>
                    System
                </h2>
                <p class="text-blue-200/70 text-[17px] font-medium leading-relaxed max-w-[340px]">
                    Complete POS, order tracking, inventory control, and customer management — all in one platform.
                </p>
                </p>
            </div>

            <!-- Bottom: Feature pills -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-2xl p-5 text-center transition-transform hover:-translate-y-1 duration-300">
                    <p class="text-xl font-black text-white mb-1">POS</p>
                    <p class="text-[11px] font-extrabold text-blue-200/60 uppercase tracking-widest">Point of Sale</p>
                </div>
                <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-2xl p-5 text-center transition-transform hover:-translate-y-1 duration-300">
                    <p class="text-xl font-black text-white mb-1">Track</p>
                    <p class="text-[11px] font-extrabold text-blue-200/60 uppercase tracking-widest">Live Orders</p>
                </div>
                <div class="bg-white/5 border border-white/10 backdrop-blur-md rounded-2xl p-5 text-center transition-transform hover:-translate-y-1 duration-300">
                    <p class="text-xl font-black text-white mb-1">WA</p>
                    <p class="text-[11px] font-extrabold text-blue-200/60 uppercase tracking-widest">WhatsApp</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ──────── RIGHT PANEL: Form ──────── -->
    <div class="flex-1 flex items-center justify-center bg-slate-900 p-10 md:p-14 w-full">
        <div class="w-full max-w-[380px]">

            <!-- Mobile brand -->
            <div class="flex lg:hidden flex-col items-center justify-center mb-10 animate-slide-up">
                <div class="w-24 h-24 rounded-[1.5rem] bg-slate-800 flex items-center justify-center shadow-2xl shadow-blue-900/20 mb-4 ring-8 ring-slate-900 p-2 overflow-hidden">
                    <img src="{{ $companyLogo }}" alt="Logo" class="w-full h-full object-contain" onerror="this.src='{{ asset('images/laundry_main_logo.png') }}'">
                </div>
                <h1 class="font-black text-3xl text-white tracking-tight text-center">{{ $companyName }}</h1>
                <p class="text-[11px] font-extrabold text-blue-400 uppercase tracking-widest mt-1.5">Management System</p>
            </div>

            <!-- Heading (Desktop) -->
            <div class="mb-10 hidden lg:block animate-slide-up" style="animation-delay: 100ms;">
                <h1 class="text-4xl font-black text-white tracking-tight mb-2">Welcome back</h1>
                <p class="text-sm font-semibold text-slate-400">Sign in to access {{ $companyName }} dashboard</p>
            </div>
            
            <!-- Install App Button -->
            <button id="installPwaBtn" class="hidden w-full mb-8 p-1 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl shadow-lg shadow-emerald-900/30 hover:shadow-emerald-900/50 transition-all group animate-fade-in">
                <div class="w-full bg-slate-900/95 backdrop-blur-sm rounded-[14px] px-4 py-3.5 flex items-center justify-between group-hover:bg-transparent transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-900/50 flex items-center justify-center group-hover:bg-white/10 transition-colors p-1.5 overflow-hidden">
                            <img src="{{ $companyLogo }}" alt="App Icon" class="w-full h-full object-contain" onerror="this.style.display='none'">
                        </div>
                        <div class="text-left">
                            <p class="text-[13px] font-black text-white group-hover:text-white transition-colors">Install {{ Str::limit($companyName, 18) }}</p>
                            <p class="text-[11px] font-bold text-slate-400 group-hover:text-emerald-100 transition-colors">Add to Home Screen (PWA)</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-emerald-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </button>

            {{ $slot }}

            <p class="text-center text-slate-400 text-xs font-bold mt-10">
                &copy; {{ date('Y') }} {{ $companyName }} &middot; Powered by DSRZone
            </p>
        </div>
    </div>

    <style>
        /* These classes will be automatically styled by Tailwind if used inside forms */
        input[type="email"], input[type="password"], input[type="text"] {
            @apply w-full px-4 py-3.5 bg-slate-800 border-2 border-slate-700 rounded-xl text-sm font-semibold text-white focus:bg-slate-800 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 transition-all outline-none placeholder:text-slate-500;
        }
        button[type="submit"] {
            @apply w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-[15px] rounded-xl shadow-lg shadow-blue-900/50 hover:shadow-blue-900/70 hover:-translate-y-0.5 transition-all duration-300;
        }
    </style>
    
    <script>
        let deferredPrompt;
        const installBtn = document.getElementById('installPwaBtn');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.classList.remove('hidden');
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installBtn.classList.add('hidden');
                }
                deferredPrompt = null;
            }
        });
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(err => console.error(err));
        }

        // Anti-double-submit for guest forms
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form.checkValidity && !form.checkValidity()) return;
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
            form.dataset.submitting = 'true';
            const btns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            btns.forEach(btn => {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                btn.style.cursor = 'wait';
                btn.style.pointerEvents = 'none';
            });
            setTimeout(() => {
                form.dataset.submitting = 'false';
                btns.forEach(btn => {
                    btn.disabled = false;
                    btn.style.opacity = '';
                    btn.style.cursor = '';
                    btn.style.pointerEvents = '';
                });
            }, 10000);
        });
        window.addEventListener('pageshow', () => {
            document.querySelectorAll('form[data-submitting="true"]').forEach(f => f.dataset.submitting = 'false');
            document.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '';
                btn.style.cursor = '';
                btn.style.pointerEvents = '';
            });
        });
    </script>
</body>
</html>


