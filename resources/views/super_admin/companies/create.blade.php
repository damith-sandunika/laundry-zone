@extends('super_admin.layouts.app', ['title' => 'Register Company & Provision DB'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="provisionWizard()">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Register Company & Provision Database',
        'subtitle' => 'Automatically generates a dedicated MySQL database, migrates all tables, and creates the company owner account'
    ])
        <a href="{{ route('super_admin.companies.index') }}"
            class="flex items-center justify-center px-4 py-2 bg-white text-slate-600 text-[11px] sm:text-[13px] font-bold border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors h-9 sm:h-10 shadow-sm whitespace-nowrap">
            &larr; Back to Companies
        </a>
    @endcomponent

    <form id="createCompanyForm" action="{{ route('super_admin.companies.store') }}" method="POST" enctype="multipart/form-data"
        @submit.prevent="submitProvisioning($event)" class="space-y-6">
        @csrf

        <!-- ══════════ SECTION 1: COMPANY PROFILE ══════════ -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">1</div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Company Identity & Domain</h2>
                    <p class="text-xs text-slate-400">Basic business credentials and subdomain routing.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        @input="if(!subdomainManuallyEdited) subdomain = $el.value.toLowerCase().replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-')"
                        placeholder="e.g. CleanWave Laundromat"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Subdomain <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <input type="text" name="subdomain" x-model="subdomain" @input="subdomainManuallyEdited = true" required
                            placeholder="e.g. cleanwave"
                            class="w-full pl-4 pr-32 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <span class="absolute right-3 text-xs font-mono font-bold text-slate-400 pointer-events-none">.{{ $centralDomain }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1 font-mono">
                        <span>Portal URL:</span>
                        <strong class="text-blue-600">http://<span x-text="subdomain || 'company'"></span>.{{ $centralDomain }}</strong>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Custom Domain (Optional)</label>
                    <input type="text" name="custom_domain" value="{{ old('custom_domain') }}" placeholder="e.g. cleanwavelaundry.lk"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Company Logo</label>
                    <input type="file" name="logo" accept="image/*"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Company Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="011 234 5678"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Company Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="info@cleanwave.lk"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Business Address</label>
                    <textarea name="address" rows="2" placeholder="123 Galle Road, Colombo 03, Sri Lanka"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        <!-- ══════════ SECTION 2: SUBSCRIPTION & PRICING TIER ══════════ -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">2</div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Subscription Tier & Pricing</h2>
                    <p class="text-xs text-slate-400">Configure monthly billing plan for laundry operations.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Plan Tier</label>
                    <select name="plan" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="Basic">Basic Tier</option>
                        <option value="Standard" selected>Standard Tier</option>
                        <option value="Premium">Premium Tier</option>
                        <option value="Enterprise">Enterprise Tier</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Monthly Price (Rs.)</label>
                    <input type="number" step="100" name="plan_price" x-model="basePrice" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <div class="text-xs text-slate-500 font-medium">Monthly Subscription Price:</div>
                    <div class="text-xs font-bold text-slate-800 mt-0.5">Fixed rate billing for branches and workshop operations</div>
                </div>
                <div class="text-xl font-black text-emerald-600" x-text="formatLkr(basePrice) + ' / mo'"></div>
            </div>
        </div>

        <!-- ══════════ SECTION 3: COMPANY OWNER MASTER ACCOUNT ══════════ -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">3</div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Company Owner (Master Account)</h2>
                    <p class="text-xs text-slate-400">Seeded with full <strong>Owner</strong> privileges for this tenant.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Owner Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="owner_name" value="{{ old('owner_name') }}" required placeholder="e.g. Nimal Perera"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Owner Login Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="owner_email" value="{{ old('owner_email') }}" required placeholder="e.g. owner@cleanwave.lk"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Owner Phone</label>
                    <input type="text" name="owner_phone" value="{{ old('owner_phone') }}" placeholder="077 123 4567"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Initial Password <span class="text-rose-500">*</span></label>
                    <input type="text" name="owner_password" value="{{ old('owner_password', 'Password@123') }}" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2 flex items-center justify-end gap-3">
            <a href="{{ route('super_admin.companies.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Cancel</a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm hover:shadow-md transition">
                <i class="fa-solid fa-bolt text-xs"></i>
                <span>Create Company & Auto-Provision Database</span>
            </button>
        </div>
    </form>

    <!-- PROVISIONING MODAL & SCRIPT PARTIALS -->
    @include('super_admin.companies.partials.create-modal')
    @include('super_admin.companies.partials.create-script')
</div>
@endsection
