@php
    $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';
    $isGlobalManager = auth()->user() && auth()->user()->canManageGlobalConfig();
    $isSuperAdmin = auth()->user() && auth()->user()->role === 'super_admin';
    $canManageConfig = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
@endphp
@component('settings.pages.layout')
    @slot('title', $title ?? 'Settings')
    @slot('description', $description ?? '')
    @yield('content')
@endcomponent