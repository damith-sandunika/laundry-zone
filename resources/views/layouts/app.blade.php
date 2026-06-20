<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ config('app.name', 'SmartLaundry') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { background: #f8fafc; color: #334155; }
        h1, h2, h3, h4, h5, h6, .sidebar-brand-name, .topbar-title {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            font-weight: 800 !important;
        }
        /* Page header titles - globally overridable on mobile */
        .ph-title { font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 4px; }
        .ph-sub { font-size: 13px; color: #94a3b8; font-weight: 500; margin: 0; }
        @media (max-width: 768px) {
            .ph-title { font-size: 16px !important; letter-spacing: -0.01em !important; }
            .ph-sub { font-size: 11px !important; }
        }

        /* ── Sidebar (Premium Light Theme) ── */
        .sidebar {
            width: 220px;
            min-width: 220px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            overflow: hidden;
        }
        .sidebar-brand {
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .sidebar-brand-inner {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(30,64,175,0.15);
        }
        .sidebar-brand-name { font-size: 17px; font-weight: 900; color: #1e3a8a; letter-spacing: -0.02em; }
        .sidebar-brand-sub { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 1px; }

        /* Sidebar nav */
        .sidebar-nav {
            flex: 1;
            padding: 10px 8px;
            overflow-y: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        .nav-item {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 10px; border-radius: 10px;
            margin-bottom: 2px; cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
            color: #64748b;
            font-size: 13px; font-weight: 600;
        }
        .nav-item:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        .nav-item.active {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }
        .nav-item.active .nav-icon { color: #1d4ed8; }
        .nav-icon { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.85; }
        .nav-item.active .nav-icon { opacity: 1; }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }
        .logout-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 10px; border-radius: 10px;
            background: #fef2f2; border: 1px solid #fee2e2;
            color: #991b1b; font-size: 12px; font-weight: 700;
            cursor: pointer; transition: all 0.15s; letter-spacing: 0.03em;
            margin-bottom: 10px;
        }
        .logout-btn:hover { background: #fee2e2; color: #7f1d1d; }

        /* ── Topbar ── */
        .topbar {
            padding: 0 40px;
            height: 64px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; flex-direction: column; }
        .topbar-title { font-size: 16px; font-weight: 900; color: #0f172a; letter-spacing: -0.02em; line-height: 1; }
        .topbar-sub { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 20px; }

        .notif-btn {
            width: 38px; height: 38px; border-radius: 10px;
            background: #f8fafc; border: 1.5px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; position: relative; transition: all 0.15s;
        }
        .notif-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }
        .notif-dot {
            position: absolute; top: 7px; right: 7px;
            width: 8px; height: 8px; border-radius: 50%;
            background: #3b82f6; border: 2px solid #ffffff;
        }
        .topbar-avatar {
            width: 30px; height: 30px; border-radius: 8px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            font-size: 11.5px; font-weight: 800; color: white;
            cursor: pointer;
        }

        /* ── Page Content ── */
        .page-content { flex: 1; overflow-y: auto; padding: 36px 40px; }

        /* ── Mobile overlay ── */
        .mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 9990 !important; backdrop-filter: blur(2px); }

        @media (max-width: 900px) {
            .topbar-quick-btns { display: none !important; }
        }

        /* ── Mobile Responsiveness ── */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed !important;
                z-index: 9999 !important;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: flex !important; position: relative; z-index: 10; }
            .topbar { padding: 0 12px; height: 56px; display: flex !important; align-items: center; justify-content: space-between !important; gap: 8px; }
            /* Center brand on mobile */
            .topbar-left { position: absolute !important; left: 50%; transform: translateX(-50%) !important; display: flex !important; flex-direction: column; align-items: center; pointer-events: none; }
            .topbar-left .topbar-title { font-size: 13px !important; }
            .topbar-left .topbar-sub { font-size: 9px !important; margin-top: 1px !important; }
            .topbar-tagline { display: none !important; }
            /* Hide name, show only avatar on mobile */
            .topbar-right { display: flex; align-items: center; gap: 6px !important; }
            .topbar-right .topbar-user-info { display: none !important; }
            .topbar-right .dropdown-arrow { display: none !important; }
            .notif-btn { background: transparent !important; border: none !important; width: 40px; height: 40px; padding: 0 !important; }
            .page-content { padding: 14px 10px; }
            table { display: block; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; }
            
            /* Page title reductions */
            .page-content [style*="font-size:24px"],
            .page-content h2[style*="font-size:24px"],
            div[style*="margin-bottom:28px"] h2[style*="font-size:24px"] { font-size: 16px !important; letter-spacing: -0.01em !important; }
            div[style*="margin-bottom:28px"] p[style*="font-size:13px"] { font-size: 11px !important; }
            
            /* Dashboard specific fixes */
            .dashboard-main-grid { display: flex !important; flex-direction: column !important; }
            .dashboard-sidebar { width: 100% !important; max-width: 100% !important; margin-top: 1rem !important; }
        }
    </style>
</head>
<body x-data="{ sidebarOpen: false, notifOpen: false, profileOpen: false }">
    <div style="display:flex;height:100vh;overflow:hidden;">

        <!-- Mobile overlay -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen=false" class="mobile-overlay"></div>

        <!-- ══════════ SIDEBAR ══════════ -->
        <aside class="sidebar" :class="sidebarOpen ? 'mobile-open' : ''" style="flex-shrink:0;">

            <!-- Brand, Logo & User Info at Top -->
            <div class="sidebar-brand" style="display:flex; flex-direction:column; align-items:center; text-align:center; gap:4px;">
                
                <!-- ADMIN DASHBOARD LOGO: Replace public/images/admin_logo.png with your file -->
                <div style="width:100%; display:flex; justify-content:center; padding: 4px 0 6px;">
                    <img src="{{ asset('images/admin_logo.png') }}" alt="Admin Logo" style="max-height: 90px; max-width: 190px; object-fit: contain;" onerror="this.style.display='none'">
                </div>

                <div>
                    <div class="sidebar-brand-name">Smart Laundry</div>
                    <div class="sidebar-user-name" style="font-size:13.5px; font-weight:700; color:#0f172a; margin-top:3px; line-height:1.2;">{{ auth()->user()->name ?? 'Administrator' }}</div>
                    <div class="sidebar-user-role" style="font-size:10px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.06em; margin-top:2px;">{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'admin')) }}</div>
                </div>
            </div>

            <!-- Nav (Tabs one after another cleanly, section labels removed) -->
            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>

                <a href="{{ route('pos.index') }}" class="nav-item {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    POS System
                </a>

                <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Orders
                </a>

                <a href="{{ route('online_bookings.index') }}" class="nav-item {{ request()->routeIs('online_bookings.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    Online Bookings
                </a>

                <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Customers
                </a>

                <a href="{{ route('expenses.index') }}" class="nav-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Expenses
                </a>

                <a href="{{ route('inventory.index') }}" class="nav-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Inventory
                </a>

                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                    @if(Route::has('services.index'))
                    <a href="{{ route('services.index') }}" class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Services
                    </a>
                    @endif

                    <a href="{{ route('staff.index') }}" class="nav-item {{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Staff Manager
                    </a>

                    <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Reports
                    </a>

                    <a href="{{ route('whatsapp_templates.index') }}" class="nav-item {{ request()->routeIs('whatsapp_templates.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        WhatsApp Msgs
                    </a>

                    <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                @endif
            </nav>

            <!-- Footer (Sign Out & Software by DSRZone) -->
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
                </form>
                <div style="font-size:10px; font-weight:700; color:#94a3b8; letter-spacing:0.02em; padding-bottom:8px;">Software by DSRZone</div>
            </div>
        </aside>

        <!-- ══════════ MAIN AREA ══════════ -->
        <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0;">

            <!-- Topbar (Search bar removed, tagline in desktop view, notification & profile dropdowns) -->
            <header class="topbar">
                <!-- LEFT: Hamburger -->
                <button @click="sidebarOpen=!sidebarOpen" style="display:none;width:40px;height:40px;background:transparent;border:none;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0;" class="mobile-menu-btn">
                    <svg width="26" height="26" fill="none" stroke="#0f172a" stroke-width="2.5"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <!-- CENTER: Brand (desktop: topbar-left, mobile: absolute center) -->
                <div class="topbar-left" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                    @php 
                        $branchId = auth()->user()->branch_id ?? 1; 
                        $branch = \App\Models\Branch::find($branchId); 
                    @endphp
                    <div class="topbar-title" style="font-size:15px;font-weight:900;color:#0f172a;letter-spacing:-0.02em;line-height:1;">{{ $branch->name ?? 'Urban Wash' }}</div>
                    <div class="topbar-sub" style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.1em;margin-top:2px;">Laundry Management</div>
                </div>

                <div style="display:flex;align-items:center;gap:12px;margin-left:auto;margin-right:24px;" class="topbar-quick-btns">
                    <a href="{{ route('customers.index') }}" style="background:#ef4444;color:white;padding:8px 16px;border-radius:20px;font-size:12.5px;font-weight:800;text-decoration:none;display:flex;flex-direction:row;align-items:center;gap:4px;white-space:nowrap;box-shadow:0 4px 10px rgba(239,68,68,0.25);transition:all 0.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <span style="font-size:16px;line-height:1;margin-bottom:2px;">+</span> New Customer
                    </a>
                    <a href="{{ route('pos.index') }}" style="background:#1d4ed8;color:white;padding:8px 16px;border-radius:20px;font-size:12.5px;font-weight:800;text-decoration:none;display:flex;flex-direction:row;align-items:center;gap:4px;white-space:nowrap;box-shadow:0 4px 10px rgba(29,78,216,0.25);transition:all 0.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <span style="font-size:16px;line-height:1;margin-bottom:2px;">+</span> New Order
                    </a>
                </div>

                <div class="topbar-right">
                    <!-- Notification Button with Dropdown (Low Stock & New Orders Alert) -->
                    @php
                        $notifsList = [];
                        $lowStockAlerts = \App\Models\InventoryItem::whereRaw('current_stock <= min_stock')->get();
                        foreach ($lowStockAlerts as $item) {
                            $notifsList[] = [
                                'title' => 'Low Stock: ' . $item->name . ' is low (' . $item->current_stock . ' ' . $item->unit . ' left)',
                                'time' => 'System Alert',
                                'unread' => true,
                                'type' => 'alert'
                            ];
                        }
                        $pendingBookings = \App\Models\OnlineBooking::where('status', 'pending')->latest()->take(3)->get();
                        foreach ($pendingBookings as $b) {
                            $notifsList[] = [
                                'title' => 'New booking request from ' . $b->customer_name,
                                'time' => $b->created_at->diffForHumans(),
                                'unread' => true,
                                'type' => 'booking'
                            ];
                        }
                        $recentOrdersCount = \App\Models\Order::where('created_at', '>=', now()->subHours(2))->count();
                        if ($recentOrdersCount > 0) {
                            $notifsList[] = [
                                'title' => $recentOrdersCount . ' new orders placed recently',
                                'time' => 'Just now',
                                'unread' => true,
                                'type' => 'order'
                            ];
                        }
                    @endphp
                    <div style="position:relative;">
                        <button @click="notifOpen = !notifOpen; profileOpen = false" @click.away="notifOpen = false" class="notif-btn" style="display:flex;align-items:center;justify-content:center;">
                            @if(count($notifsList) > 0)
                                <div class="notif-dot" style="top:4px; right:4px;"></div>
                            @endif
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </button>
                        <!-- Notifications Dropdown Menu -->
                        <div x-show="notifOpen" style="display:none; position:absolute; right:0; top:46px; width:300px; background:white; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.08); z-index:100; overflow:hidden;">
                            <div style="padding:12px 16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:12px; font-weight:800; color:#0f172a;">Notifications</span>
                                <span style="font-size:10px; font-weight:700; color:#1d4ed8; background:#eff6ff; padding:2px 6px; border-radius:4px;">{{ count($notifsList) }} New</span>
                            </div>
                            <div style="max-height:240px; overflow-y:auto;">
                                @forelse($notifsList as $n)
                                    <div style="padding:12px 16px; border-bottom:1px solid #f8fafc; display:flex; gap:10px; align-items:flex-start;">
                                        <span style="margin-top:2px;">
                                            @if(($n['type'] ?? '') === 'alert')
                                                <svg width="14" height="14" fill="none" stroke="#ef4444" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            @else
                                                <svg width="14" height="14" fill="none" stroke="#1d4ed8" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @endif
                                        </span>
                                        <div style="text-align:left;">
                                            <p style="font-size:11.5px; font-weight:600; color:#334155; margin:0; line-height:1.3;">{{ $n['title'] }}</p>
                                            <span style="font-size:9.5px; color:#94a3b8; font-weight:500;">{{ $n['time'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div style="padding:24px; text-align:center; font-size:12px; color:#94a3b8; font-weight:500;">No new notifications.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div style="position:relative;">
                        <button @click="profileOpen = !profileOpen; notifOpen = false" @click.away="profileOpen = false" style="background:none;border:none;cursor:pointer;display:flex;align-items:center;padding:4px;border-radius:50%;transition:all 0.15s;">
                            <!-- Desktop: avatar + name -->
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="topbar-avatar" style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#1d4ed8,#3b82f6);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:white;">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="topbar-user-info" style="font-size:13px;font-weight:700;color:#334155;white-space:nowrap;">{{ auth()->user()->name ?? 'User' }}</span>
                                <svg class="dropdown-arrow" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </button>
                        <!-- Profile Dropdown Menu -->
                        <div x-show="profileOpen" style="display:none; position:absolute; right:0; top:46px; width:170px; background:white; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.08); z-index:100; overflow:hidden;">
                            <a href="{{ route('profile.edit') }}" style="display:flex; align-items:center; gap:8px; padding:12px 16px; font-size:12.5px; font-weight:700; color:#334155; text-decoration:none; border-bottom:1px solid #f1f5f9; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button type="submit" style="display:flex; align-items:center; gap:8px; width:100%; padding:12px 16px; border:none; background:none; text-align:left; font-size:12.5px; font-weight:700; color:#dc2626; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                @if (isset($header))
                    <div style="margin-bottom:28px;">{{ $header }}</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    @if (isset($scripts))
        {{ $scripts }}
    @endif

    <style>
        @media (max-width: 1023px) {
            .sidebar { position: fixed; top: 0; left: 0; z-index: 50; transform: translateX(-100%); transition: transform 0.25s ease; }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-overlay { display: block; }
            .mobile-menu-btn { display: flex !important; }
            .topbar-tagline { display: none !important; }
            .topbar-user-info { display: none !important; }
            .page-content { padding: 24px 20px; }
            .topbar { padding: 0 20px; }
        }
    </style>
</body>
</html>
