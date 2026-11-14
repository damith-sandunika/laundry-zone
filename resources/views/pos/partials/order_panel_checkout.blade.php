    <!-- Fixed Checkout Bar -->
    <div
        class="p-3 bg-white border-t border-slate-100 z-20 shadow-[0_-10px_40px_rgba(0,0,0,0.04)] flex items-center justify-between gap-3">
        <div class="flex flex-col">
            <span
                class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest leading-none mb-1">Total</span>
            <span class="text-[18px] font-black text-slate-900 tracking-tight leading-none"
                x-text="'Rs. ' + subtotal.toFixed(2)"></span>
        </div>

        <div class="flex gap-2 flex-1 justify-end">
            <button
                @click="if(!selectedCustomer) { alert('Please select a customer first.'); return; } if(!showCheckoutModal){ showCheckoutModal = true; if(!dueDate) { let d = new Date(); d.setDate(d.getDate() + 1); d.setHours(17, 0, 0, 0); dueDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16); } }"
                :disabled="cart.length === 0"
                :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5'"
                class="px-4 py-2.5 rounded-xl text-[12px] font-black flex justify-center items-center gap-1.5 transition-all duration-300">
                Checkout
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </div>
