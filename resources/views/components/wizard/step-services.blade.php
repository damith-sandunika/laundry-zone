            <!-- Step 1/3: Services (Drill-Down UI) -->
            <div x-show="currentView === 'services'" x-transition.opacity.duration.300ms class="pb-12">
                @if(!$isHomepage)
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-slate-800">Select Services</h3>
                    <p class="text-sm text-slate-500 mt-1">Navigate through categories to add items to your cart.</p>
                </div>
                @endif

                <!-- Drill Down Container -->
                <div class="flex flex-col">
                    
                    <!-- Header / Navigation Bar -->
                    <div class="flex items-center justify-center mb-6 sm:mb-8">
                        <!-- Breadcrumbs -->
                        <div class="inline-flex items-center justify-center flex-wrap gap-2 sm:gap-3 text-sm sm:text-base font-bold text-slate-800 bg-white px-5 sm:px-8 py-2.5 sm:py-3.5 rounded-full shadow-sm border border-slate-100">
                            <button type="button" @click="currentViewLevel = 0" class="hover:text-blue-600 transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-blue-500"></i> All Categories
                            </button>
                            
                            <span x-show="currentViewLevel > 0" class="text-slate-300"><i class="fa-solid fa-chevron-right text-[10px] sm:text-xs"></i></span>
                            
                            <button type="button" x-show="currentViewLevel > 0" @click="currentViewLevel = 1" class="hover:text-blue-600 transition-colors" x-text="selectedMainCategory?.name"></button>
                            
                            <span x-show="currentViewLevel > 1" class="text-slate-300"><i class="fa-solid fa-chevron-right text-[10px] sm:text-xs"></i></span>
                            
                            <span x-show="currentViewLevel > 1" class="text-blue-600" x-text="selectedSubcategory?.name || selectedMainCategory?.name"></span>
                        </div>
                    </div>

                    <div class="flex-1">
                        <!-- Level 0: Main Categories Grid -->
                        <div x-show="currentViewLevel === 0">
                            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                                <template x-for="cat in categories" :key="cat.id">
                                    <button type="button" @click="selectLevel1(cat)" class="w-[30%] sm:w-[22%] md:w-[18%] bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group">
                                        <div class="w-full aspect-[4/3] bg-blue-50 text-blue-600 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="cat.image_path">
                                                    <img :src="cat.image_path?.startsWith('http') ? cat.image_path : '/' + cat.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!cat.image_path">
                                                    <i class="fa-solid fa-shirt text-4xl opacity-40"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-4 w-full bg-white relative z-10 border-t border-slate-50 flex-1 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full" x-text="cat.name"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Level 1: Subcategories Grid -->
                        <div x-show="currentViewLevel === 1">
                            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                                <template x-for="sub in currentSubcategories" :key="sub.id">
                                    <button type="button" @click="selectLevel2(sub)" class="w-[30%] sm:w-[22%] md:w-[18%] bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group">
                                        <div class="w-full aspect-[4/3] bg-slate-50 text-slate-400 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="sub.image_path">
                                                    <img :src="sub.image_path?.startsWith('http') ? sub.image_path : '/' + sub.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!sub.image_path">
                                                    <i class="fa-solid fa-layer-group text-3xl opacity-40 group-hover:text-blue-400 transition-colors"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-3 w-full bg-white relative z-10 border-t border-slate-50 flex-1 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full" x-text="sub.name"></span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="currentSubcategories.length === 0" class="col-span-full py-12 text-center text-slate-500 text-sm font-medium">
                                    No subcategories found.
                                </div>
                            </div>
                        </div>

                        <!-- Level 2: Services List -->
                        <div x-show="currentViewLevel === 2" class="space-y-4">
                            <template x-for="service in currentServices" :key="service.id">
                                <div class="bg-white border-2 border-slate-100 rounded-xl p-4 flex gap-4 transition-all hover:border-blue-200 hover:shadow-md cursor-pointer group" @click="openQtyModal(service)">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-blue-600 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-jug-detergent"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition-colors" x-text="service.name"></div>
                                        <div class="text-[10px] text-slate-500 font-medium mt-0.5 leading-tight" x-text="service.description || 'Professional cleaning'"></div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="text-blue-600 font-bold text-sm">LKR <span x-text="service.base_price"></span></div>
                                            <div class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : (service.price_type === 'per_pair' ? 'Per Pair' : 'Per Item'))"></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center">
                                        <button type="button" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors flex items-center justify-center shadow-sm">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="currentServices.length === 0" class="py-12 text-center text-slate-500 text-sm font-medium">
                                No services found in this category.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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


