<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/laundry_main_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
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
                        class="mb-6 lg:mb-8 bg-white rounded-md p-5 shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden">
                        <!-- Geometric Pattern Right Side -->
                        <div
                            class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
                            <div class="w-24 h-24 bg-blue-50 rotate-45 translate-x-12 rounded-lg"></div>
                            <div class="w-36 h-36 bg-blue-100 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
                            <div class="w-48 h-48 bg-blue-200 rotate-45 -ml-12 rounded-2xl"></div>
                            <div class="w-32 h-32 bg-blue-300 rotate-45 -ml-16 rounded-lg"></div>
                        </div>

                        <div
                            class="relative z-10 flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                            <div class="w-full">
                                {!! str_replace('ph-title', 'text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight', str_replace('ph-sub', 'text-slate-400 text-[11px] font-bold uppercase tracking-widest mt-1', $header)) !!}
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
        NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });

        document.addEventListener('click', function (e) {
            let target = e.target.closest('a');
            if (target && target.href && !target.href.startsWith('javascript:') && !target.href.includes('#') && target.target !== '_blank') {
                NProgress.start();
            }
        });

        document.addEventListener('submit', function (e) {
            NProgress.start();
        });

        const originalFetch = window.fetch;
        window.fetch = async function (...args) {
            NProgress.start();
            try {
                const response = await originalFetch.apply(this, args);
                return response;
            } finally {
                NProgress.done();
            }
        };

        window.addEventListener('load', () => NProgress.done());
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) NProgress.done();
        });

        // â”€â”€â”€ Order Quick Search Alpine Component â”€â”€â”€
        function orderSearch() {
            return {
                query: '',
                results: [],
                open: false,
                expanded: false,
                loading: false,
                async fetchResults() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch(`/orders/search?q=${encodeURIComponent(this.query)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        this.results = await res.json();
                        this.open = this.results.length > 0;
                    } catch (e) {
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },
                close() {
                    this.open = false;
                    this.expanded = false;
                    this.query = '';
                    this.results = [];
                }
            };
        }
    </script>
    @stack('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
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
</body>

</html>