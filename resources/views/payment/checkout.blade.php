<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
            <div class="bg-blue-600 p-6 text-center">
                <h2 class="text-white text-2xl font-black">Secure Checkout</h2>
                <p class="text-blue-100 text-sm mt-1">Mock Payment Gateway Simulation</p>
            </div>
            
            <div class="p-6">
                <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Total to Pay</p>
                    <p class="text-4xl font-black text-slate-800">Rs. {{ number_format($amount, 2) }}</p>
                    <p class="text-xs font-semibold text-slate-400 mt-2">Order #{{ $order->order_number }}</p>
                    <p class="text-[10px] text-slate-400 mt-1">Merchant ID: {{ $merchantId }}</p>
                </div>

                <form action="{{ route('payment.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                    <input type="hidden" name="amount" value="{{ $amount }}">

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Cardholder Name</label>
                        <input type="text" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-semibold" placeholder="John Doe" value="John Doe" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1">Card Number</label>
                        <div class="relative">
                            <input type="text" name="card_number" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-mono tracking-widest" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242" required>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex gap-1 text-xl text-slate-300">
                                <i class="fa-brands fa-cc-visa"></i>
                                <i class="fa-brands fa-cc-mastercard"></i>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Expiry Date</label>
                            <input type="text" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-mono" placeholder="MM/YY" value="12/25" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">CVV</label>
                            <input type="password" class="w-full rounded-xl border-slate-200 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 font-mono" placeholder="***" value="123" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-black py-4 rounded-xl shadow-lg shadow-green-200 transition-transform hover:scale-[1.02] flex items-center justify-center gap-2 text-lg">
                        <i class="fa-solid fa-lock"></i> Pay Rs. {{ number_format($amount, 2) }}
                    </button>
                    
                    <p class="text-center text-xs text-slate-400 font-semibold mt-4">
                        This is a secure mock payment. No real funds will be deducted.
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

