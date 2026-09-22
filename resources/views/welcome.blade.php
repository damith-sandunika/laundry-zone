<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-100 selection:text-blue-900 overflow-x-hidden min-h-screen flex flex-col">
    
    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/360_laundry_logo.png') }}" alt="{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}" class="h-10" onerror="this.style.display='none'">
            </div>
            
            <div class="flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-blue-50 text-blue-700 font-bold text-sm rounded-xl hover:bg-blue-100 transition-colors">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 text-slate-600 font-bold text-sm hover:text-slate-900 transition-colors">Login</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all duration-300">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden flex-1 flex flex-col justify-center">
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#f1f5f9_1px,transparent_1px),linear-gradient(to_bottom,#f1f5f9_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-50"></div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[40rem] h-[40rem] bg-blue-400/20 rounded-full blur-3xl opacity-50 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[40rem] h-[40rem] bg-indigo-400/20 rounded-full blur-3xl opacity-50 animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 text-center animate-slide-up">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-black uppercase tracking-widest mb-8 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                Smart Laundry Management
            </div>
            
            <h1 class="text-5xl md:text-7xl font-black text-slate-900 tracking-tight leading-tight mb-6">
                Manage Your Laundry <br class="hidden md:block"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Like A Pro</span>
            </h1>
            
            <p class="mt-4 text-lg md:text-xl font-medium text-slate-500 max-w-2xl mx-auto leading-relaxed mb-10">
                The ultimate SaaS platform for modern laundries. Seamlessly handle POS orders, inventory, staff, and customer WhatsApp notifications from one centralized dashboard.
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-slate-900 text-white font-black text-sm rounded-xl shadow-xl shadow-slate-900/20 hover:shadow-slate-900/40 hover:-translate-y-1 transition-all duration-300">
                    Get Started Now
                </a>
                <a href="{{ route('online_bookings.index') }}" class="w-full sm:w-auto px-8 py-4 bg-white text-slate-700 border border-slate-200 font-black text-sm rounded-xl shadow-sm hover:bg-slate-50 hover:text-slate-900 hover:-translate-y-1 transition-all duration-300">
                    Book Laundry Pickup
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-slate-200/60 bg-white/50 backdrop-blur-xl relative z-10">
        <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">&copy; {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}. All rights reserved.</p>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Software by DSRZone</p>
        </div>
    </footer>
</body>
</html>


