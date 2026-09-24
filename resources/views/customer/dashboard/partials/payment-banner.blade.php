    <!-- Action Required: Payment Banner -->
    @php
        $latestSlip = \App\Models\PaymentTransaction::where('order_id', $order->id)->whereNotNull('receipt_path')->latest()->first();
        $isLatestSlipRejected = $latestSlip && $latestSlip->status === 'rejected';
        
        $hasStartedWashing = !in_array($order->status, ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop']);
    @endphp

    @if(!$hasStartedWashing)
        @if($isLatestSlipRejected)
        <div class="bg-red-50 rounded-lg sm:rounded-xl border border-red-200 p-4 sm:p-6 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-red-800 text-center sm:text-left">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-red-100 flex items-center justify-center text-2xl sm:text-3xl shrink-0">
                    <i class="fa-solid fa-circle-xmark text-red-500"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base sm:text-lg mb-1">Payment Slip Rejected</h3>
                    <p class="font-bold text-xs sm:text-sm text-red-700 opacity-90 max-w-xl">
                        Unfortunately, your recent payment slip was not accepted by our team.
                    </p>
                    <div class="mt-3 bg-white p-3 rounded-xl border border-red-100 text-sm font-bold shadow-sm">
                        <span class="text-red-900 uppercase text-[10px] tracking-wider block mb-1">Reason for Rejection:</span>
                        {{ $latestSlip->reject_reason ?? 'No reason provided' }}
                    </div>
                </div>
            </div>
            
            <div class="relative z-10 shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="w-full sm:w-auto bg-red-500 text-white hover:bg-red-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload New Slip
                </button>
            </div>
        </div>
        @elseif(!$order->payment_approved && ($order->requested_advance - $order->paid_amount) > 0 && !$hasPendingSlipGlobally)
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-lg sm:rounded-xl shadow-lg p-5 sm:p-8 mb-6 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mt-20 -mr-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white opacity-5 rounded-full -mb-10 -ml-10 pointer-events-none"></div>
            
            <div class="relative z-10 text-center md:text-left">
                <h3 class="text-lg sm:text-2xl font-bold mb-2"><i class="fa-solid fa-circle-exclamation mr-2 text-yellow-300 animate-pulse"></i> Action Required: Advance Payment</h3>
                <p class="text-blue-100 font-medium text-sm md:text-base max-w-2xl">
                    We have inspected your laundry! To start the washing process, please pay the requested advance amount of 
                    <strong class="text-white bg-blue-800/50 px-2 py-1 rounded-lg">LKR {{ number_format($order->requested_advance - $order->paid_amount, 2) }}</strong>. 
                    Once paid, our workshop will immediately begin processing your order.
                </p>
            </div>
            
            <div class="relative z-10 shrink-0 w-full md:w-auto">
                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="w-full md:w-auto bg-yellow-400 text-yellow-900 hover:bg-yellow-300 px-6 py-3 sm:px-8 sm:py-4 rounded-lg sm:rounded-xl font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all text-base sm:text-lg flex items-center justify-center gap-3">
                    <i class="fa-solid fa-bolt"></i> PAY NOW
                </button>
            </div>
        </div>
        @elseif($hasPendingSlipGlobally)
        <div class="bg-yellow-50 rounded-lg sm:rounded-xl border border-yellow-200 p-4 sm:p-6 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-yellow-800">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-yellow-200 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hourglass-half fa-spin"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base sm:text-lg">Payment Under Review</h3>
                    <p class="font-bold text-xs sm:text-sm text-yellow-700 opacity-90 mt-1">Your payment slip has been submitted and is currently being reviewed by our workshop admins.</p>
                </div>
            </div>
        </div>
        @endif
    @endif
