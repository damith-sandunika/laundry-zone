<!-- Editable Amount Modal -->
<div x-show="showAmountModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAmountModal = false"
        x-transition.opacity></div>
    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-[280px] flex flex-col overflow-hidden"
        x-transition.scale.origin.bottom>
        <div class="p-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
            <h3 class="text-[10px] font-black text-slate-800 uppercase tracking-wider">Confirm Amount</h3>
            <button type="button" @click="showAmountModal = false"
                class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-4">
            <label class="block text-[10px] font-black text-slate-700 mb-2 uppercase tracking-wider">How
                much are you paying?</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-slate-500 font-black text-xs">Rs.</span>
                </div>
                <input type="number" step="0.01" x-model="paymentAmount"
                    class="w-full pl-8 pr-3 py-1.5 text-base bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-black text-slate-900 outline-none transition-all">
            </div>
        </div>
        <div class="p-3 bg-slate-50 border-t border-slate-100 flex gap-2">
            <button type="button" @click="showAmountModal = false; pendingMethod = '';"
                class="flex-1 py-2 text-[9px] font-black text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors uppercase tracking-wider">Cancel</button>
            <button type="button" @click="
                    if(paymentAmount <= 0) return;
                    confirmedMethod = pendingMethod;
                    showAmountModal = false;
                    if(confirmedMethod === 'card_transfer') {
                        startPayHere(paymentAmount);
                    }
                "
                class="flex-1 py-2 text-[9px] font-black text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-md transition-all uppercase tracking-wider">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
    // Function to start PayHere with the custom amount
    function startPayHere(amount) {
        payhere.onCompleted = function onCompleted(orderId) {
            alert("Payment successful! The system will process your payment momentarily.");
            window.location.reload();
        };
        payhere.onDismissed = function onDismissed() {
            console.log("Payment dismissed");
        };
        payhere.onError = function onError(error) {
            alert("Error:" + error);
        };

        var payment = {
            "sandbox": true,
            "merchant_id": "{{ $merchantId }}",
            "return_url": "{{ route('payment.link.show', $order->payment_token) }}",
            "cancel_url": "{{ route('payment.link.show', $order->payment_token) }}",
            "notify_url": "{{ route('payment.link.webhook', $order->payment_token) }}",
            "order_id": "{{ $order->id }}",
            "items": "Laundry Services Order #{{ $order->order_number }}",
            "amount": amount,
            "currency": "LKR",
            "first_name": "{{ $order->customer->name ?? 'Guest' }}",
            "last_name": "",
            "email": "{{ $order->customer->email ?? 'no-email@laundry.com' }}",
            "phone": "{{ $order->customer->phone ?? '0000000000' }}",
            "address": "{{ $order->delivery_address ?? 'Pickup' }}",
            "city": "Colombo",
            "country": "Sri Lanka"
        };

        payhere.startPayment(payment);
    }
</script>

