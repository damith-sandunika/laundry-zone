<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name', '360 Laundry') }} — Sign In</title>
    <link rel="icon" type="image/png" href="{{ asset('logos/1786348129_admin_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white min-h-screen flex m-0">

    <!-- ──────── LEFT PANEL ──────── -->
    <div class="hidden lg:flex flex-col justify-between w-[55%] min-h-screen relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 px-14 py-14">
        <!-- Grid overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.05)_1px,transparent_0)] bg-[size:32px_32px]"></div>
        <!-- Glowing blobs -->
        <div class="absolute -top-[10%] -right-[5%] w-[500px] h-[500px] rounded-full bg-[radial-gradient(circle,rgba(59,130,246,0.35)_0%,transparent_70%)] animate-pulse"></div>
        <div class="absolute -bottom-[10%] -left-[5%] w-[400px] h-[400px] rounded-full bg-[radial-gradient(circle,rgba(99,102,241,0.25)_0%,transparent_70%)] animate-pulse" style="animation-delay: 1s;"></div>

        <div class="relative z-10 flex flex-col justify-between h-full">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-lg">
                    <img src="{{ asset('logos/1786348129_admin_logo.png') }}" alt="Logo" class="max-w-[36px] max-h-[36px] object-contain" onerror="this.style.display='none'">
                </div>
                <span class="text-white font-black text-lg tracking-tight">360 Laundry</span>
            </div>

            <!-- Middle: Hero -->
            <div>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/15 mb-8 backdrop-blur-sm shadow-lg shadow-black/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-white/80 text-[13px] font-bold">System Online</span>
                </div>
                <h2 class="text-[58px] font-black text-white leading-[1.08] tracking-tight mb-6">
                    Laundry<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Management</span><br>
                    System
                </h2>
                <p class="text-blue-200/70 text-[17px] font-medium leading-relaxed max-w-[340px]">
                    Complete POS, order tracking, inventory control, and customer management — all in one platform.
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
    <div class="flex-1 flex items-center justify-center bg-white p-10 md:p-14 w-full">
        <div class="w-full max-w-[380px]">

            <!-- Mobile brand -->
            <div class="flex lg:hidden flex-col items-center justify-center mb-10 animate-slide-up">
                <div class="w-24 h-24 rounded-[1.5rem] bg-white flex items-center justify-center shadow-2xl shadow-blue-500/20 mb-4 ring-8 ring-slate-50">
                    <img src="{{ asset('logos/1786348129_admin_logo.png') }}" alt="Logo" class="max-w-[64px] max-h-[64px] object-contain" onerror="this.style.display='none'">
                </div>
                <h1 class="font-black text-3xl text-slate-900 tracking-tight">360 Laundry</h1>
                <p class="text-[11px] font-extrabold text-blue-600 uppercase tracking-widest mt-1.5">Management System</p>
            </div>

            <!-- Heading (Desktop) -->
            <div class="mb-10 hidden lg:block animate-slide-up" style="animation-delay: 100ms;">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-2">Welcome back</h1>
                <p class="text-sm font-semibold text-slate-500">Sign in to access your management dashboard</p>
            </div>
            
            <!-- Install App Button -->
            <button id="installPwaBtn" class="hidden w-full mb-8 p-1 bg-gradient-to-r from-emerald-400 to-teal-500 rounded-2xl shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all group animate-fade-in">
                <div class="w-full bg-white/95 backdrop-blur-sm rounded-[14px] px-4 py-3.5 flex items-center justify-between group-hover:bg-transparent transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-5 h-5 text-emerald-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <div class="text-left">
                            <p class="text-[13px] font-black text-slate-900 group-hover:text-white transition-colors">Install App</p>
                            <p class="text-[11px] font-bold text-slate-500 group-hover:text-emerald-100 transition-colors">Add to Home Screen</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-emerald-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </div>
            </button>

            {{ $slot }}

            <p class="text-center text-slate-400 text-xs font-bold mt-10">
                &copy; {{ date('Y') }} 360 Laundry &middot; Powered by DSRZone
            </p>
        </div>
    </div>

    <style>
        /* These classes will be automatically styled by Tailwind if used inside forms */
        input[type="email"], input[type="password"], input[type="text"] {
            @apply w-full px-4 py-3.5 bg-slate-50 border-2 border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none placeholder:text-slate-400;
        }
        button[type="submit"] {
            @apply w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-[15px] rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all duration-300;
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
    </script>
</body>
</html>
