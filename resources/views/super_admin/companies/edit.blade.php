@extends('super_admin.layouts.app', ['title' => 'Edit Company'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
    x-data="{
        basePrice: {{ old('plan_price', $company->plan_price ?? 15000) }},
        formatLkr(val) {
            return new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR', maximumFractionDigits: 0 }).format(val);
        }
    }">

    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Edit: ' . $company->name,
        'subtitle' => 'Domain: ' . ($company->subdomain . '.' . $centralDomain) . ' • Database: ' . $company->database_name
    ])
        <a href="{{ route('super_admin.companies.index') }}"
            class="flex items-center justify-center px-3 py-2 bg-white text-slate-600 text-xs font-bold border border-slate-200 rounded-xl hover:bg-slate-50 transition h-9">
            &larr; Back
        </a>
        <a href="{{ route('super_admin.companies.impersonate', $company) }}" target="_blank"
            class="flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition h-9">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Login as Owner
        </a>
    @endcomponent

    <form action="{{ route('super_admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Company Information -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Company Identity & Domain</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $company->name) }}" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Custom Domain (Optional)</label>
                    <input type="text" name="custom_domain" value="{{ old('custom_domain', $company->custom_domain) }}"
                        placeholder="e.g. cleanwavelaundry.lk"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Contact Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Contact Email</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Update Logo</label>
                    <div class="flex items-center gap-4">
                        @if($company->logo_path)
                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0">
                                <img src="{{ asset($company->logo_path) }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*"
                            class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">{{ old('address', $company->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Subscription & Status -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Subscription Plan & Status</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Subscription Plan</label>
                    <select name="plan" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="Basic" {{ $company->plan === 'Basic' ? 'selected' : '' }}>Basic Tier</option>
                        <option value="Standard" {{ $company->plan === 'Standard' ? 'selected' : '' }}>Standard Tier</option>
                        <option value="Premium" {{ $company->plan === 'Premium' ? 'selected' : '' }}>Premium Tier</option>
                        <option value="Enterprise" {{ $company->plan === 'Enterprise' ? 'selected' : '' }}>Enterprise Tier</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Monthly Base Fee (Rs.)</label>
                    <input type="number" step="100" name="plan_price" x-model="basePrice" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Account Status</label>
                    <select name="status" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="active" {{ $company->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $company->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="inactive" {{ $company->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Custom SMS & Brand Masking -->
        <div class="rounded-3xl bg-white border border-slate-200/80 p-6 md:p-8 space-y-6 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Custom SMS Gateway & Brand Masking</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS Sender Mask (Header)</label>
                    <input type="text" name="sms_mask" value="{{ old('sms_mask', $company->sms_mask) }}" placeholder="e.g. CLEANWAVE (Max 11 chars)" maxlength="11"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-bold uppercase text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1.5">Displayed as the sender on customer mobile phones.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS Gateway Provider</label>
                    <select name="sms_gateway" class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="textware" {{ $company->sms_gateway === 'textware' ? 'selected' : '' }}>Textware (api.textware.lk)</option>
                        <option value="mobitel" {{ $company->sms_gateway === 'mobitel' ? 'selected' : '' }}>Mobitel mBiz Gateway</option>
                        <option value="dialog" {{ $company->sms_gateway === 'dialog' ? 'selected' : '' }}>Dialog Enterprise SMS</option>
                        <option value="custom" {{ $company->sms_gateway === 'custom' ? 'selected' : '' }}>Generic REST API</option>
                        <option value="mock" {{ $company->sms_gateway === 'mock' ? 'selected' : '' }}>Simulated / Mock Gateway (Testing)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS API Key / Token</label>
                    <input type="password" name="sms_api_key" value="{{ old('sms_api_key', $company->sms_api_key) }}" placeholder="Enter Gateway API Key"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">SMS API Endpoint URL (Optional)</label>
                    <input type="url" name="sms_api_url" value="{{ old('sms_api_url', $company->sms_api_url) }}" placeholder="https://api.textware.lk/sms/send"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Sender Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $company->mail_from_name) }}" placeholder="e.g. CleanWave Laundry"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email Sender Address</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $company->mail_from_address) }}" placeholder="e.g. notifications@cleanwave.lk"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-end gap-3">
            <a href="{{ route('super_admin.companies.index') }}" class="px-5 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Cancel</a>
            <button type="submit" class="px-7 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition">
                Update Company
            </button>
        </div>
    </form>
</div>
@endsection
