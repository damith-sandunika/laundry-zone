                <!-- Dark Checkout Summary Bar -->
                <div
                    class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">

                    <div class="p-2.5">
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5">

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md">
                                <span
                                    class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[12px] font-black"
                                    x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>

                            <!-- Total -->
                            <div
                                class="flex justify-between items-center bg-blue-900/40 p-1.5 rounded-md border border-blue-500/30">
                                <span class="text-blue-300 text-[10px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[14px] font-black"
                                    x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>

                            <!-- Discount -->
                            <div class="flex flex-col justify-center bg-slate-800/50 p-1.5 rounded-md group">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">
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
                                <div class="flex items-center justify-between mt-0.5">
                                    <span x-show="discountAmount > 0" class="text-[9px] font-semibold text-emerald-400/80"
                                        x-text="discountType === 'percent' ? ('-Rs. ' + discountAmount.toFixed(2)) : ('(' + (subtotal > 0 ? ((discountAmount / subtotal) * 100).toFixed(1) : 0) + '%)')">
                                    </span>
                                    <div class="flex items-center ml-auto">
                                        <input x-show="discountType === 'percent'" type="number" x-model.number="discountPercent"
                                            class="w-12 bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[11px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            min="0" max="100" placeholder="0">
                                        <input x-show="discountType === 'fixed'" type="number" x-model.number="discountFixed"
                                            class="w-16 bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[11px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            min="0" :max="subtotal" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <!-- Advance -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md group">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Advance
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="requestedAdvance"
                                        class="w-14 bg-slate-800 border-none rounded text-right text-white text-[12px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Delivery Fee & Save Button Row -->
                            <div class="col-span-2 grid grid-cols-2 gap-x-2 items-center mt-1">
                                <!-- Delivery Fee -->
                                @if(config('app.delivery_enabled'))
                                <div
                                    class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md group">
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Delivery Fee
                                        (Rs)</span>
                                    <div class="flex items-center">
                                        <input type="number" x-model.number="deliveryFee"
                                            class="w-14 bg-slate-800 border-none rounded text-right text-white text-[12px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            placeholder="0">
                                    </div>
                                </div>
                                @else
                                <div></div>
                                @endif

                                <!-- Save & Request Button -->
                                <button @click="saveOrder('request_payment')" :disabled="cart.length === 0 || isSaving"
                                    class="w-full py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-md font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5">
                                    <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg x-show="isSaving" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Save & Request
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
