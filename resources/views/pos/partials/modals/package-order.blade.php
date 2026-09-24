<!-- ===================== Package Order Modal ===================== -->
        <div x-show="showPackageModal" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 65;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="showPackageModal = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-lg z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop>
                <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Use Active Package
                        </h3>
                        <p class="text-[11px] font-bold text-slate-500 mt-1" x-text="activePackage?.package?.name || ''"></p>
                    </div>
                    <button @click="showPackageModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 overflow-y-auto max-h-[60vh] custom-scrollbar">
                    
                    <div class="bg-blue-50/50 rounded-xl p-4 border border-blue-100">
                        <p class="text-xs font-bold text-slate-600 mb-3">Enter the quantities the customer is providing today. Any excess will be charged at standard rates.</p>
                        <div class="space-y-3">
                            <template x-for="(item, index) in packageCart" :key="index">
                                <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-slate-200">
                                    <div>
                                        <div class="text-[13px] font-black text-slate-800" x-text="item.name"></div>
                                        <div class="text-[10px] font-bold text-blue-600 mt-0.5">Remaining: <span x-text="item.remaining"></span></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="if(item.qty > 0) item.qty--" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-black flex items-center justify-center transition-colors">−</button>
                                        <input type="number" x-model.number="item.qty" @input="if(item.qty > item.remaining) item.qty = item.remaining" class="w-16 text-center text-[14px] font-black border-2 border-slate-200 rounded-lg py-1 focus:border-blue-500 outline-none" min="0" :max="item.remaining">
                                        <button @click="if(item.qty < item.remaining) item.qty++" :disabled="item.qty >= item.remaining" :class="item.qty >= item.remaining ? 'opacity-50 cursor-not-allowed' : ''" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-black flex items-center justify-center transition-colors">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Estimated Delivery Date</label>
                        <input type="datetime-local" x-model="dueDate" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer">
                    </div>

                </div>

                <div class="p-6 pt-0 bg-white border-t border-slate-100 mt-4">
                    <button @click="placePackageOrder" :disabled="isPlacingOrder"
                        class="w-full mt-4 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[15px] font-black rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2 hover:-translate-y-0.5">
                        <template x-if="!isPlacingOrder">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="isPlacingOrder">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                        </template>
                        <span x-text="isPlacingOrder ? 'Processing...' : 'Done (Place Order)'"></span>
                    </button>
                </div>
            </div>
        </div>
