<!-- ══════════ SUPER ADMIN SIDEBAR ══════════ -->
<aside id="main-sidebar"
    :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'sidebar-collapsed' : 'w-64']"
    class="w-64 fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out bg-white/80 backdrop-blur-xl border-r border-slate-200/60 lg:static lg:translate-x-0">
    <script>
        if (localStorage.getItem('sa_sidebarCollapsed') === 'true') {
            document.getElementById('main-sidebar').classList.add('sidebar-collapsed');
        }
    </script>

    <!-- Header / Brand -->
    @include('super_admin.layouts.partials.sidebar-header')

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar"
        x-init="$watch('sidebarCollapsed', val => { 
            document.querySelectorAll('aside nav a, aside nav button').forEach(el => { 
                if(val) el.setAttribute('title', el.textContent.trim()); 
                else el.removeAttribute('title'); 
            }) 
         }); 
         setTimeout(() => { if(sidebarCollapsed) document.querySelectorAll('aside nav a, aside nav button').forEach(el => el.setAttribute('title', el.textContent.trim())) }, 100);">

        @include('super_admin.layouts.partials.sidebar-menu')
    </nav>

    <!-- Footer / Sign Out -->
    @include('super_admin.layouts.partials.sidebar-footer')
</aside>
