<x-app-layout>

    <style>
        /* Mobile warning */
        .mobile-pos-warning { display: none; }
        @media (max-width: 768px) {
            .pos-wrapper { display: none !important; }
            .mobile-pos-warning { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; text-align: center; padding: 20px; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .custom-scrollbar-hide::-webkit-scrollbar { display: none; }
        .custom-scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-arrows::-webkit-outer-spin-button,
        .hide-arrows::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        .hide-arrows {
            -moz-appearance: textfield;
        }
    </style>
    
    <div class="mobile-pos-warning">
        <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <path stroke-linecap="round" d="M10 20v-2m4 2v-2" />
        </svg>
        <h2 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Desktop View Required</h2>
        <p class="text-sm font-semibold text-slate-500">The POS System is designed for wider screens. Please use a tablet or desktop computer to process orders.</p>
    </div>

    <!-- Wrapping to override default app layout padding for a full height POS -->
    <div class="pos-wrapper -mt-6 -mx-4 lg:-mx-8 h-[calc(100vh-64px)] bg-slate-100 p-3 lg:p-4" x-data="finalizeBill()" >
        
        <div class="flex flex-col lg:flex-row gap-3 lg:gap-4 h-full overflow-hidden">

            <!-- ===================== Left: Services ===================== -->
            <div class="flex-1 lg:flex-[1.2] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">
                
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

                <!-- Search bar & Category Filters Container -->
                <div x-data="{ searchExpanded: false }" class="border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10 relative flex items-start gap-2 p-3">
                    
                    <!-- Search Bar Icon (Collapsible) -->
                    <div class="relative shrink-0 mt-0.5" :class="searchExpanded ? 'w-full max-w-sm absolute top-2 left-2 z-50 bg-white rounded-xl shadow-lg' : ''">
                        <button @click="searchExpanded = true" x-show="!searchExpanded" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <div x-show="searchExpanded" x-cloak class="w-full relative flex items-center transition-all animate-fade-in">
                            <input type="text" x-model="searchQuery" x-ref="searchInput" placeholder="Search services..."
                                class="w-full pl-10 pr-10 py-2 bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[12px] font-bold text-slate-800 transition-all outline-none placeholder:text-slate-400">
                            <svg class="absolute left-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button @click="searchExpanded = false; searchQuery = ''" class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Category Filters (2 rows with horizontal scroll) -->
                    <div class="flex-1 min-w-0 relative group" x-data="{
                        hasOverflow: false,
                        checkOverflow() {
                            this.$nextTick(() => {
                                if (this.$refs.catScroll) {
                                    this.hasOverflow = this.$refs.catScroll.scrollWidth > this.$refs.catScroll.clientWidth;
                                }
                            });
                        },
                        init() {
                            this.checkOverflow();
                            // If you have search filtering that alters width, it'll trigger resize or you can call checkOverflow
                        },
                        scrollCatLeft() { $refs.catScroll.scrollBy({ left: -200, behavior: 'smooth' }); },
                        scrollCatRight() { $refs.catScroll.scrollBy({ left: 200, behavior: 'smooth' }); }
                    }" @resize.window="checkOverflow()">
                        <!-- Left Arrow -->
                        <button x-show="hasOverflow" x-transition @click="scrollCatLeft()" class="absolute -left-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        
                        <!-- Right Arrow -->
                        <button x-show="hasOverflow" x-transition @click="scrollCatRight()" class="absolute -right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <div class="flex flex-col min-w-0 gap-1.5 pb-1">
                            <!-- Bottom Layer: Parent Categories View (2 Rows) -->
                            <div class="relative w-full">
                                <div x-ref="catScroll" class="overflow-x-auto scrollbar-none custom-scrollbar-hide">
                                    <div class="grid grid-rows-2 grid-flow-col gap-1.5 w-max">
                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                            <button @click="activeCategory = cat.id; activeSubcategory = (cat.children && cat.children.length > 0) ? cat.children[0].id : null" 
                                                :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                                class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[70px] flex items-center gap-1 justify-center">
                                                <span x-text="cat.name"></span>
                                                <template x-if="cat.children && cat.children.length > 0">
                                                    <svg class="w-3 h-3 opacity-60 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                </template>
                                            </button>
                                        </template>
                                        <button @click="activeCategory = 'uncategorized'; activeSubcategory = null" 
                                            :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[70px]">
                                            Uncategorized
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Distinct 3rd Row: Subcategories -->
                            <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0">
                                <div class="overflow-x-auto custom-scrollbar mt-1 pt-1.5 border-t border-slate-200/60 pb-1">
                                    <div class="flex items-center gap-2 w-max pl-1 pr-2">

                                        <template x-for="sub in activeCategoryData.children" :key="sub.id">
                                            <button @click="activeSubcategory = sub.id" 
                                                :class="activeSubcategory === sub.id ? 'bg-indigo-600 text-white shadow-sm ring-1 ring-indigo-700' : 'bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100'"
                                                class="px-3 py-0.5 text-[10px] font-bold rounded-md transition-all whitespace-nowrap min-w-[60px]"
                                                x-text="sub.name">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Services Grid -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar z-10">
                    <div class="grid grid-cols-4 sm:grid-cols-6 xl:grid-cols-6 gap-2 lg:gap-3 auto-rows-max">
                        

                        <!-- Service Cards -->
                        <template x-for="service in filteredServices" :key="service.id">
                            <button @click="addToCart(service)"
                                class="col-span-2 sm:col-span-1 xl:col-span-1 flex flex-col p-3 rounded-[14px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_16px_rgba(0,0,0,0.06)] hover:-translate-y-0.5 transition-all duration-300 text-left group overflow-hidden relative justify-center items-center text-center"
                                :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                
                                <span class="text-[12px] font-black text-slate-800 leading-tight tracking-tight line-clamp-2 mb-1" x-text="service.name"></span>
                                <div class="flex items-end justify-center gap-1">
                                    <span class="text-[13px] font-black text-blue-600" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                    <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest pb-0.5" x-text="service.price_type === 'per_kg' ? '/KG' : '/ITM'"></span>
                                </div>
                            </button>
                        </template>

                        <!-- Empty State -->
                        <div x-show="filteredServices.length === 0" class="col-span-full py-16 flex flex-col items-center justify-center text-center opacity-70">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">No services found</p>
                        </div>
                    </div>
                </div>
            </div>

            
            <!-- ===================== Right: Order Panel ===================== -->
            <div class="flex-1 lg:flex-[0.8] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">
                
                <!-- Customer Section -->
                <div class="p-4 border-b-2 border-slate-100 bg-white z-20 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Customer Details</span>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl relative flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                            {{ substr($dispatch->order->customer->name ?? $order->customer->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <span class="text-[14px] font-black text-blue-900 block truncate">{{ $dispatch->order->customer->name ?? $order->customer->name ?? 'Unknown Customer' }}</span>
                            <span class="text-[11px] font-bold text-blue-600 block">{{ $dispatch->order->customer->phone ?? $order->customer->phone ?? 'No Phone' }}</span>
                        </div>
                    </div>
                    @if((isset($dispatch) && $dispatch->order->notes) || (isset($order) && $order->notes))
                        <div class="mt-1 p-2 bg-yellow-50 text-yellow-800 text-xs font-bold rounded-lg border border-yellow-200 flex gap-2">
                            <i class="fa-solid fa-note-sticky mt-0.5"></i> {{ $dispatch->order->notes ?? $order->notes }}
                        </div>
                    @endif
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto bg-slate-50/50 p-2 custom-scrollbar">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-white border-b lg:border border-slate-100 lg:rounded-xl p-3 mb-2 flex gap-3 shadow-sm relative group transition-all">
                            
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            </div>
                            
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <div class="text-[9px] font-bold text-slate-400 leading-none uppercase tracking-widest mb-0.5 truncate" x-text="getCategoryString(item.service_type_id)"></div>
                                <div class="text-[13px] font-black text-slate-800 leading-tight mb-0.5 line-clamp-2" x-text="item.name"></div>
                            </div>
                            
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg p-0.5">
                                    <button @click="if(item.qty > 0.5) item.qty = item.qty - 0.5" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-white rounded-md transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg></button>
                                    <input type="number" step="any" x-model.number="item.qty" class="w-10 text-center text-[13px] font-black text-slate-800 bg-transparent border-none focus:ring-0 p-0 hide-arrows">
                                    <button @click="item.qty = item.qty + 0.5" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-white rounded-md transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                                </div>
                                <div class="flex items-center gap-1 w-[80px]">
                                    <span class="text-[12px] font-bold text-slate-400">Rs.</span>
                                    <input type="number" step="any" x-model.number="item.base_price" class="w-full text-right text-[14px] font-black text-blue-600 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-blue-500 focus:ring-0 p-0 hide-arrows">
                                </div>
                                <button @click="cart.splice(index, 1)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Cart -->
                    <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center opacity-60">
                        <div class="w-20 h-20 bg-white rounded-full shadow-sm flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <p class="text-[15px] font-black text-slate-400">Cart is empty</p>
                        <p class="text-[12px] font-bold text-slate-400 mt-1">Select services to add.</p>
                    </div>
                </div>

                <!-- Dark Checkout Summary Bar -->
                <div class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">
                    
                    <div class="p-3">
                        <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                            
                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[13px] font-black" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>
                            
                            <!-- Total -->
                            <div class="flex justify-between items-center bg-blue-900/40 p-2 rounded-lg border border-blue-500/30">
                                <span class="text-blue-300 text-[11px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[15px] font-black" x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>
                            
                            <!-- Discount -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Discount (%)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="discountPercent" class="w-12 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500" min="0" max="100">
                                </div>
                            </div>
                            
                            <!-- Advance -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Advance (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="requestedAdvance" class="w-16 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500" placeholder="0">
                                </div>
                            </div>

                            <!-- Delivery Fee -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group col-span-2">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Delivery Fee (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="deliveryFee" class="w-20 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500" placeholder="0">
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-950/50 rounded-b-[24px]">
                        <button @click="saveOrder('request_payment')" :disabled="cart.length === 0 || isSaving" class="py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-black text-[12px] tracking-wide transition-all disabled:opacity-50 flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Save & Request
                        </button>
                        
                        <button @click="saveOrder('start_processing')" :disabled="cart.length === 0 || isSaving" class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Save & Process
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function finalizeBill() {
            return {
                services: @json($services),
                categories: @json($categories),
                searchQuery: "",
                activeCategory: null,
                activeSubcategory: null,
                cart: @json($cartItems),
                
                deliveryFee: {{ (float)($dispatch->order->delivery_fee ?? $order->delivery_fee ?? 0) }},
                discountPercent: 0,
                requestedAdvance: null,
                isSaving: false,
                orderId: {{ $dispatch->order_id ?? $order->id ?? 0 }},
                orderNumber: '{{ $dispatch->order->order_number ?? $order->order_number ?? '' }}',
                
                init() {
                    if(this.categories.length > 0) {
                        this.activeCategory = this.categories[0].id;
                        // Auto-select first sub-category if it exists
                        const firstCat = this.categories[0];
                        if (firstCat.children && firstCat.children.length > 0) {
                            this.activeSubcategory = firstCat.children[0].id;
                        }
                    }
                },

                get filteredCategories() {
                    if(!this.searchQuery) return this.categories;
                    let q = this.searchQuery.toLowerCase();
                    return this.categories.filter(c => c.name.toLowerCase().includes(q));
                },

                get activeCategoryData() {
                    return this.categories.find(c => c.id === this.activeCategory);
                },

                get currentSubcategories() {
                    if (!this.activeCategory) return [];
                    const cat = this.categories.find(c => c.id === this.activeCategory);
                    return cat && cat.children ? cat.children : [];
                },

                get filteredServices() {
                    let result = this.services;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        result = result.filter(s => s.name.toLowerCase().includes(q));
                    }
                    if (this.activeCategory) {
                        const cat = this.categories.find(c => c.id === this.activeCategory);
                        let validIds = [this.activeCategory];
                        if (cat && cat.children) {
                            validIds.push(...cat.children.map(c => c.id));
                        }
                        
                        if (this.activeSubcategory) {
                            // Show sub-category services AND services directly under the parent category
                            validIds = [this.activeSubcategory, this.activeCategory];
                        }
                        
                        result = result.filter(s => {
                            let sId = s.service_category_id || s.category_id;
                            return validIds.map(String).includes(String(sId));
                        });
                    } else {
                        result = [];
                    }
                    return result;
                },

                addToCart(service) {
                    // Match only by service_type_id — so editing price later doesn't create duplicates
                    let existing = this.cart.find(i => i.service_type_id === service.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            service_type_id: service.id,
                            name: service.name,
                            base_price: service.base_price,
                            qty: 1
                        });
                    }
                },
                
                getCategoryString(serviceId) {
                    let serviceArray = this.services || this.allServices || [];
                    let service = serviceArray.find(s => s.id === serviceId);
                    if (!service) return '';
                    let cId = service.service_category_id || service.category_id;
                    for (let cat of this.categories) {
                        if (cat.id == cId) return cat.name;
                        if (cat.children) {
                            for (let sub of cat.children) {
                                if (sub.id == cId) return cat.name + ' > ' + sub.name;
                            }
                        }
                    }
                    return '';
                },
                
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.base_price * item.qty), 0);
                },
                
                get total() {
                    let baseSub = this.subtotal;
                    let discountAmount = baseSub * (this.discountPercent / 100);
                    let calculated = (baseSub - discountAmount) + this.deliveryFee;
                    return Math.max({{ $minimumDeliveryCost }}, calculated);
                },
                
                async saveOrder(action) {
                    if (this.cart.length === 0) return alert("Cart is empty!");
                    this.isSaving = true;
                    
                    try {
                        const res = await fetch(`/orders/${this.orderNumber}/update`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                            },
                            body: JSON.stringify({
                                cart: this.cart,
                                discount_percentage: this.discountPercent,
                                requested_advance: this.requestedAdvance,
                                next_action: action
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.href = "{{ route('workshop.dashboard') }}";
                        } else {
                            alert(data.message || "Failed to update");
                        }
                    } catch (e) {
                        alert("Error saving order");
                    }
                    
                    this.isSaving = false;
                }
            }
        }
    </script>
</x-app-layout>


