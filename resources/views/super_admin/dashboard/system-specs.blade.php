<!-- ══════════ ENGINE DIAGNOSTICS & SYSTEM SPECS (LIGHT THEME) ══════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Architecture Specs -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-sm font-bold text-slate-900">Multi-Tenant Architecture Specs</h3>
            </div>
            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400">Pure Workshop Model</span>
        </div>

        <div class="space-y-3 text-xs">
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Isolation Strategy:</span>
                <strong class="text-slate-900 font-bold">Database-Per-Tenant (Physical Isolation)</strong>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Central Landlord Database:</span>
                <strong class="font-mono text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">laundry_zone_saas_central</strong>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Tenant DB Prefix:</span>
                <strong class="font-mono text-slate-800 bg-slate-100 px-2 py-0.5 rounded">{{ \App\Models\Central\PlatformSetting::get('db_prefix', 'lz_tenant_') }}</strong>
            </div>
            <div class="flex justify-between py-1 border-b border-slate-50">
                <span class="text-slate-500">Wildcard Subdomain Routing:</span>
                <strong class="font-mono text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">*.{{ \App\Services\Tenancy\TenantManager::getCentralBaseDomain() }}</strong>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-slate-500">Multi-Tenancy Engine:</span>
                <span class="font-bold text-emerald-600">Dynamic Connection Hydration</span>
            </div>
        </div>
    </div>

    <!-- Quick Maintenance Actions -->
    <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Platform Quick Actions</h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase">Management</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('super_admin.companies.create') }}"
                class="p-4 rounded-xl bg-blue-50 hover:bg-blue-100/80 border border-blue-100 text-blue-800 transition block group">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-plus text-blue-600"></i>
                    <strong class="text-xs font-bold block">New Company</strong>
                </div>
                <p class="text-[11px] text-blue-600/80 mt-1">Auto-provision DB & tenant</p>
            </a>

            <a href="{{ route('super_admin.databases.index') }}"
                class="p-4 rounded-xl bg-purple-50 hover:bg-purple-100/80 border border-purple-100 text-purple-800 transition block group">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-database text-purple-600"></i>
                    <strong class="text-xs font-bold block">Databases Health</strong>
                </div>
                <p class="text-[11px] text-purple-600/80 mt-1">Verify tenant connections</p>
            </a>

            <a href="{{ route('super_admin.owners.index') }}"
                class="p-4 rounded-xl bg-indigo-50 hover:bg-indigo-100/80 border border-indigo-100 text-indigo-800 transition block group">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-user-shield text-indigo-600"></i>
                    <strong class="text-xs font-bold block">Company Owners</strong>
                </div>
                <p class="text-[11px] text-indigo-600/80 mt-1">Manage master accounts</p>
            </a>

            <a href="{{ route('super_admin.settings.index') }}"
                class="p-4 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-800 transition block group">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-slate-600"></i>
                    <strong class="text-xs font-bold block">Platform Settings</strong>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Global pricing & defaults</p>
            </a>
        </div>
    </div>
</div>
