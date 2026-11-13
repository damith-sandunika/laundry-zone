@php
    $tenant = \App\Services\Tenancy\TenantManager::getTenant();
    $companyName = $tenant ? $tenant->name : \App\Models\Setting::get('company_name', 'Laundry Zone');
    $defaultLogo = file_exists(public_path('images/laundry_main_logo.jpeg')) 
        ? asset('images/laundry_main_logo.jpeg') 
        : asset('images/laundry_main_logo.png');
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
    <link rel="icon" type="image/jpeg" href="{{ $defaultLogo }}">
    <link rel="apple-touch-icon" href="{{ $defaultLogo }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.gtm')
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white relative overflow-x-hidden">
    @include('layouts.partials.gtm-body')

    <!-- Subtle Background Accents -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-100/70 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-100/60 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px] opacity-60"></div>
    </div>

    <!-- Main Content Centered Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 md:p-8">
        <div class="w-full max-w-[420px] transition-all">
            
            <!-- PWA Install Prompt Button (if supported) -->
            <button id="installPwaBtn" class="hidden w-full mb-6 p-1 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl shadow-md hover:shadow-lg transition-all group animate-fade-in">
                <div class="w-full bg-white rounded-[14px] px-4 py-3 flex items-center justify-between group-hover:bg-opacity-95 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center p-1 overflow-hidden">
                            <img src="{{ $companyLogo }}" alt="App Icon" class="w-full h-full object-contain" onerror="this.style.display='none'">
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-black text-slate-900">Install {{ Str::limit($companyName, 18) }}</p>
                            <p class="text-[11px] font-semibold text-slate-500">Add to Home Screen (PWA)</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-emerald-600 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </button>

            <!-- Card -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-7 sm:p-9 shadow-xl shadow-slate-200/50">
                
                <!-- Logo & Brand Header in Center -->
                <div class="flex flex-col items-center justify-center text-center mb-7">
                    <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200/80 shadow-md p-2 flex items-center justify-center mb-3.5 hover:scale-105 transition-transform duration-300">
                        <img src="{{ $companyLogo }}" alt="{{ $companyName }} Logo" class="w-full h-full object-contain" onerror="this.src='{{ $defaultLogo }}'">
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">
                        {{ $companyName }}
                    </h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">
                        Sign In to Your Account
                    </p>
                </div>

                {{ $slot }}

            </div>

            <!-- Powered By DSRZone Footer -->
            <div class="text-center mt-6 mb-2">
                <p class="text-xs font-semibold text-slate-400 flex items-center justify-center gap-1.5">
                    <span>Powered by</span>
                    <a href="https://dsrzone.com" target="_blank" rel="noopener" class="font-extrabold text-blue-600 hover:text-blue-700 transition-colors">
                        DSRZone
                    </a>
                </p>
            </div>

        </div>
    </main>

    <script>
        let deferredPrompt;
        const installBtn = document.getElementById('installPwaBtn');
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if (installBtn) installBtn.classList.remove('hidden');
        });

        if (installBtn) {
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
        }
        
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
