@extends('super_admin.layouts.app', ['title' => 'Settings & Configuration'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Platform Settings',
        'subtitle' => 'Configure global SaaS parameters, database provisioning defaults, and platform branding'
    ])
        <a href="{{ route('super_admin.dashboard') }}"
            class="flex items-center justify-center px-4 py-2 bg-white text-slate-600 text-[11px] sm:text-[13px] font-bold border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors h-9 sm:h-10 shadow-sm whitespace-nowrap">
            &larr; Back to Dashboard
        </a>
    @endcomponent

    <form action="{{ route('super_admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- General Platform Settings -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-600"></i>
                <span>General SaaS Platform Identity</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Platform Name</label>
                    <input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Central Domain (Base Domain)</label>
                    <input type="text" name="central_domain" value="{{ $settings['central_domain'] }}" required
                        placeholder="localhost"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1">Tenant subdomains will be formed as <code>{subdomain}.{{ $settings['central_domain'] }}</code>.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Platform Support Email</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] }}"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Platform Support Phone</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] }}"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
        </div>

        <!-- Default Pricing -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-coins text-emerald-600"></i>
                <span>Default Subscription Pricing</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Default Base Monthly Price (Rs.)</label>
                    <input type="number" step="100" name="default_base_price" value="{{ $settings['default_base_price'] }}" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Production Server IP (Optional)</label>
                    <input type="text" name="server_ip" value="{{ $settings['server_ip'] }}" placeholder="e.g. 159.65.12.34"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
        </div>

        <!-- Database Provisioning Defaults -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-database text-purple-600"></i>
                <span>Tenant Database Provisioning Defaults</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Tenant Database Prefix</label>
                    <input type="text" name="db_prefix" value="{{ $settings['db_prefix'] }}" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1">e.g. <code>lz_tenant_</code> produces <code>lz_tenant_sunshine</code></p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                class="px-7 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                Save Platform Settings
            </button>
        </div>
    </form>
</div>
@endsection
