<x-app-layout>
    <div class="py-12 bg-slate-50 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100 text-center p-8">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fa-solid fa-check text-4xl text-green-500"></i>
            </div>
            
            <h2 class="text-2xl font-black text-slate-800 mb-2">Payment Successful!</h2>
            <p class="text-slate-500 font-medium mb-6">Your payment for Order #{{ $order->order_number }} has been processed successfully.</p>
            
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-8 text-left">
                <div class="flex justify-between mb-2">
                    <span class="text-slate-500 font-semibold text-sm">Order Total</span>
                    <span class="text-slate-800 font-bold text-sm">Rs. {{ number_format($order->total, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-slate-500 font-semibold text-sm">Amount Paid</span>
                    <span class="text-green-600 font-bold text-sm">Rs. {{ number_format($order->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-2 mt-2">
                    <span class="text-slate-700 font-bold text-sm">Status</span>
                    <span class="text-green-600 font-black text-sm uppercase"><i class="fa-solid fa-circle-check"></i> Approved</span>
                </div>
            </div>

            <a href="{{ route('customer.booking.index') }}" class="w-full inline-block bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition-transform hover:scale-[1.02]">
                Return Home
            </a>
        </div>
    </div>
</x-app-layout>
