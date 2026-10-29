@extends('super_admin.layouts.app', ['title' => $company->name . ' - Company Details'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => $company->name,
        'subtitle' => 'Tenant ID #' . $company->id . ' • Registered on ' . $company->created_at->format('M d, Y')
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
        <a href="{{ route('super_admin.companies.edit', $company) }}"
            class="flex items-center justify-center px-4 py-2 bg-white text-slate-700 text-xs font-bold border border-slate-200 rounded-xl hover:bg-slate-50 transition h-9 shadow-sm">
            Edit
        </a>
    @endcomponent

    <!-- Quick Stats Cards (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-semibold">Company Access URL</div>
            <div class="text-sm font-bold font-mono text-blue-600 mt-1 truncate">
                <a href="{{ $company->url }}" target="_blank" class="hover:underline flex items-center gap-1.5">
                    <span>{{ parse_url($company->url, PHP_URL_HOST) ?? ($company->subdomain . '.' . $centralDomain) }}</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-70"></i>
                </a>
            </div>
            <div class="mt-1">
                <a href="{{ $company->staff_login_url }}" target="_blank" class="text-xs font-bold text-sky-600 hover:underline">
                    &rarr; Go to Staff Login
                </a>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-semibold">Database Name</div>
            <div class="text-sm font-bold font-mono text-purple-700 mt-1 truncate">{{ $company->database_name }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">{{ $dbStats['tables'] }} Tables &bull; {{ $dbStats['size_mb'] }} MB</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-semibold">Monthly MRR</div>
            <div class="text-sm font-black text-slate-900 mt-1">Rs. {{ number_format($company->total_monthly_price, 0) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Plan: {{ $company->plan }}</div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Company Identity Card -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-3">Company Information</h3>
            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Company Name:</span>
                    <strong class="text-slate-900">{{ $company->name }}</strong>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Official URL:</span>
                    <a href="{{ $company->url }}" target="_blank" class="text-blue-600 hover:underline font-mono">{{ $company->url }}</a>
                </div>
                @if($company->custom_domain)
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Custom Domain:</span>
                        <strong class="text-emerald-600 font-mono">{{ $company->custom_domain }}</strong>
                    </div>
                @endif
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Contact Phone:</span>
                    <span class="text-slate-800">{{ $company->phone ?: 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-50">
                    <span class="text-slate-500">Contact Email:</span>
                    <span class="text-slate-800">{{ $company->email ?: 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-500">Business Address:</span>
                    <span class="text-slate-800 max-w-xs text-right">{{ $company->address ?: 'Not provided' }}</span>
                </div>
            </div>
        </div>

        <!-- Owner & Master Account Card -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-3">Company Owner Account</h3>
            @if($company->latestOwner)
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Owner Name:</span>
                        <strong class="text-slate-900">{{ $company->latestOwner->name }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Login Email:</span>
                        <strong class="text-blue-600 font-mono">{{ $company->latestOwner->email }}</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Owner Phone:</span>
                        <span class="text-slate-800">{{ $company->latestOwner->phone ?: 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Initial Password:</span>
                        <span class="font-mono text-amber-700 bg-amber-50 px-2 py-0.5 rounded">{{ $company->latestOwner->initial_password ?: '********' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Account Status:</span>
                        <span class="text-emerald-600 font-bold">Active Owner</span>
                    </div>
                </div>
            @else
                <div class="text-slate-400 text-xs py-4 text-center">No owner assigned yet.</div>
            @endif
        </div>
    </div>

    <!-- Subscription History Table -->
    <div class="rounded-3xl bg-white border border-slate-200/80 p-6 space-y-4 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">Billing & Invoices History</h3>
        @if($company->subscriptions->isEmpty())
            <div class="text-center py-6 text-slate-400 text-xs">No billing records for this tenant.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100 bg-slate-50/50">
                        <tr>
                            <th class="py-3 px-3">Invoice #</th>
                            <th class="py-3 px-3">Period</th>
                            <th class="py-3 px-3">Base Plan</th>
                            <th class="py-3 px-3">Total Amount</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($company->subscriptions as $sub)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $sub->invoice_number }}</td>
                                <td class="py-3 px-3 text-slate-500">{{ $sub->billing_period_start?->format('M Y') ?? 'Monthly' }}</td>
                                <td class="py-3 px-3">Rs. {{ number_format($sub->base_amount, 0) }}</td>
                                <td class="py-3 px-3 font-bold text-emerald-600">Rs. {{ number_format($sub->total_amount, 0) }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $sub->status_badge['bg'] }}">
                                        {{ $sub->status_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('super_admin.payments.invoice', $sub) }}" target="_blank"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
