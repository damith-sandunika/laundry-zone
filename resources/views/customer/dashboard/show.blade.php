<x-customer-layout>
    @php
        $hasPendingSlipGlobally = \App\Models\PaymentTransaction::where('order_id', $order->id)->where('status', 'pending')->exists();
    @endphp
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders') }}" class="text-slate-400 hover:text-blue-600 transition p-2 bg-white rounded-lg shadow-sm border border-slate-100"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <h2 class="font-bold text-lg sm:text-xl md:text-2xl text-slate-800 leading-tight">Order #{{ $order->order_number }}</h2>
                    <p class="text-xs sm:text-sm font-bold text-slate-500">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            @if($order->payment_status === 'unpaid' || $order->payment_status === 'partial')
                @if(!$hasPendingSlipGlobally)
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-slate-900 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Pay Now
                </button>
                @endif
            @else
                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Paid
                </span>
            @endif
        </div>
    </x-slot>

    @include('customer.dashboard.partials.timeline')

    @include('customer.dashboard.partials.payment-banner')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            @include('customer.dashboard.partials.order-items')

            @include('customer.dashboard.partials.notes')
        </div>

        <!-- Right Column: Timeline & Branch -->
        <div class="space-y-6">
            @include('customer.dashboard.partials.branch-info')
        </div>
    </div>

    @include('customer.dashboard.partials.payment-modal')
</x-customer-layout>


