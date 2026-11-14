<!-- Dark Checkout Summary Bar -->
<div class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">
    <div class="p-2">
        <div class="grid grid-cols-2 gap-x-2 gap-y-1 mb-1">
            <!-- Subtotal -->
            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md">
                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Subtotal</span>
                <span class="text-white text-[12px] font-black" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
            </div>

            <!-- Total -->
            <div class="flex justify-between items-center bg-blue-900/40 p-1.5 rounded-md border border-blue-500/30">
                <span class="text-blue-300 text-[10px] font-bold uppercase tracking-wide">Total</span>
                <span class="text-blue-400 text-[13px] font-black" x-text="'Rs. ' + total.toFixed(2)"></span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-1">
            <!-- Discount -->
            <div class="flex flex-col justify-center bg-slate-800/50 px-2 py-1 rounded-md group">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-[9px] font-bold uppercase tracking-wide">
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
                <div class="mt-0.5">
                    <input x-show="discountType === 'percent'" type="number" x-model.number="discountPercent"
                        class="w-full bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[11px] font-black py-0.5 px-1 hide-arrows focus:ring-1 focus:ring-blue-500"
                        min="0" max="100" placeholder="0">
                    <input x-show="discountType === 'fixed'" type="number" x-model.number="discountFixed"
                        class="w-full bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[11px] font-black py-0.5 px-1 hide-arrows focus:ring-1 focus:ring-blue-500"
                        min="0" :max="subtotal" step="0.01" placeholder="0.00">
                </div>
                <span x-show="discountAmount > 0" class="text-[9px] font-semibold text-emerald-400/80 text-right mt-0.5"
                    x-text="discountType === 'percent' ? ('- Rs. ' + discountAmount.toFixed(2)) : ('(' + (subtotal > 0 ? ((discountAmount / subtotal) * 100).toFixed(1) : 0) + '%)')">
                </span>
            </div>

            <!-- Delivery Fee -->
            @if(config('app.delivery_enabled'))
            <div class="flex flex-col justify-center bg-slate-800/50 px-2 py-1 rounded-md group">
                <span class="text-slate-400 text-[9px] font-bold uppercase tracking-wide">Delivery (Rs)</span>
                <input type="number" x-model.number="deliveryFee"
                    class="w-full bg-slate-800 border border-slate-700/50 rounded text-right text-emerald-400 text-[11px] font-black py-0.5 px-1 hide-arrows focus:ring-1 focus:ring-blue-500"
                    placeholder="0">
            </div>
            @endif

        </div>
    </div>

    <!-- Edit Correction Fields -->
    <div class="px-2 pt-2 pb-4 bg-slate-800/80 rounded-b-[24px] border-t border-slate-700/50 mt-1">
        <div class="flex items-end gap-2">
            <div class="flex flex-col gap-1 flex-1">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">Reason <span class="text-red-400">*</span></label>
                <input type="text" x-model="editReason" placeholder="Reason..." class="bg-slate-900 border border-slate-700 rounded-md text-white text-[11px] font-medium py-1 px-2 focus:ring-1 focus:ring-blue-500 w-full outline-none h-[32px]" required>
            </div>
            <button @click="saveOrder()" :disabled="cart.length === 0 || isSaving || !editReason"
                class="h-[32px] px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-md font-black text-[11px] tracking-wide transition-all shadow-[0_2px_10px_rgba(37,99,235,0.4)] hover:shadow-[0_4px_15px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5 shrink-0">
                <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Request
            </button>
        </div>
    </div>
</div>
