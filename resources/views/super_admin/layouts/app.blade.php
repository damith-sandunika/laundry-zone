<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SaaS Admin Platform' }} - {{ \App\Models\Central\PlatformSetting::get('platform_name', 'Laundry Zone SaaS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/laundry_main_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    borderRadius: {
                        '3xl': '1rem',
                        '2xl': '0.75rem',
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        *, *::before, *::after {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif !important;
        }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Collapsed Sidebar Styles (identical to workshop admin) */
        aside.sidebar-collapsed { width: 5rem !important; }
        aside.sidebar-collapsed .sidebar-header { padding: 1rem 0.5rem !important; }
        aside.sidebar-collapsed .sidebar-logo-btn { width: 3rem !important; height: 3rem !important; border-radius: 0.75rem !important; margin-bottom: 0 !important; }
        aside.sidebar-collapsed .sidebar-logo-img { max-width: 32px !important; max-height: 32px !important; }
        aside.sidebar-collapsed .sidebar-brand-text { display: none !important; }
        aside.sidebar-collapsed .sidebar-footer { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
        aside.sidebar-collapsed .sidebar-footer button { padding: 0.75rem !important; border-radius: 9999px !important; justify-content: center !important; font-size: 0px !important; color: transparent !important; gap: 0 !important; }
        aside.sidebar-collapsed .sidebar-footer button svg { width: 1.25rem !important; height: 1.25rem !important; margin: 0 !important; flex-shrink: 0 !important; }
        aside.sidebar-collapsed .sidebar-footer button span { display: none !important; }
        aside.sidebar-collapsed nav { overflow-x: hidden; }
        aside.sidebar-collapsed nav a, aside.sidebar-collapsed nav button { padding-left: 0.75rem !important; padding-right: 0.75rem !important; justify-content: center; font-size: 0px !important; color: transparent !important; gap: 0 !important; white-space: nowrap; }
        aside.sidebar-collapsed nav a svg, aside.sidebar-collapsed nav button svg { margin: 0 !important; color: #64748b; flex-shrink: 0; }
        aside.sidebar-collapsed nav a.bg-blue-50 svg, aside.sidebar-collapsed nav button.bg-blue-50 svg { color: #1d4ed8; }
        aside.sidebar-collapsed .menu-category-title { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-100 selection:text-blue-900"
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sa_sidebarCollapsed') === 'true', profileOpen: false }"
    x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sa_sidebarCollapsed', val))">

    <div class="flex h-screen overflow-hidden bg-slate-50/50">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

        <!-- ══════════ SIDEBAR ══════════ -->
        @include('super_admin.layouts.partials.sidebar')

        <!-- ══════════ MAIN CONTENT AREA ══════════ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            <!-- TOPBAR -->
            @include('super_admin.layouts.partials.topbar')

            <!-- MAIN BODY -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 animate-fade-in relative z-10 custom-scrollbar">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-sm"
                        x-data="{ show: true }" x-show="show" x-transition>
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                    </div>
                @endif

                @if(session('error') || (isset($errors) && $errors->has('error')))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-800 text-xs font-bold flex items-center justify-between shadow-sm"
                        x-data="{ show: true }" x-show="show" x-transition>
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>{{ session('error') ?? ($errors->first('error') ?? '') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
