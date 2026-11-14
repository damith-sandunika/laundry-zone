<x-app-layout>
    @include('orders.partials.header')

    @if(session('success'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:12px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div
            style="margin-bottom:12px;padding:10px 14px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:12px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Please fix the following errors:</span>
            </div>
            <ul style="margin:0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('print_bill_id'))
        <script>
            window.onload = function () {
                window.open("{{ route('orders.print', session('print_bill_id')) }}", "_blank");
            };
        </script>
    @endif

    @include('orders.partials.workflow_actions')

    @include('orders.partials.correction_requests')

    <div class="flex flex-col gap-4 items-start">
        <div class="w-full flex flex-col gap-4">
            @include('orders.partials.main_column')

            <div class="max-w-sm flex flex-col gap-4 mt-4">
                @include('orders.partials.payment_slips')

            </div>
        </div>

        @include('orders.partials.maps')

        @include('orders.partials.details.delivery_pickup_modals')

        <!-- Pay Now Modal -->
        <div x-data="{ openPayModal: false, payAmount: '{{ number_format(max(0, $order->total - $order->paid_amount), 2, '.', '') }}', payMethod: 'cash' }"
            x-on:open-pay-modal.window="openPayModal = true" x-show="openPayModal" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

            <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-sm overflow-hidden"
                @click.away="openPayModal = false" x-show="openPayModal" x-transition.scale.origin.bottom>
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-800">Collect Payment</h3>
                    <button @click="openPayModal = false"
                        class="text-slate-400 hover:text-red-500 font-bold text-xl leading-none">&times;</button>
                </div>

                <form action="{{ route('orders.collectCreditPayment', $order->id) }}" method="POST"
                    class="p-5 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label
                            class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Amount
                            (Rs.)</label>
                        <input type="number" name="amount" x-model="payAmount" step="0.01" min="0.01" required
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[15px] font-black text-slate-800 outline-none transition-all">
                        <p class="text-[10px] font-bold text-slate-400 mt-1">Balance Due:
                            Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</p>
                    </div>

                    <div>
                        <label
                            class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Payment
                            Method</label>
                        <select name="payment_method" x-model="payMethod" required
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="bank">🏦 Bank Transfer</option>
                        </select>
                    </div>

                    <div class="flex gap-2 mt-2">
                        <button type="button" @click="openPayModal = false"
                            class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl transition-colors hover:bg-slate-200 text-[13px]">Cancel</button>
                        <button type="submit"
                            class="flex-1 py-3 bg-emerald-500 text-white font-black rounded-xl transition-all hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30 text-[13px]">Confirm
                            Payment</button>
                    </div>
                </form>
            </div>
        </div>
</x-app-layout>
