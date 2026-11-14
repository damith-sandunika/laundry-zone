@php
    $tenant = \App\Services\Tenancy\TenantManager::getTenant();
    $companyName = $tenant ? $tenant->name : \App\Models\Setting::get('company_name', 'Laundry Zone');
    $defaultLogo = file_exists(public_path('images/laundry_main_logo.jpeg')) 
        ? asset('images/laundry_main_logo.jpeg') 
        : asset('images/laundry_main_logo.png');
    $companyLogo = ($tenant && !empty($tenant->logo_path)) 
        ? asset($tenant->logo_path) 
        : asset(\App\Models\Setting::get('company_logo', 'images/laundry_main_logo.jpeg'));
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
    <title>{{ $companyName }}</title>
    <link rel="icon" type="image/jpeg" href="{{ $companyLogo }}">
    <link rel="apple-touch-icon" href="{{ $companyLogo }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #nprogress .bar { background: #3b82f6 !important; height: 3px !important; }
        #nprogress .peg { box-shadow: 0 0 10px #3b82f6, 0 0 5px #3b82f6 !important; }
        #nprogress .spinner { display: none !important; }
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @include('layouts.partials.gtm')
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-100 selection:text-blue-900"
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', notifOpen: false, profileOpen: false }"
    x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
    @include('layouts.partials.gtm-body')
    <div class="flex h-screen overflow-hidden bg-slate-50/50">
        @include('layouts.partials.sidebar_collapsed_styles')

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen=false"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

        @include('layouts.partials.sidebar')

        <!-- MAIN AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
            @include('layouts.partials.topbar')

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 animate-fade-in relative z-10 scrollbar-hide">
                @if (isset($header))
                    <div class="mb-6 lg:mb-8 bg-white rounded-xl p-3.5 sm:p-4 md:p-5 shadow-sm border border-slate-200/90 relative flex items-center justify-between gap-3 md:gap-4 overflow-hidden">
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
                            <div class="w-24 h-24 bg-blue-50/80 rotate-45 translate-x-12 rounded-lg"></div>
                            <div class="w-36 h-36 bg-blue-100/70 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
                            <div class="w-48 h-48 bg-blue-200/50 rotate-45 -ml-12 rounded-2xl"></div>
                            <div class="w-32 h-32 bg-blue-300/40 rotate-45 -ml-16 rounded-lg"></div>
                        </div>

                        <div class="relative z-10 w-full flex items-center gap-3">
                            <button type="button"
                                onclick="if(window.history.length > 1) { window.history.back(); } else { window.location.href='/dashboard'; }"
                                class="p-2 sm:p-2.5 min-w-[38px] sm:min-w-[42px] min-h-[38px] sm:min-h-[42px] rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-blue-600 border border-slate-200/80 shadow-sm transition-all flex items-center justify-center shrink-0 active:scale-95 group"
                                title="Go Back" aria-label="Go Back">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </button>
                            <div class="flex-1 min-w-0">
                                {!! str_replace('ph-title', 'text-lg sm:text-xl md:text-2xl font-black text-slate-900 tracking-tight leading-tight', str_replace('ph-sub', 'text-slate-500 text-xs sm:text-[13px] font-semibold mt-0.5', $header)) !!}
                            </div>
                        </div>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    @if (isset($scripts))
        {{ $scripts }}
    @endif

    @include('layouts.partials.app_scripts')

    @stack('scripts')
    @if(!auth()->check() || auth()->user()->role !== 'super_admin')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if(typeof Swal === "undefined") return; const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            @if(session('success')) Toast.fire({ icon: 'success', title: "{!! addslashes(session('success')) !!}" }); @endif
            @if(session('error')) Toast.fire({ icon: 'error', title: "{!! addslashes(session('error')) !!}" }); @endif
            @if(session('warning')) Toast.fire({ icon: 'warning', title: "{!! addslashes(session('warning')) !!}" }); @endif
            @if(session('info')) Toast.fire({ icon: 'info', title: "{!! addslashes(session('info')) !!}" }); @endif
        });
    </script>
    @endif
</body>
</html>
