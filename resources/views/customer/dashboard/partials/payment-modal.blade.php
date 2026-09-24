    <!-- Payment Modal -->
    <div x-data="{ 
            showPaymentModal: false, 
            amountToPay: {{ ($order->requested_advance - $order->paid_amount > 0) ? ($order->requested_advance - $order->paid_amount) : max(0, $order->total - $order->paid_amount) }},
            paymentMethod: 'bank',
            isSubmitting: false
         }" 
         @open-payment-modal.window="showPaymentModal = true"
         class="relative z-50" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
         
        <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div x-show="showPaymentModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 w-screen overflow-y-auto" style="display: none;">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 w-full max-w-md mx-4 sm:mx-auto">
                    <form action="{{ route('customer.orders.submitPayment', $order->order_number) }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
                        @csrf
                        <div class="bg-white px-4 sm:px-6 pb-4 sm:pb-6 pt-6 sm:pt-8">
                            <div class="sm:flex sm:items-start mb-6">
                                <div class="mx-auto flex h-10 w-10 sm:h-12 sm:w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 text-blue-600 text-lg sm:text-xl">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-lg sm:text-xl font-bold leading-6 text-slate-800" id="modal-title">Make Payment</h3>
                                    <div class="mt-1">
                                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Please enter the amount you wish to pay.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4 sm:space-y-5">
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-1">Amount (LKR)</label>
                                    <input type="number" name="amount" x-model="amountToPay" step="0.01" max="{{ $order->total }}" required class="w-full px-3 py-2 sm:px-4 sm:py-3 text-base sm:text-lg font-bold text-blue-700 bg-slate-50 border border-slate-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 text-center">
                                    <div class="flex justify-between mt-1 px-1">
                                        <span class="text-[10px] text-slate-400 font-bold">Requested: {{ number_format($order->requested_advance > 0 ? $order->requested_advance : $order->total, 2) }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold">Total: {{ number_format($order->total, 2) }}</span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">Payment Method</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @forelse($paymentMethods as $method)
                                        <label class="relative cursor-pointer h-full">
                                            <input type="radio" name="payment_method" value="{{ $method->type === 'bank_transfer' ? 'bank' : 'gateway' }}" x-model="paymentMethod" class="peer sr-only">
                                            <div class="p-2 sm:p-3 border-2 border-slate-200 rounded-lg sm:rounded-xl text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:bg-slate-50 transition-all h-full flex flex-col justify-center">
                                                <i class="fa-solid {{ $method->type === 'bank_transfer' ? 'fa-building-columns' : 'fa-credit-card' }} text-slate-400 peer-checked:text-blue-600 mb-1 text-base sm:text-lg"></i>
                                                <div class="text-[10px] sm:text-xs font-bold text-slate-700 peer-checked:text-blue-700">{{ $method->name }}</div>
                                            </div>
                                        </label>
                                        @empty
                                        <div class="col-span-2 text-xs sm:text-sm text-red-500 font-bold p-3 bg-red-50 rounded-lg border border-red-100">
                                            No online payment methods are currently available.
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                                
                                <div x-show="paymentMethod === 'bank'" x-collapse>
                                    @if(!empty($bankDetails))
                                    <div class="mb-4 p-3 sm:p-4 bg-blue-50 rounded-lg sm:rounded-xl border border-blue-100">
                                        <h4 class="text-xs sm:text-sm font-bold text-blue-800 mb-2">Our Bank Details</h4>
                                        <div class="text-[11px] sm:text-xs text-blue-700 whitespace-pre-wrap font-medium">{!! nl2br(e($bankDetails)) !!}</div>
                                    </div>
                                    @endif
                                    <label class="block text-xs sm:text-sm font-bold text-slate-700 mb-2">Upload Bank Slip</label>
                                    <input type="file" name="receipt_image" accept="image/*" :required="paymentMethod === 'bank'" class="w-full text-xs sm:text-sm text-slate-500 file:mr-3 sm:file:mr-4 file:py-2 file:px-3 sm:file:py-2.5 sm:file:px-4 file:rounded-lg sm:file:rounded-xl file:border-0 file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all border border-slate-200 rounded-lg sm:rounded-xl p-1 bg-slate-50">
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 sm:px-6 py-3 sm:py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 gap-2 sm:gap-3">
                            <button type="submit" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-lg sm:rounded-xl bg-blue-600 px-6 py-2.5 sm:py-3 text-sm font-bold text-white shadow-md hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!isSubmitting">Proceed to Pay</span>
                                <span x-show="isSubmitting"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Processing...</span>
                            </button>
                            <button type="button" @click="showPaymentModal = false" class="mt-3 inline-flex w-full justify-center rounded-lg sm:rounded-xl bg-white px-6 py-2.5 sm:py-3 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
