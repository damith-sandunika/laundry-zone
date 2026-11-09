@extends('super_admin.layouts.app', ['title' => 'Payments & Billing'])

@section('content')
<div class="space-y-6" x-data="{ invoiceModal: false, statusModal: false, smsModal: false, activeSub: {} }">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Subscriptions & Billing',
        'subtitle' => 'Tenant subscription billing, invoice tracking, and SMS credit revenue'
    ])
        <div class="flex items-center gap-2">
            <button @click="smsModal = true"
                class="flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add SMS Credits
            </button>
            <button @click="invoiceModal = true"
                class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Generate New Invoice
            </button>
        </div>
    @endcomponent

    <!-- Revenue Summary Cards (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Subscriptions</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">Rs. {{ number_format($totalCollected, 0) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">Settled monthly invoices</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">SMS Credits Revenue</div>
            <div class="text-2xl font-black text-indigo-600 mt-2">Rs. {{ number_format($totalSmsRevenue, 0) }}</div>
            <div class="text-[11px] text-indigo-600 font-semibold mt-1">{{ number_format($totalSmsIssued) }} SMS credits issued</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Pending Billing</div>
            <div class="text-2xl font-black text-amber-600 mt-2">Rs. {{ number_format($pendingAmount, 0) }}</div>
            <div class="text-[11px] text-amber-600 font-semibold mt-1">Awaiting client payment</div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Overdue Amount</div>
            <div class="text-2xl font-black text-rose-600 mt-2">Rs. {{ number_format($overdueAmount, 0) }}</div>
            <div class="text-[11px] text-rose-600 font-semibold mt-1">Requires follow-up</div>
        </div>
    </div>

    <!-- Invoices Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-sm font-black text-slate-800">Monthly Subscription Invoices</h3>
        </div>
        @if($subscriptions->isEmpty())
            <div class="p-12 text-center text-slate-400 text-sm">
                No billing records found. Click <strong>Generate New Invoice</strong> to create one!
            </div>
        @else
            @include('super_admin.payments.partials.table')
        @endif
    </div>

    <!-- SMS Credit Purchase Records -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-sm font-black text-slate-800">Tenant SMS Top-Up History</h3>
                <p class="text-[11px] font-semibold text-slate-500">Record of SMS packages issued to tenant companies</p>
            </div>
            <button @click="smsModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                + Add SMS Credits
            </button>
        </div>
        @if(isset($smsCredits) && $smsCredits->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                        <tr>
                            <th class="px-6 py-3.5">Company</th>
                            <th class="px-4 py-3.5">SMS Added</th>
                            <th class="px-4 py-3.5">Unit Rate</th>
                            <th class="px-4 py-3.5">Total Paid</th>
                            <th class="px-4 py-3.5">Current Balance</th>
                            <th class="px-4 py-3.5">Method</th>
                            <th class="px-6 py-3.5 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($smsCredits as $c)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-900 block">{{ $c->tenant->name ?? 'Company' }}</span>
                                    <span class="text-xs font-mono text-slate-400">{{ $c->tenant->subdomain ?? '' }}</span>
                                </td>
                                <td class="px-4 py-4 font-black text-emerald-600 text-xs">
                                    + {{ number_format($c->sms_count) }} SMS
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-600">
                                    Rs. {{ number_format($c->rate_per_sms, 2) }}
                                </td>
                                <td class="px-4 py-4 font-black text-sm text-slate-900">
                                    Rs. {{ number_format($c->amount_paid, 0) }}
                                </td>
                                <td class="px-4 py-4 font-mono font-bold text-xs text-indigo-600">
                                    {{ number_format($c->tenant->sms_balance ?? 0) }} SMS
                                </td>
                                <td class="px-4 py-4 text-xs text-slate-500">
                                    {{ $c->payment_method }}
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-slate-500 whitespace-nowrap">
                                    {{ $c->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $smsCredits->links() }}
            </div>
        @else
            <div class="p-8 text-center text-slate-400 text-sm">
                No SMS top-up transactions recorded yet. Click <strong>Add SMS Credits</strong> to issue a package.
            </div>
        @endif
    </div>

    <!-- MODALS PARTIAL -->
    @include('super_admin.payments.partials.modals')
</div>
@endsection
