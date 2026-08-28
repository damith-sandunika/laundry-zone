<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name', '360 Laundry') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/admin_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
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
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-100 selection:text-blue-900" x-data="{ sidebarOpen: false, notifOpen: false, profileOpen: false }">
    <div class="flex h-screen overflow-hidden bg-slate-50/50">

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen=false" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" x-cloak></div>

        <!-- ══════════ SIDEBAR ══════════ -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 transition-transform duration-300 ease-in-out bg-white/80 backdrop-blur-xl border-r border-slate-200/60 lg:static lg:translate-x-0">
            
            <!-- Brand & Logo -->
            <div class="flex flex-col items-center text-center p-6 border-b border-slate-100/80 bg-slate-50/50">
                <div class="w-20 h-20 rounded-[1.25rem] bg-white flex items-center justify-center shadow-xl shadow-blue-500/10 mb-3 ring-4 ring-white transition-transform hover:scale-105 duration-300">
                    <img src="{{ asset('images/360_laundry_logo.png') }}" alt="Admin Logo" class="max-w-[52px] max-h-[52px] object-contain" onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="text-xl font-black text-slate-900 tracking-tight leading-tight">360 Laundry</div>
                    <div class="text-[10px] font-extrabold text-blue-600 uppercase tracking-widest mt-1">Management System</div>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
                @php $role = auth()->user()->role; @endphp

                {{-- Dashboard: All roles --}}
                <a href="{{ route($role === 'rider' ? 'rider.dashboard' : ($role === 'workshop_admin' ? 'workshop.dashboard' : 'dashboard')) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('dashboard') || request()->routeIs('rider.dashboard') || request()->routeIs('workshop.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>

                {{-- ── SUPER ADMIN ── --}}
                @if($role === 'super_admin')
                    <a href="{{ route('branches.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('branches.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Branches
                    </a>
                    <a href="{{ route('staff.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('staff.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        All Users
                    </a>
                    <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Global Reports
                    </a>
                    <a href="{{ route('whatsapp_templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('whatsapp_templates.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        WhatsApp Msgs
                    </a>
                    <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('hr.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        HR Dashboard
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.index') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Global Settings
                    </a>
                @endif

                {{-- ── RIDER ── --}}
                @if($role === 'rider')
                    <a href="{{ route('rider.tasks') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('rider.tasks') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        My Tasks
                    </a>
                @endif

                {{-- ── WORKSHOP ADMIN ── --}}
                @if($role === 'workshop_admin')
                    <a href="{{ route('delivery.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('delivery.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        Deliveries
                    </a>
                    <a href="{{ route('workshop.operations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('workshop.operations') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Operations
                    </a>
                    <a href="{{ route('workshop.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('workshop.history') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Completed History
                    </a>
                    <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('inventory.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventory
                    </a>
                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Expenses
                    </a>
                @endif

                {{-- ── BRANCH ADMIN / ADMIN / STAFF ── --}}
                @if(in_array($role, ['branch_admin', 'admin', 'staff']))
                    <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('pos.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        POS System
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('orders.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Orders
                    </a>
                    
                    <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('customers.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Customers
                    </a>
                    <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('packages.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Packages
                    </a>
                    <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('expenses.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Expenses
                    </a>

                    @if(in_array($role, ['branch_admin', 'admin']))
                        <a href="{{ route('staff.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('staff.*') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Staff Manager
                        </a>
                    @endif

                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('settings.index') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Settings
                        </a>
                @endif
            </nav>

            <!-- Footer -->
            <div class="p-5 border-t border-slate-100/80 text-center bg-white/40">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center justify-center gap-2 w-full p-2.5 rounded-xl bg-red-50/50 border border-red-100 text-red-600 hover:text-red-700 hover:bg-red-100 hover:border-red-200 transition-all duration-200 text-xs font-bold tracking-wide shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
                </form>
                <div class="text-[10px] font-extrabold text-slate-400/80 tracking-widest mt-4 uppercase">Powered by DSRZone</div>
            </div>
        </aside>

        <!-- ══════════ MAIN AREA ══════════ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">

            <!-- Topbar -->
            <header class="py-2 lg:py-0 px-3 lg:px-8 bg-white/70 backdrop-blur-xl border-b border-slate-200/60 flex flex-col lg:flex-row lg:items-center justify-between sticky top-0 z-30 shadow-sm gap-y-3 lg:gap-y-0 lg:h-16">
                
                <!-- Top Row (Mobile & Desktop) -->
                <div class="flex items-center justify-between w-full lg:w-auto relative">
                    <!-- Mobile Toggle -->
                    <button @click="sidebarOpen=!sidebarOpen" class="p-1.5 sm:p-2 -ml-1 sm:-ml-2 rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden transition-colors flex-shrink-0 z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <!-- Title (Centered on mobile, left on desktop) -->
                    <div class="absolute left-1/2 -translate-x-1/2 lg:static lg:transform-none flex flex-col text-center lg:text-left pointer-events-none lg:pointer-events-auto">
                        @php 
                            $branchId = auth()->user()->branch_id ?? 1; 
                            $branch = \App\Models\Branch::find($branchId); 
                        @endphp
                        <div class="flex items-center justify-center lg:justify-start gap-2">
                            @if(auth()->user()->role === 'rider')
                                <div class="text-[14px] lg:text-[15px] font-bold text-slate-800 tracking-tight leading-none">Rider Dashboard</div>
                            @else
                                <div class="text-[14px] lg:text-[15px] font-bold text-slate-800 tracking-tight leading-none">{{ $branch->name ?? '360 Laundry' }}</div>
                            @endif
                            
                            <!-- Global WhatsApp Status Indicator -->
                            <div x-data="{ waStatus: 'checking' }" 
                                 x-init="fetch('{{ route('whatsapp.status') }}').then(r=>r.json()).then(d=>{ waStatus = d.connected ? 'connected' : 'disconnected' }).catch(()=>{ waStatus='disconnected' })"
                                 class="pointer-events-auto cursor-help"
                                 :title="waStatus === 'connected' ? 'WhatsApp Connected' : 'WhatsApp Disconnected'">
                                
                                <!-- Connected Icon -->
                                <svg x-show="waStatus === 'connected'" x-cloak class="w-[16px] h-[16px] text-green-500 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                
                                <!-- Disconnected Icon -->
                                <svg x-show="waStatus === 'disconnected'" x-cloak class="w-[16px] h-[16px] text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-[9px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-[0.15em] mt-1">Laundry Management</div>
                    </div>

                    <!-- Profile Dropdown (Mobile only in this row, hidden on desktop) -->
                    <div class="relative lg:hidden z-10" x-data="{ profileOpenMobile: false }">
                        <button @click="profileOpenMobile = !profileOpenMobile" @click.away="profileOpenMobile = false" class="flex items-center p-1 rounded-full hover:bg-slate-100 transition-colors">
                            <div class="w-[30px] h-[30px] rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-[12px] font-black text-white shadow-md">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </button>
                        <!-- Profile Menu -->
                        <div x-show="profileOpenMobile" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="absolute right-0 top-[42px] w-[180px] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-[60] origin-top-right" x-cloak>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-3 text-[12.5px] font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 transition-colors pointer-events-auto">
                                <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0 pointer-events-auto">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-3 text-[12.5px] font-bold text-red-600 hover:bg-red-50/50 hover:text-red-700 transition-colors text-left">
                                    <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right Side Actions (2nd Row on Mobile) -->
                <div class="flex flex-row items-center w-full lg:w-auto justify-between lg:justify-end gap-1.5 sm:gap-3 lg:gap-6 ml-auto border-t border-slate-100/80 lg:border-none pt-2 lg:pt-0">
                    {{-- Order Quick Search Box --}}
                    @if(in_array(auth()->user()->role ?? '', ['branch_admin', 'admin', 'staff', 'super_admin', 'workshop_admin']))
                    <div class="block relative" x-data="orderSearch()" @click.away="close()">
                        <div class="relative">
                            <svg class="absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 w-3.5 sm:w-4 h-3.5 sm:h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                            <input
                                type="text"
                                x-model="query"
                                @input.debounce.300ms="fetchResults()"
                                @focus="if(results.length > 0) open = true"
                                @keydown.escape="close()"
                                placeholder="Search..."
                                class="w-24 sm:w-52 pl-7 sm:pl-9 pr-2 sm:pr-4 py-1.5 sm:py-2 text-[11px] sm:text-[12.5px] font-semibold bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:bg-white focus:w-36 sm:focus:w-72 transition-all duration-300 placeholder-slate-400"
                            >
                            <div x-show="loading" x-cloak class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-slate-400 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </div>
                        </div>

                        {{-- Dropdown Results --}}
                        <div x-show="open && results.length > 0" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute right-0 top-[calc(100%+6px)] w-80 bg-white border border-slate-200 rounded-2xl shadow-2xl z-50 overflow-hidden">
                            <div class="px-3 py-2 border-b border-slate-100 bg-slate-50/60">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Quick Results</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto">
                                <template x-for="order in results" :key="order.id">
                                    <a :href="order.url" @click="close()" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-blue-50 border-b border-slate-50 last:border-0 transition-colors group">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-[13px] font-bold text-slate-800 group-hover:text-blue-700" x-text="order.order_number"></div>
                                                <div class="text-[11px] text-slate-400 font-medium truncate" x-text="order.customer"></div>
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0">
                                            <div class="text-[12px] font-black text-slate-700" x-text="'LKR ' + order.total"></div>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                                                :class="{
                                                    'bg-green-100 text-green-700': order.status === 'done' || order.status === 'delivered',
                                                    'bg-blue-100 text-blue-700': order.status === 'received',
                                                    'bg-purple-100 text-purple-700': order.status === 'processing' || order.status === 'dispatched_to_workshop',
                                                    'bg-amber-100 text-amber-700': order.status === 'returned_from_workshop',
                                                    'bg-slate-100 text-slate-500': order.status === 'cancelled',
                                                }"
                                                x-text="order.status_label">
                                            </span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <div x-show="query.length >= 2 && results.length === 0 && !loading" x-cloak class="px-4 py-5 text-center text-[12px] font-semibold text-slate-400">
                                No orders found.
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notifications Dropdown -->
                    <x-alert-system />

                    <!-- Profile Dropdown (Desktop) -->
                    <div class="relative hidden lg:block">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" @click.away="profileOpen = false" class="flex items-center p-1 rounded-full hover:bg-slate-100 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <div class="w-[34px] h-[34px] rounded-full bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-[13px] font-black text-white shadow-md">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="hidden lg:block text-[13px] font-bold text-slate-700">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="hidden lg:block w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        
                        <!-- Profile Menu -->
                        <div x-show="profileOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 top-[52px] w-[180px] bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl overflow-hidden z-50 origin-top-right" x-cloak>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-3 text-[12.5px] font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 transition-colors">
                                <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-3 text-[12.5px] font-bold text-red-600 hover:bg-red-50/50 hover:text-red-700 transition-colors text-left">
                                    <svg class="w-[14px] h-[14px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 animate-fade-in relative z-10 scrollbar-hide">
                @if (isset($header))
                    <div class="mb-6 lg:mb-8 bg-white rounded-md p-5 shadow-sm border border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative overflow-hidden">
                        <!-- Geometric Pattern Right Side -->
                        <div class="absolute right-0 top-1/2 -translate-y-1/2 flex items-center pointer-events-none opacity-60">
                            <div class="w-24 h-24 bg-blue-50 rotate-45 translate-x-12 rounded-lg"></div>
                            <div class="w-36 h-36 bg-blue-100 rotate-45 translate-x-6 -ml-8 rounded-xl"></div>
                            <div class="w-48 h-48 bg-blue-200 rotate-45 -ml-12 rounded-2xl"></div>
                            <div class="w-32 h-32 bg-blue-300 rotate-45 -ml-16 rounded-lg"></div>
                        </div>
                        
                        <div class="relative z-10 flex-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                            <div class="w-full">{!! str_replace('ph-title', 'text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight', str_replace('ph-sub', 'text-slate-400 text-[11px] font-bold uppercase tracking-widest mt-1', $header)) !!}</div>
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
        
        document.addEventListener('click', function(e) {
            let target = e.target.closest('a');
            if (target && target.href && !target.href.startsWith('javascript:') && !target.href.includes('#') && target.target !== '_blank') {
                NProgress.start();
            }
        });

        document.addEventListener('submit', function(e) {
            NProgress.start();
        });

        const originalFetch = window.fetch;
        window.fetch = async function(...args) {
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

        // ─── Order Quick Search Alpine Component ───
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
                    } catch(e) {
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
</body>
</html>
