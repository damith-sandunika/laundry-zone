<!-- ===================== Checkout Modal ===================== -->
        <div x-show="showCheckoutModal" x-cloak
            x-effect="if(showCheckoutModal) $nextTick(() => { const dp = document.getElementById('due-date-input'); if(dp) { try { dp.showPicker(); } catch(e) { dp.focus(); dp.click(); } } })"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="showCheckoutModal = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-md z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop>
                
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Checkout Order
                    </h3>
                    <button @click="showCheckoutModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto max-h-[75vh] custom-scrollbar">
                    
                    <!-- Delivery Date -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Estimated Delivery Date</label>
                        <div class="flex gap-2">
                            <input type="datetime-local" id="due-date-input" x-model="dueDate"
                                class="flex-1 bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer">
                            <button @click="document.getElementById('due-date-input').blur()" type="button" class="px-4 bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Done
                            </button>
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

                    <!-- Discount & Advance Amount -->
                    <div class="mb-3 px-1 flex gap-3">
                        <div class="flex-1">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Discount (%)</label>
                            <input type="number" x-model.number="discountPercentage" min="0" max="100" step="1"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                        </div>
                        <div class="flex-1" x-show="paymentMode === 'pay_now'">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Advance Amount</label>
                            <input type="number" x-model="advanceAmount" min="0" step="0.01" :placeholder="'Full: Rs. ' + total.toFixed(2)"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                        </div>
                    </div>



                    <!-- Total -->
                    <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl">
                        <span class="text-[13px] font-black text-blue-900 uppercase tracking-widest">Final Total</span>
                        <div class="text-right">
                            <span x-show="discountPercentage > 0" class="text-sm font-bold text-slate-400 line-through block" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
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
