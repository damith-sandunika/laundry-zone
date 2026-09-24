<!-- ===================== Order Success Modal ===================== -->
        <div x-data="{ show: false, orderId: '', orderNum: '', custName: '', total: '', payMode: '', dueDate: '' }"
            x-on:order-placed.window="show = true; orderId = $event.detail.orderId; orderNum = $event.detail.orderNumber; custName = $event.detail.customerName; total = $event.detail.total; payMode = $event.detail.paymentMode; dueDate = $event.detail.dueDate; if({{ \App\Models\Setting::get('auto_print_pos_chit', '0') == '1' ? 'true' : 'false' }}) { printOrderChit(orderId); }"
            x-show="show" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 70;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-sm z-10 text-center p-8 animate-slide-up" @click.stop>
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-5 border-4 border-white shadow-[0_0_0_4px_rgba(220,252,231,1)]">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-2xl font-black text-slate-900 mb-2 tracking-tight">Order Placed!</h3>
                <p class="text-[13px] font-bold text-slate-500 mb-6">The laundry order has been registered successfully.</p>

                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 text-left space-y-3.5 mb-6 shadow-inner">
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Order #:</span>
                        <span class="font-black text-blue-600 text-[14px]" x-text="orderNum"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Customer:</span>
                        <span class="font-black text-slate-800 text-[14px]" x-text="custName"></span>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200/60 pb-2">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Total:</span>
                        <span class="font-black text-emerald-600 text-[15px]" x-text="'Rs. ' + total"></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-slate-200/60" x-show="dueDate">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Delivery Date:</span>
                        <span class="font-black text-orange-600 text-[13px]"
                            x-text="dueDate ? new Date(dueDate).toLocaleDateString('en-US', {day:'2-digit', month:'short', year:'numeric'}) : ''"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Payment:</span>
                        <span class="font-black text-slate-700 text-[13px]"
                            x-text="payMode === 'pay_now' ? 'Paid (Cash/Card)' : 'Pay on Pickup'"></span>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button @click="printReceipt(orderId)"
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-[14px] font-black rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Print Thermal Bill
                    </button>

                    <button @click="show = false"
                        class="w-full py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[13px] font-bold rounded-xl transition-colors">
                        Skip & Create New Order
                    </button>
                </div>
            </div>
        </div>
