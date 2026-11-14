            <!-- Quantity Selection Modal -->
            <div x-show="isQtyModalOpen" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div x-show="isQtyModalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isQtyModalOpen = false"></div>
                <div x-show="isQtyModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-lg shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
                    <div class="p-6 text-center">
                        <div class="text-xs font-semibold text-slate-500 mb-1">
                            <span x-text="selectedMainCategory?.name"></span>
                            <span x-show="selectedSubcategory"> / <span x-text="selectedSubcategory?.name"></span></span>
                        </div>
                        <h3 class="text-xl font-semibold text-slate-800 mb-1" x-text="selectedService?.name"></h3>
                        <p class="text-sm font-medium text-slate-500 mb-6">LKR <span x-text="selectedService?.base_price"></span></p>
                        
                        <div class="flex items-center justify-center gap-6">
                            <button type="button" @click="if(serviceQty > 1) serviceQty--" class="w-10 h-10 rounded-md border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-medium transition-colors"><i class="fa-solid fa-minus"></i></button>
                            <div class="text-2xl font-semibold text-slate-800 w-12 text-center" x-text="serviceQty"></div>
                            <button type="button" @click="serviceQty++" class="w-10 h-10 rounded-md border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-medium transition-colors"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-3">
                        <button type="button" @click="isQtyModalOpen = false" class="flex-1 py-2.5 rounded-md font-medium text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="button" @click="confirmAddToCart()" class="flex-1 py-2.5 rounded-md font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Global Checkout Modal -->
            <div x-show="isGlobalCheckoutModalOpen" style="display:none;" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                <div x-show="isGlobalCheckoutModalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isGlobalCheckoutModalOpen = false"></div>
                <div x-show="isGlobalCheckoutModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-lg shadow-xl w-full max-w-md flex flex-col max-h-[90vh] overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-white">
                        <h3 class="text-lg font-semibold text-slate-800">Finalize Order</h3>
                        <button type="button" @click="isGlobalCheckoutModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-6">
                        <!-- Turnaround Type -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Do you need quick service? <span class="text-red-500">*</span></label>
                            <p class="text-xs text-slate-500 font-medium mb-3">Charges apply only to the base services (not applied to extras).</p>
                            <select x-model="globalTurnaround" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-md focus:ring-2 focus:ring-blue-500 font-medium">
                                <template x-for="tt in turnaroundTypes" :key="tt.id">
                                    <option :value="tt.id" x-text="tt.name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Extras -->
                        <div x-show="extras.length > 0">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Do you need any extra services?</label>
                            <p class="text-xs text-slate-500 font-medium mb-3">Add extra services to your order.</p>
                            <div class="space-y-2">
                                <template x-for="extra in extras" :key="extra.id">
                                    <div class="flex items-center justify-between p-3 rounded-md border border-slate-200 bg-white shadow-sm">
                                        <div class="flex-1">
                                            <div class="text-sm font-bold text-slate-800" x-text="extra.name"></div>
                                            <div class="text-xs font-semibold text-blue-600 mt-0.5" x-text="'+ LKR ' + extra.price + ' / item'"></div>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <button type="button" @click="globalExtras[extra.id] = Math.max(0, (globalExtras[extra.id] || 0) - 1)" class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                            <span class="w-4 text-center text-sm font-bold text-slate-800" x-text="globalExtras[extra.id] || 0"></span>
                                            <button type="button" @click="globalExtras[extra.id] = (globalExtras[extra.id] || 0) + 1" class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Grand Total</div>
                            <div class="text-xl font-semibold text-blue-600">LKR <span x-text="calculatedGrandTotal.toFixed(2)"></span></div>
                            <div x-show="calculatedGrandTotal < minLimit" class="text-[10px] text-red-500 font-medium mt-1">Min. order: LKR <span x-text="minLimit"></span></div>
                        </div>
                        <button type="button" @click="proceedToDelivery()" :disabled="calculatedGrandTotal < minLimit" class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white py-3 rounded-md font-semibold transition-colors shadow-sm text-center flex items-center justify-center gap-2">
                            Continue <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
