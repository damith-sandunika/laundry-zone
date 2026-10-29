        <!-- ══════════ SIDEBAR ══════════ -->
        <aside id="main-sidebar" :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'sidebar-collapsed' : 'w-64']" class="w-64 fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out bg-white/80 backdrop-blur-xl border-r border-slate-200/60 lg:static lg:translate-x-0">
            <script>if (localStorage.getItem('sidebarCollapsed') === 'true') document.getElementById('main-sidebar').classList.add('sidebar-collapsed');</script>

            @include('layouts.partials.sidebar.header')

            <!-- Nav Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar"
                x-init="$watch('sidebarCollapsed', val => { 
                    document.querySelectorAll('aside nav a, aside nav button').forEach(el => { 
                        if(val) el.setAttribute('title', el.textContent.trim()); 
                        else el.removeAttribute('title'); 
                    }) 
                 }); 
                 setTimeout(() => { if(sidebarCollapsed) document.querySelectorAll('aside nav a, aside nav button').forEach(el => el.setAttribute('title', el.textContent.trim())) }, 100);">
                @php 
                    $role = auth()->user()->role;
                    $branchType = auth()->user()->branch->type ?? 'branch';
                    $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';
                    $isGlobalManager = auth()->user()->canManageGlobalConfig();
                    $isSuperAdmin = in_array($role, ['owner', 'super_admin']);
                    $canManageConfig = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
                @endphp

                {{-- Dashboard: All roles --}}
                <a href="{{ route($role === 'workshop_admin' ? 'workshop.dashboard' : 'dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 text-[13px] font-semibold {{ request()->routeIs('dashboard') || request()->routeIs('workshop.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-blue-100/50' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="w-[18px] h-[18px] opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>
                    Dashboard
                </a>

                @include('layouts.partials.sidebar.menu-super-admin')

                @include('layouts.partials.sidebar.menu-workshop')

                @include('layouts.partials.sidebar.menu-branch')
            </nav>

            @include('layouts.partials.sidebar.footer')
        </aside>