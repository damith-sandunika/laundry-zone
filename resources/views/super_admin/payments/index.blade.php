@extends('super_admin.layouts.app', ['title' => 'Payments & Billing'])

@section('content')
<div class="space-y-6" x-data="{ invoiceModal: false, statusModal: false, activeSub: {} }">
    <!-- Signature Title Section -->
    @component('super_admin.layouts.partials.title-section', [
        'title' => 'Subscriptions & Billing',
        'subtitle' => 'Tenant subscription billing, invoice tracking, and revenue records'
    ])
        <button @click="invoiceModal = true"
            class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[11px] sm:text-[13px] font-bold rounded-xl shadow-sm transition-colors h-9 sm:h-10 whitespace-nowrap">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Generate New Invoice
        </button>
    @endcomponent

    <!-- Revenue Summary Cards (Light Theme) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Collected Revenue</div>
            <div class="text-2xl font-black text-emerald-600 mt-2">Rs. {{ number_format($totalCollected, 0) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1">Settled invoices</div>
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
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        @if($subscriptions->isEmpty())
            <div class="p-12 text-center text-slate-400 text-sm">
                No billing records found. Click <strong>Generate New Invoice</strong> to create one!
            </div>
        @else
            @include('super_admin.payments.partials.table')
        @endif
    </div>

    <!-- MODALS PARTIAL -->
    @include('super_admin.payments.partials.modals')
</div>
@endsection
