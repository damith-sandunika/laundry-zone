{{-- 1. Main Dashboard --}}
<a href="{{ route('super_admin.dashboard') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7" rx="1" />
        <rect x="14" y="3" width="7" height="7" rx="1" />
        <rect x="3" y="14" width="7" height="7" rx="1" />
        <rect x="14" y="14" width="7" height="7" rx="1" />
    </svg>
    <span>Dashboard</span>
</a>

{{-- 2. Companies / Tenants --}}
<a href="{{ route('super_admin.companies.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.companies.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
    <span>Companies</span>
</a>

{{-- 3. Tenant Owners --}}
<a href="{{ route('super_admin.owners.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.owners.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    <span>Tenant Owners</span>
</a>

{{-- 4. Databases --}}
<a href="{{ route('super_admin.databases.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.databases.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
    </svg>
    <span>Databases</span>
</a>

{{-- 5. Subscriptions / Billing --}}
<a href="{{ route('super_admin.payments.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.payments.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span>Subscriptions</span>
</a>

{{-- 6. SMS Packages & Top-Ups --}}
<a href="{{ route('super_admin.sms_packages.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.sms_packages.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
    <span>SMS & Bundles</span>
</a>

{{-- 7. Platform Settings --}}
<a href="{{ route('super_admin.settings.index') }}"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('super_admin.settings.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    <span>Platform Settings</span>
</a>
