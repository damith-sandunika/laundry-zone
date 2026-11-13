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

<!-- Brand & Logo -->
<div class="sidebar-header flex flex-col items-center text-center p-6 border-b border-slate-100/80 bg-slate-50/50 transition-all duration-300"
    :class="sidebarCollapsed ? 'px-2 py-4' : ''">
    <button @click="sidebarCollapsed = !sidebarCollapsed"
        :class="sidebarCollapsed ? 'w-12 h-12 rounded-xl ring-2 mb-0' : 'w-20 h-20 rounded-[1.25rem] ring-4 mb-3'"
        class="sidebar-logo-btn w-20 h-20 rounded-[1.25rem] ring-4 mb-3 bg-white flex items-center justify-center shadow-xl shadow-blue-500/10 ring-white transition-all hover:scale-105 duration-300 overflow-hidden flex-shrink-0 cursor-pointer outline-none p-1.5">
        <img src="{{ $companyLogo }}" alt="{{ $companyName }} Logo"
            class="sidebar-logo-img max-w-[56px] max-h-[56px] object-contain transition-all duration-300"
            :class="sidebarCollapsed ? 'max-w-[34px] max-h-[34px]' : 'max-w-[56px] max-h-[56px]'"
            onerror="this.src='{{ $defaultLogo }}'">
    </button>
    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.300ms
        class="whitespace-nowrap overflow-hidden">
        <div class="text-xl font-black text-slate-900 tracking-tight leading-tight">
            {{ $companyName }}
        </div>
        <div class="text-[10px] font-extrabold text-blue-600 uppercase tracking-widest mt-1">
            Management System
        </div>
    </div>
</div>
