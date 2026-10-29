<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $subscription->invoice_number }} - Laundry Zone SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-card { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-6 md:p-12 font-sans antialiased min-h-screen flex flex-col items-center">
    <!-- Top Action Bar -->
    <div class="w-full max-w-2xl flex items-center justify-between mb-6 no-print">
        <a href="{{ route('super_admin.payments.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">&larr; Back to Payments</a>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-lg transition">
            Print / Save as PDF
        </button>
    </div>

    <!-- Invoice Card -->
    <div class="w-full max-w-2xl bg-white rounded-3xl p-8 md:p-12 shadow-2xl border border-slate-200/80 print-card space-y-8">
        <!-- Invoice Header -->
        <div class="flex items-start justify-between pb-6 border-b border-slate-200">
            <div>
                <div class="text-2xl font-black text-slate-900 tracking-tight">Laundry Zone SaaS</div>
                <div class="text-xs text-slate-500 mt-1">Multi-Tenant Platform Billing</div>
                <div class="text-xs text-slate-500 font-mono mt-0.5">support@laundryzone.dsrzone.com</div>
            </div>
            <div class="text-right">
                <div class="text-lg font-black font-mono text-indigo-600">{{ $subscription->invoice_number }}</div>
                <div class="text-xs text-slate-500 mt-1">Issued: {{ $subscription->created_at->format('M d, Y') }}</div>
                <div class="mt-2">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider {{ $subscription->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ strtoupper($subscription->status) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Billed To -->
        <div class="grid grid-cols-2 gap-6 text-xs">
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Billed To (Tenant Company):</span>
                <strong class="text-sm font-bold text-slate-900 block">{{ $subscription->tenant->name ?? 'Company' }}</strong>
                <div class="text-slate-600 mt-1 font-mono">Domain: {{ $subscription->tenant->subdomain ?? '' }}.{{ \App\Models\Central\PlatformSetting::get('central_domain', 'laundryzone.dsrzone.com') }}</div>
                @if($subscription->tenant && $subscription->tenant->phone)
                    <div class="text-slate-600">Phone: {{ $subscription->tenant->phone }}</div>
                @endif
                @if($subscription->tenant && $subscription->tenant->email)
                    <div class="text-slate-600">Email: {{ $subscription->tenant->email }}</div>
                @endif
            </div>
            <div class="text-right">
                <span class="text-slate-400 font-bold uppercase tracking-wider block mb-1">Billing Period:</span>
                <strong class="text-sm font-bold text-slate-900 block">{{ $subscription->billing_period_start?->format('M d, Y') ?? 'N/A' }} - {{ $subscription->billing_period_end?->format('M d, Y') ?? 'N/A' }}</strong>
                <div class="text-slate-600 mt-1">Cycle: {{ ucfirst($subscription->billing_cycle) }}</div>
                <div class="text-slate-600">Payment Method: {{ $subscription->payment_method ?? 'Direct Bank' }}</div>
            </div>
        </div>

        <!-- Line Items -->
        <div>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-200">
                    <tr>
                        <th class="py-3 px-3">Description</th>
                        <th class="py-3 px-3 text-center">Billing Unit</th>
                        <th class="py-3 px-3 text-right">Amount (LKR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr>
                        <td class="py-4 px-3">
                            <strong class="text-slate-900">{{ $subscription->plan_name }} Plan</strong>
                            <div class="text-slate-500 text-[11px] mt-0.5">Core SaaS platform, dedicated database, POS, workshop, HR, and multi-branch management.</div>
                        </td>
                        <td class="py-4 px-3 text-center text-slate-500">1 Month</td>
                        <td class="py-4 px-3 text-right font-bold text-slate-900">Rs. {{ number_format($subscription->base_amount, 2) }}</td>
                    </tr>
                    @if($subscription->delivery_addon_amount > 0)
                        <tr>
                            <td class="py-4 px-3">
                                <strong class="text-slate-900">Home Delivery & Pickup Logistics Module</strong>
                                <div class="text-slate-500 text-[11px] mt-0.5">Online customer delivery bookings, rider dispatch system, and delivery zones.</div>
                            </td>
                            <td class="py-4 px-3 text-center text-slate-500">1 Month</td>
                            <td class="py-4 px-3 text-right font-bold text-slate-900">Rs. {{ number_format($subscription->delivery_addon_amount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot class="border-t-2 border-slate-900 font-bold">
                    <tr>
                        <td colspan="2" class="py-4 px-3 text-right text-sm text-slate-900">Total Invoice Amount:</td>
                        <td class="py-4 px-3 text-right text-lg font-black text-indigo-600">Rs. {{ number_format($subscription->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Note & Payment Info -->
        <div class="pt-6 border-t border-slate-200 text-xs text-slate-500 space-y-2">
            <div class="font-bold text-slate-700">Bank Remittance Instructions:</div>
            <div>Bank: Commercial Bank of Ceylon | Account: 1000293847 | Name: Laundry Zone SaaS Platform</div>
            <div class="text-[11px] text-slate-400 mt-4 text-center">Thank you for your business. For billing queries, email support@laundryzone.dsrzone.com</div>
        </div>
    </div>
</body>
</html>
