            <!-- Fixed Bottom Cart Bar -->
            <div x-show="currentView === 'services' && cart.length > 0" x-transition.opacity style="display:none;" class="fixed bottom-0 left-0 w-full z-40 bg-white border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
                
                <!-- Expandable Cart List -->
                <div x-show="isCartExpanded" class="bg-slate-50 border-b border-slate-200 max-h-[40vh] overflow-y-auto custom-scrollbar">
                    <div class="max-w-4xl mx-auto p-4 space-y-2">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Your Cart Items</h4>
                        <template x-for="(item, index) in cart" :key="item.cart_key">
                            <div class="flex items-center justify-between bg-white p-3 rounded-md border border-slate-200 shadow-sm">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="font-medium text-sm text-slate-800 truncate" x-text="item.name"></div>
                                    <div class="text-xs font-medium text-blue-600 mt-1">LKR <span x-text="item.base_price"></span> x <span x-text="item.qty"></span> = LKR <span x-text="(parseFloat(item.base_price) * item.qty).toFixed(2)"></span></div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="flex items-center bg-slate-50 rounded-md p-1 border border-slate-200">
                                        <button type="button" @click="updateCartQty(index, -1)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 shadow-sm hover:text-red-500 border border-slate-200"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                        <span class="w-8 text-center text-xs font-semibold text-slate-800" x-text="item.qty"></span>
                                        <button type="button" @click="updateCartQty(index, 1)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 shadow-sm hover:text-blue-600 border border-slate-200"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                    </div>
                                    <button type="button" @click="removeFromCartByIndex(index)" class="text-red-400 hover:text-red-600 p-2"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </template>
                        <div x-show="cart.length === 0" class="text-center p-4 text-sm font-bold text-slate-400">Cart is empty</div>
                    </div>
                </div>

                <!-- Main Bar -->
                <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 cursor-pointer" @click="isCartExpanded = !isCartExpanded">
                        <div class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center relative shadow-sm">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span x-show="cart.length > 0" class="absolute -top-2 -right-2 w-5 h-5 bg-blue-600 text-white text-[10px] font-semibold rounded-full flex items-center justify-center shadow-sm" x-text="cart.reduce((sum, i) => sum + i.qty, 0)"></span>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-slate-500 flex items-center gap-1">Cart Total <i class="fa-solid text-[10px] ml-1 transition-transform" :class="isCartExpanded ? 'fa-chevron-down' : 'fa-chevron-up'"></i></div>
                            <div class="text-lg font-semibold text-slate-800">LKR <span x-text="baseCartTotal.toFixed(2)"></span></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button type="button" @click="goToStep(step - 1)" x-show="step > 1" class="hidden sm:flex text-slate-500 hover:text-slate-800 font-medium items-center gap-2 px-4 py-2 border border-transparent hover:border-slate-200 rounded-md transition-all">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="openGlobalCheckoutModal()" :disabled="cart.length === 0" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 sm:px-8 py-2.5 rounded-md font-semibold transition-colors shadow-sm flex items-center gap-2">
                            Checkout <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
