<!-- ══════════ SUPER ADMIN TOPBAR ══════════ -->
<header class="py-2 lg:py-0 px-3 lg:px-8 bg-white/70 backdrop-blur-xl border-b border-slate-200/60 flex flex-col lg:flex-row lg:items-center justify-between sticky top-0 z-30 shadow-sm gap-y-3 lg:gap-y-0 lg:h-16">

    <!-- Left Section: Mobile Toggle & Brand Navigation -->
    <div class="flex items-center justify-between w-full lg:w-auto relative">
        <!-- Mobile Toggle Button -->
        <button @click="sidebarOpen = !sidebarOpen"
            class="p-1.5 sm:p-2 -ml-1 sm:-ml-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden transition-colors flex-shrink-0 z-10"
            title="Open Menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Brand / Path (Centered on mobile, left on desktop) -->
        <div class="absolute left-1/2 -translate-x-1/2 lg:static lg:transform-none flex flex-col text-center lg:text-left pointer-events-none lg:pointer-events-auto">
            <div class="flex items-center justify-center lg:justify-start gap-2">
                <div class="text-[14px] lg:text-[15px] font-bold text-slate-800 tracking-tight leading-none">
                    {{ \App\Models\Central\PlatformSetting::get('platform_name', 'Laundry Zone') }}
                </div>
            </div>
            <div class="text-[9px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] mt-1">
                SaaS Admin Central Cloud Portal
            </div>
        </div>

        <!-- Mobile Profile Avatar (only shown on mobile) -->
        <div class="relative lg:hidden z-10" x-data="{ mobileProfileOpen: false }">
            <button @click="mobileProfileOpen = !mobileProfileOpen"
                class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-xs font-black text-white shadow-md">
                SA
            </button>
            <div x-show="mobileProfileOpen" @click.away="mobileProfileOpen = false" x-cloak
                class="absolute right-0 top-10 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                <a href="{{ route('super_admin.settings.index') }}" class="block px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Platform Settings</a>
                <form method="POST" action="{{ route('super_admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50">Sign Out</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Side: Engine Status & Desktop Profile Dropdown -->
    <div class="flex flex-row items-center w-full lg:w-auto justify-between lg:justify-end gap-2 sm:gap-4 ml-auto border-t border-slate-100/80 lg:border-none pt-2 lg:pt-0">
        <!-- Central Domain Badge -->
        <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100/80 border border-slate-200/80 text-[11px] font-bold text-slate-600">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <span>{{ config('tenancy.central_domain', 'localhost') }}</span>
        </div>

        <!-- Engine Status Live Pill -->
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200/80 text-[11px] font-bold text-emerald-700 shadow-sm">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="tracking-wide">Multi-Tenant Live</span>
        </div>

        <!-- Desktop Profile Menu -->
        <div class="hidden lg:relative lg:block" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
            <button @click="profileOpen = !profileOpen"
                class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-100/80 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-xs font-black text-white shadow-md">
                    SA
                </div>
                <div class="text-left">
                    <div class="text-[12px] font-extrabold text-slate-800 leading-tight">SaaS Admin</div>
                    <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Platform Owner</div>
                </div>
                <svg class="w-4 h-4 text-slate-400 ml-1 transition-transform" :class="{ 'rotate-180': profileOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="profileOpen" x-transition.opacity.scale.95 x-cloak
                class="absolute right-0 top-12 w-52 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-100">
                    <div class="text-[12px] font-extrabold text-slate-800">SaaS Administrator</div>
                    <div class="text-[11px] text-slate-400 truncate">saasadmin@laundryzone.com</div>
                </div>
                <a href="{{ route('super_admin.settings.index') }}"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    Platform Settings
                </a>
                <form method="POST" action="{{ route('super_admin.logout') }}" class="m-0 border-t border-slate-100">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2.5 w-full px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50/70 transition">
                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
