<!-- ===================== Checkout Modal ===================== -->
        <div x-show="showCheckoutModal" x-cloak
            class="fixed inset-0 z-[99999] flex items-center justify-center p-3 sm:p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="showCheckoutModal = false"></div>
            
            <div class="relative bg-white rounded-[22px] shadow-2xl w-full max-w-lg z-10 flex flex-col overflow-hidden animate-slide-up max-h-[90vh]" @click.stop>
                
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex justify-between items-center">
                    <h3 class="font-black text-base text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Checkout Order
                    </h3>
                    <button @click="showCheckoutModal = false" class="p-1.5 -mr-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-4 sm:p-5 space-y-4 overflow-y-auto max-h-[72vh] custom-scrollbar">
                    
                    <!-- Delivery Date -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Estimated Delivery Date</label>
                        <div class="flex gap-2 items-center">
                            <input type="datetime-local" x-model="dueDate"
                                class="flex-1 bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Payment Timing</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="paymentMode = 'pay_now'"
                                :class="paymentMode === 'pay_now' ? 'bg-blue-50 border-blue-200 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                class="flex flex-col items-center justify-center p-4 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_now' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="font-black text-slate-800 text-[13px]">Pay Now</span>
                            </button>

                            <button @click="paymentMode = 'pay_on_pickup'"
                                :class="paymentMode === 'pay_on_pickup' ? 'bg-amber-50 border-amber-200 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                class="flex flex-col items-center justify-center p-4 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_on_pickup' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400'"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="font-black text-slate-800 text-[13px]">Pay on Pickup</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sub options for Pay Now -->
                    <div x-show="paymentMode === 'pay_now'" x-transition.opacity
                        class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <button @click.stop="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-emerald-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            💵 CASH
                        </button>
                        <button @click.stop="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-violet-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            💳 CARD
                        </button>
                        <button @click.stop="paymentMethod = 'bank'"
                            :class="paymentMethod === 'bank' ? 'bg-blue-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            🏦 BANK
                        </button>
                    </div>

                    <!-- Discount & Advance Amount (Combined Row) -->
                    <div class="mb-2 px-1">
                        <div class="flex flex-col sm:flex-row gap-3 items-start">
                            <!-- Discount control in one clean flex line -->
                            <div class="flex-1 w-full">
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Discount</label>
                                <div class="flex items-center gap-1.5 w-full">
                                    <div class="inline-flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200 shrink-0">
                                        <button type="button" @click="discountType = 'percentage'"
                                            :class="discountType === 'percentage' ? 'bg-white text-blue-600 shadow-sm font-black' : 'text-slate-400 font-semibold'"
                                            class="px-2.5 py-1 text-[11px] rounded-lg transition-all cursor-pointer">
                                            %
                                        </button>
                                        <button type="button" @click="discountType = 'fixed'"
                                            :class="discountType === 'fixed' ? 'bg-white text-blue-600 shadow-sm font-black' : 'text-slate-400 font-semibold'"
                                            class="px-2.5 py-1 text-[11px] rounded-lg transition-all cursor-pointer">
                                            Rs.
                                        </button>
                                    </div>
                                    <div class="relative flex-1">
                                        <input type="number" x-model.number="discountValue" min="0" :max="discountType === 'percentage' ? 100 : subtotal" step="any"
                                            :placeholder="discountType === 'percentage' ? '0%' : '0.00'"
                                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-3.5 py-2 text-[14px] font-black text-slate-800 outline-none transition-all pr-10">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400 pointer-events-none" x-text="discountType === 'percentage' ? '%' : 'Rs.'"></span>
                                    </div>
                                </div>
                                <template x-if="calculatedDiscount > 0">
                                    <div class="text-[11px] font-bold text-emerald-600 mt-1 flex items-center gap-1">
                                        <span>-Rs. <span x-text="calculatedDiscount.toFixed(2)"></span></span>
                                        <span x-show="discountType === 'fixed' && subtotal > 0" class="text-slate-400 font-normal" x-text="'(' + ((calculatedDiscount/subtotal)*100).toFixed(1) + '%)'"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Advance Amount (when pay_now) -->
                            <div class="flex-1 w-full" x-show="paymentMode === 'pay_now'">
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Advance Amount</label>
                                <input type="number" x-model="advanceAmount" min="0" step="0.01" :placeholder="'Full: Rs. ' + total.toFixed(2)"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-3.5 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl">
                        <span class="text-[13px] font-black text-blue-900 uppercase tracking-widest">Final Total</span>
                        <div class="text-right">
                            <span x-show="calculatedDiscount > 0" class="text-sm font-bold text-slate-400 line-through block" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            <span class="text-2xl font-black text-blue-700" x-text="'Rs. ' + total.toFixed(2)"></span>
                        </div>
                    </div>

                </div>

                <div class="p-6 pt-2 bg-white border-t border-slate-100">
                    <button @click="placeOrder" :disabled="isPlacingOrder"
                        :class="isPlacingOrder ? 'opacity-60 cursor-not-allowed' : 'hover:shadow-blue-500/40 hover:-translate-y-0.5'"
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[15px] font-black rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                        <template x-if="!isPlacingOrder">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="isPlacingOrder">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                        </template>
                        <span x-text="isPlacingOrder ? 'Processing...' : 'OK'"></span>
                    </button>
                </div>
            </div>
        </div>
