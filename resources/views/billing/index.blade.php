<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">SaaS Subscription & Monthly Billing</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">
                    Monthly billing history, invoices, and outstanding dues for <strong class="text-slate-700">{{ $tenant->name }}</strong>
                </p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold {{ $tenant->status_badge['bg'] ?? 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                    <span class="w-2 h-2 rounded-full {{ $tenant->status_badge['dot'] ?? 'bg-emerald-500' }}"></span>
                    Portal Status: {{ ucfirst($tenant->status) }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Outstanding Balance Alert if Pending -->
    @if($outstandingBalance > 0)
        <div class="mb-6 p-5 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-slide-up">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-rose-900">Outstanding Payment Due</h4>
                    <p class="text-xs font-semibold text-rose-700 mt-0.5">
                        You have an outstanding balance of <strong class="text-rose-950 font-black">Rs. {{ number_format($outstandingBalance, 0) }}</strong>. Please settle your monthly dues to prevent service interruption.
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-3.5 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-black shadow-sm shadow-rose-600/30 whitespace-nowrap">
                    Due: Rs. {{ number_format($outstandingBalance, 0) }}
                </span>
            </div>
        </div>
    @endif

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Monthly Plan Rate -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Monthly Plan Rate</span>
            <div class="text-2xl font-black text-slate-900 mt-2">
                Rs. {{ number_format($tenant->total_monthly_price, 0) }}
                <span class="text-xs font-bold text-slate-400">/ month</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                Plan: <strong class="text-slate-700">{{ $tenant->plan ?? 'Standard' }}</strong> 
                @if($tenant->is_delivery_enabled)
                    <span class="text-blue-600">(+Delivery Addon)</span>
                @endif
            </div>
        </div>

        <!-- Total Settled Payments -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Total Settled Payments</span>
            <div class="text-2xl font-black text-emerald-600 mt-2">
                Rs. {{ number_format($totalPaid, 0) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-500 mt-1">
                All paid historical subscription invoices
            </div>
        </div>

        <!-- Outstanding Balance -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest {{ $outstandingBalance > 0 ? 'text-rose-600' : 'text-slate-400' }}">Outstanding Balance</span>
            <div class="text-2xl font-black {{ $outstandingBalance > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-2">
                Rs. {{ number_format($outstandingBalance, 0) }}
            </div>
            <div class="text-[11px] font-semibold {{ $outstandingBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                {{ $outstandingBalance > 0 ? 'Action required: payment pending' : 'All accounts settled up to date' }}
            </div>
        </div>
    </div>

    <!-- Monthly Billing History Table -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden mb-6 flex flex-col justify-between">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-black text-slate-900 tracking-tight">Monthly Billing History</h3>
                <p class="text-xs font-semibold text-slate-500">Breakdown of billing months and payments</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap">Invoice #</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Billing Month & Year</th>
                        <th class="px-4 py-3.5">Plan / Description</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Total Amount</th>
                        <th class="px-4 py-3.5 whitespace-nowrap text-center">Payment Status</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">Settled At / Method</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                {{ $sub->invoice_number }}
                            </td>
                            <td class="px-4 py-4 font-black text-slate-900 text-xs">
                                {{ $sub->billing_period_start ? $sub->billing_period_start->format('F Y') : 'Monthly' }}
                            </td>
                            <td class="px-4 py-4 text-xs">
                                <span class="font-bold text-slate-800">{{ $sub->plan_name }}</span>
                                @if($sub->delivery_addon_amount > 0)
                                    <div class="text-[10px] text-slate-400">+ Delivery Addon (Rs. {{ number_format($sub->delivery_addon_amount, 0) }})</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 font-black text-xs text-slate-900">
                                Rs. {{ number_format($sub->total_amount, 0) }}
                            </td>
                            <td class="px-4 py-4">
                                @if($sub->status === 'paid')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Paid
                                    </span>
                                @elseif($sub->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Pending Payment
                                    </span>
                                @elseif($sub->status === 'overdue')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Overdue
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                @if($sub->status === 'paid' && $sub->paid_at)
                                    <div class="font-bold text-slate-700">{{ $sub->paid_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $sub->payment_method ?? 'Direct' }}</div>
                                @else
                                    <span class="text-slate-400 italic">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('super_admin.payments.invoice', $sub) }}" target="_blank"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    View Slip
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No billing records found for this company yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

