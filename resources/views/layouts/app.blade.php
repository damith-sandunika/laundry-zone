@php
    $tenant = \App\Services\Tenancy\TenantManager::getTenant();
    $companyName = $tenant ? $tenant->name : \App\Models\Setting::get('company_name', 'Laundry Zone');
    $defaultLogo = asset('images/laundry_main_logo.png');
    $companyLogo = ($tenant && !empty($tenant->logo_path)) 
        ? asset($tenant->logo_path) 
        : asset(\App\Models\Setting::get('company_logo', 'images/laundry_main_logo.png'));
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
    <link rel="icon" type="image/png" href="{{ $defaultLogo }}">
    <link rel="apple-touch-icon" href="{{ $defaultLogo }}">
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
        #nprogress .bar {
            background: #3b82f6 !important;
            height: 3px !important;
        }

        #nprogress .peg {
            box-shadow: 0 0 10px #3b82f6, 0 0 5px #3b82f6 !important;
        }

        #nprogress .spinner {
            display: none !important;
        }

        [x-cloak] {
            display: none !important;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @include('layouts.partials.gtm')
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-100 selection:text-blue-900"
    x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', notifOpen: false, profileOpen: false }"
    x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
    @include('layouts.partials.gtm-body')
    <div class="flex h-screen overflow-hidden bg-slate-50/50">

        <style>
            /* Collapsed Sidebar Styles */
            aside.sidebar-collapsed {
                width: 5rem !important;
                /* w-20 */
            }

            aside.sidebar-collapsed .sidebar-header {
                padding: 1rem 0.5rem !important;
            }

            aside.sidebar-collapsed .sidebar-logo-btn {
                width: 3rem !important;
                height: 3rem !important;
                border-radius: 0.75rem !important;
                margin-bottom: 0 !important;
                box-shadow: 0 0 0 2px #fff !important;
            }

            aside.sidebar-collapsed .sidebar-logo-img {
                max-width: 32px !important;
                max-height: 32px !important;
            }

            aside.sidebar-collapsed .sidebar-brand-text {
                display: none !important;
            }

            aside.sidebar-collapsed .sidebar-footer {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            aside.sidebar-collapsed .sidebar-footer button {
                padding: 0.75rem !important;
                border-radius: 9999px !important;
                justify-content: center !important;
                font-size: 0px !important;
                color: transparent !important;
                gap: 0 !important;
            }
            
            aside.sidebar-collapsed .sidebar-footer button svg {
                width: 1.25rem !important;
                height: 1.25rem !important;
                margin: 0 !important;
                flex-shrink: 0 !important;
            }
            
            aside.sidebar-collapsed .sidebar-footer button span {
                display: none !important;
            }

            aside.sidebar-collapsed nav {
                overflow-x: hidden;
            }

            aside.sidebar-collapsed nav a,
            aside.sidebar-collapsed nav button {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                justify-content: center;
            }

            aside.sidebar-collapsed nav a svg,
            aside.sidebar-collapsed nav button svg {
                margin: 0 !important;
            }

            /* Hide the text nodes in the nav items */
            aside.sidebar-collapsed nav a,
            aside.sidebar-collapsed nav button {
                font-size: 0px !important;
                color: transparent !important;
                gap: 0 !important;
                white-space: nowrap;
            }

            aside.sidebar-collapsed nav a svg,
            aside.sidebar-collapsed nav button svg {
                color: #64748b;
                /* slate-500 */
                flex-shrink: 0;
            }

            aside.sidebar-collapsed nav a.bg-blue-50 svg,
            aside.sidebar-collapsed nav button.bg-blue-50 svg {
                color: #1d4ed8;
                /* blue-700 */
            }

            /* Hide dropdown arrows in collapsed state */
            aside.sidebar-collapsed nav button svg.transition-transform {
                display: none !important;
            }
        </style>

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen=false"
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

        @include('layouts.partials.sidebar')

        <!-- â•â•â•â•â•â•â•â•â•â• MAIN AREA â•â•â•â•â•â•â•â•â•â• -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

            @include('layouts.partials.topbar')

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 animate-fade-in relative z-10 scrollbar-hide">
                @if (isset($header))
                    <div
                        class="mb-6 lg:mb-8 bg-white rounded-xl p-3.5 sm:p-4 md:p-5 shadow-sm border border-slate-200/90 relative flex items-center justify-between gap-3 md:gap-4 overflow-hidden">
                        <!-- Subtle Geometric Accent Right Side matching Saman Hardware PageHeaderBanner -->
                        <div
                            class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
                            <div class="w-24 h-24 bg-blue-50/80 rotate-45 translate-x-12 rounded-lg"></div>
                            <div class="w-36 h-36 bg-blue-100/70 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
                            <div class="w-48 h-48 bg-blue-200/50 rotate-45 -ml-12 rounded-2xl"></div>
                            <div class="w-32 h-32 bg-blue-300/40 rotate-45 -ml-16 rounded-lg"></div>
                        </div>

                        <!-- Header Container with Back Button -->
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

    <script>
        const np = (typeof NProgress !== 'undefined') ? NProgress : { start: () => {}, done: () => {}, configure: () => {} };
        np.configure({ showSpinner: false, speed: 400, minimum: 0.1 });

        document.addEventListener('click', function (e) {
            let target = e.target.closest('a');
            if (target && target.href && !target.href.startsWith('javascript:') && !target.href.includes('#') && target.target !== '_blank') {
                np.start();
            }
        });

        // ─── Universal Button Lock & Anti-Double-Submit System ───
        function lockElement(el) {
            if (!el || el.dataset.locked === 'true') return;
            el.dataset.locked = 'true';
            el.dataset.prevPointerEvents = el.style.pointerEvents || '';
            el.dataset.prevOpacity = el.style.opacity || '';
            el.dataset.prevCursor = el.style.cursor || '';
            
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.6';
            el.style.cursor = 'wait';
            if (el.tagName === 'BUTTON' || el.tagName === 'INPUT') {
                el.disabled = true;
            }
        }

        function unlockElement(el) {
            if (!el || el.dataset.locked !== 'true') return;
            el.dataset.locked = 'false';
            el.style.pointerEvents = el.dataset.prevPointerEvents || '';
            el.style.opacity = el.dataset.prevOpacity || '';
            el.style.cursor = el.dataset.prevCursor || '';
            if (el.tagName === 'BUTTON' || el.tagName === 'INPUT') {
                el.disabled = false;
            }
        }

        function unlockAll() {
            document.querySelectorAll('[data-locked="true"]').forEach(unlockElement);
            document.querySelectorAll('form[data-submitting="true"]').forEach(f => f.dataset.submitting = 'false');
            np.done();
        }

        let lastActionEl = null;
        let lastActionTime = 0;

        document.addEventListener('click', function (e) {
            let link = e.target.closest('a');
            if (link && link.href && !link.href.startsWith('javascript:') && !link.href.includes('#') && link.target !== '_blank') {
                np.start();
            }

            const actionEl = e.target.closest('button, [type="submit"], a.btn, a[data-action]');
            if (!actionEl) return;

            const now = Date.now();
            if (actionEl === lastActionEl && (now - lastActionTime) < 400) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }

            lastActionEl = actionEl;
            lastActionTime = now;
        }, true);

        document.addEventListener('submit', function (e) {
            const form = e.target;
            
            if (form.checkValidity && !form.checkValidity()) {
                return;
            }

            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
            form.dataset.submitting = 'true';
            
            const btns = form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type="button"]):not([type="reset"])');
            btns.forEach(btn => lockElement(btn));
            
            np.start();

            setTimeout(() => {
                if (form.dataset.submitting === 'true') {
                    form.dataset.submitting = 'false';
                    btns.forEach(btn => unlockElement(btn));
                    np.done();
                }
            }, 12000);
        });

        const originalFetch = window.fetch;
        window.fetch = async function (...args) {
            const triggeringEl = lastActionEl || document.activeElement;
            const shouldLock = triggeringEl && (triggeringEl.tagName === 'BUTTON' || triggeringEl.tagName === 'A' || triggeringEl.getAttribute('role') === 'button');
            
            if (shouldLock) {
                if (triggeringEl.dataset.locked === 'true') {
                    return new Promise(() => {});
                }
                lockElement(triggeringEl);
            }
            
            np.start();
            try {
                const response = await originalFetch.apply(this, args);
                return response;
            } finally {
                if (shouldLock) {
                    setTimeout(() => unlockElement(triggeringEl), 300);
                }
                np.done();
            }
        };

        if (typeof window.axios !== 'undefined') {
            window.axios.interceptors.request.use((config) => {
                const triggeringEl = lastActionEl || document.activeElement;
                if (triggeringEl && (triggeringEl.tagName === 'BUTTON' || triggeringEl.tagName === 'A')) {
                    lockElement(triggeringEl);
                    config.__lockedEl = triggeringEl;
                }
                np.start();
                return config;
            }, (error) => {
                np.done();
                return Promise.reject(error);
            });

            window.axios.interceptors.response.use((response) => {
                if (response.config && response.config.__lockedEl) {
                    setTimeout(() => unlockElement(response.config.__lockedEl), 300);
                }
                np.done();
                return response;
            }, (error) => {
                if (error.config && error.config.__lockedEl) {
                    setTimeout(() => unlockElement(error.config.__lockedEl), 300);
                }
                np.done();
                return Promise.reject(error);
            });
        }

        window.addEventListener('load', () => unlockAll());
        window.addEventListener('pageshow', (e) => unlockAll());

        // ─── Order Quick Search Alpine Component ───
        function orderSearch() {
            return {
                query: '',
                results: [],
                total: 0,
                hasMore: false,
                open: false,
                loading: false,
                loadingMore: false,
                page: 1,
                async fetchResults() {
                    const q = this.query.trim();
                    if (q.length < 1) {
                        this.results = [];
                        this.total = 0;
                        this.hasMore = false;
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    this.page = 1;
                    try {
                        const res = await fetch(`/orders/search?q=${encodeURIComponent(q)}&limit=5&page=1`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        this.results = Array.isArray(data) ? data : (data.items || []);
                        this.total = data.total || this.results.length;
                        this.hasMore = data.has_more || false;
                        this.open = this.results.length > 0;
                    } catch (e) {
                        this.results = [];
                        this.hasMore = false;
                    } finally {
                        this.loading = false;
                    }
                },
                async loadMore() {
                    if (this.loadingMore || !this.hasMore) return;
                    this.loadingMore = true;
                    try {
                        const nextPage = this.page + 1;
                        const res = await fetch(`/orders/search?q=${encodeURIComponent(this.query.trim())}&limit=5&page=${nextPage}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();
                        const nextItems = Array.isArray(data) ? data : (data.items || []);
                        if (nextItems.length > 0) {
                            this.results = [...this.results, ...nextItems];
                            this.page = nextPage;
                            this.hasMore = data.has_more || false;
                        } else {
                            this.hasMore = false;
                        }
                    } catch (e) {
                        // error handled gracefully
                    } finally {
                        this.loadingMore = false;
                    }
                },
                close() {
                    this.open = false;
                    this.query = '';
                    this.results = [];
                    this.total = 0;
                    this.hasMore = false;
                }
            };
        }
    </script>
    @stack('scripts')
    @if(!auth()->check() || auth()->user()->role !== 'super_admin')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if(typeof Swal === "undefined") return; const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{!! addslashes(session('success')) !!}"
                });
            @endif

            @if(session('error'))
                Toast.fire({
                    icon: 'error',
                    title: "{!! addslashes(session('error')) !!}"
                });
            @endif

            @if(session('warning'))
                Toast.fire({
                    icon: 'warning',
                    title: "{!! addslashes(session('warning')) !!}"
                });
            @endif

            @if(session('info'))
                Toast.fire({
                    icon: 'info',
                    title: "{!! addslashes(session('info')) !!}"
                });
            @endif
        });
    </script>
    @endif
</body>

</html>
