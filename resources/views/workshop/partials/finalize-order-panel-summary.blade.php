                <!-- Dark Checkout Summary Bar -->
                <div
                    class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">

                    <div class="p-3">
                        <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg">
                                <span
                                    class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[13px] font-black"
                                    x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>

                            <!-- Total -->
                            <div
                                class="flex justify-between items-center bg-blue-900/40 p-2 rounded-lg border border-blue-500/30">
                                <span class="text-blue-300 text-[11px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[15px] font-black"
                                    x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>

                            <!-- Discount -->
                            <div class="flex flex-col justify-center bg-slate-800/50 p-2 rounded-lg group">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">
                                        Discount (<span x-text="discountType === 'percent' ? '%' : 'Rs'"></span>)
                                    </span>
                                    <button type="button" @click="toggleDiscountType()"
                                        class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black transition-all bg-blue-600/30 text-blue-300 hover:bg-blue-600/50 border border-blue-500/30 shadow-sm"
                                        :title="'Switch to ' + (discountType === 'percent' ? 'Fixed (Rs)' : 'Percentage (%)')">
                                        <span :class="discountType === 'percent' ? 'text-white font-black' : 'text-slate-400'">%</span>
                                        <span class="text-slate-500 text-[8px]">⇄</span>
                                        <span :class="discountType === 'fixed' ? 'text-white font-black' : 'text-slate-400'">Rs</span>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span x-show="discountAmount > 0" class="text-[10px] font-semibold text-emerald-400/80"
                                        x-text="discountType === 'percent' ? ('-Rs. ' + discountAmount.toFixed(2)) : ('(' + (subtotal > 0 ? ((discountAmount / subtotal) * 100).toFixed(1) : 0) + '%)')">
                                    </span>
                                    <div class="flex items-center ml-auto">
                                        <input x-show="discountType === 'percent'" type="number" x-model.number="discountPercent"
                                            class="w-14 bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            min="0" max="100" placeholder="0">
                                        <input x-show="discountType === 'fixed'" type="number" x-model.number="discountFixed"
                                            class="w-20 bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            min="0" :max="subtotal" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Advance -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Advance
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="requestedAdvance"
                                        class="w-16 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Delivery Fee -->
                            @if(config('app.delivery_enabled'))
                            <div
                                class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group col-span-2">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Delivery Fee
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="deliveryFee"
                                        class="w-20 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-950/50 rounded-b-[24px]">
                        <button @click="saveOrder('request_payment')" :disabled="cart.length === 0 || isSaving"
                            class="py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-black text-[12px] tracking-wide transition-all disabled:opacity-50 flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Save & Request
                        </button>

                        <button @click="saveOrder('start_processing')" :disabled="cart.length === 0 || isSaving"
                            class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Save & Process
                        </button>
                    </div>
                </div>
