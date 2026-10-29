<!-- Brand & Logo -->
<div class="sidebar-header flex flex-col items-center text-center p-6 border-b border-slate-100/80 bg-slate-50/50 transition-all duration-300"
    :class="sidebarCollapsed ? 'px-2 py-4' : ''">
    <button @click="sidebarCollapsed = !sidebarCollapsed"
        :class="sidebarCollapsed ? 'w-12 h-12 rounded-xl ring-2 mb-0' : 'w-20 h-20 rounded-[1.25rem] ring-4 mb-3'"
        class="sidebar-logo-btn w-20 h-20 rounded-[1.25rem] ring-4 mb-3 bg-white flex items-center justify-center shadow-xl shadow-blue-500/10 ring-white transition-all hover:scale-105 duration-300 overflow-hidden flex-shrink-0 cursor-pointer outline-none"
        title="Toggle Sidebar">
        <img src="{{ asset('images/laundry_main_logo.png') }}" alt="Super Admin Logo"
            class="sidebar-logo-img max-w-[52px] max-h-[52px] object-contain transition-all duration-300"
            :class="sidebarCollapsed ? 'max-w-[32px] max-h-[32px]' : 'max-w-[52px] max-h-[52px]'"
            onerror="this.style.display='none'">
    </button>
    <div x-show="!sidebarCollapsed" x-transition.opacity.duration.300ms
        class="sidebar-brand-text whitespace-nowrap overflow-hidden">
        <div class="text-xl font-black text-slate-900 tracking-tight leading-tight">
            {{ \App\Models\Central\PlatformSetting::get('platform_name', 'Laundry Zone') }}
        </div>
        <div class="text-[10px] font-extrabold text-blue-600 uppercase tracking-widest mt-1">
            Super Admin Cloud
        </div>
    </div>
</div>
