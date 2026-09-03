<x-app-layout>

    <style>
        /* Mobile warning */
        .mobile-pos-warning { display: none; }
        @media (max-width: 768px) {
            .pos-wrapper { display: none !important; }
            .mobile-pos-warning { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 60vh; text-align: center; padding: 20px; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .custom-scrollbar-hide::-webkit-scrollbar { display: none; }
        .custom-scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
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
    <div class="pos-wrapper -mt-6 -mx-4 lg:-mx-8 h-[calc(100vh-64px)] bg-slate-100 p-3 lg:p-4" x-data="posSystem()" @customer-created.window="allCustomers.unshift($event.detail); filterCustomers(); selectCustomer($event.detail);">
        
        <div class="flex flex-col lg:flex-row gap-3 lg:gap-4 h-full overflow-hidden">

            <!-- ===================== Left: Services ===================== -->
            <div class="flex-1 lg:flex-[1.2] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">
                
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

                <!-- Search bar & Category Filters Container -->
                <div x-data="{ searchExpanded: false }" class="border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10 relative">
                    
                    <!-- Search Bar (Collapsible) -->
                    <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100/50">
                        <div class="font-black text-slate-800 text-lg">Services</div>
                        <div class="relative flex items-center justify-end" :class="searchExpanded ? 'w-full max-w-sm ml-4' : 'w-auto'">
                            <button @click="searchExpanded = true" x-show="!searchExpanded" class="w-10 h-10 rounded-full bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                            <div x-show="searchExpanded" x-cloak class="w-full relative flex items-center transition-all origin-right animate-fade-in">
                                <input type="text" x-model="searchQuery" x-ref="searchInput" placeholder="Search services…"
                                    class="w-full pl-10 pr-10 py-2.5 bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[13px] font-bold text-slate-800 transition-all outline-none placeholder:text-slate-400">
                                <svg class="absolute left-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <button @click="searchExpanded = false; searchQuery = ''" class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Category Filters (3 rows with horizontal scroll) -->
                    <div class="px-5 py-3 relative group" x-data="{
                        scrollCatLeft() { $refs.catScroll.scrollBy({ left: -200, behavior: 'smooth' }); },
                        scrollCatRight() { $refs.catScroll.scrollBy({ left: 200, behavior: 'smooth' }); }
                    }">
                        <!-- Left Arrow -->
                        <button @click="scrollCatLeft()" class="absolute left-1 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                        
                        <!-- Right Arrow -->
                        <button @click="scrollCatRight()" class="absolute right-1 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <div x-ref="catScroll" class="overflow-x-auto scrollbar-none custom-scrollbar-hide">
                            <div class="grid grid-rows-3 grid-flow-col gap-2 w-max pb-1">
                                <template x-for="cat in filteredCategories" :key="cat.id">
                                    <button @click="activeCategory = cat.id" 
                                        :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                        class="px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[80px]"
                                        x-text="cat.name">
                                    </button>
                                </template>
                                <button @click="activeCategory = 'uncategorized'" 
                                    :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                    class="px-3 py-1.5 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[80px]">
                                    Uncategorized
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Grid -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar z-10">
                    <div class="grid grid-cols-4 sm:grid-cols-6 xl:grid-cols-8 gap-2 lg:gap-3">
                        
                        <!-- Special Service Card -->
                        <div class="col-span-4 sm:col-span-2 xl:col-span-2 flex flex-col p-4 rounded-[20px] bg-gradient-to-br from-fuchsia-50 to-purple-50 border-2 border-dashed border-purple-300 shadow-sm hover:shadow-md transition-shadow group">
                            <input type="text" x-model="specialName" placeholder="Custom Service…"
                                @click.stop @keydown.enter.prevent="$el.nextElementSibling.focus()"
                                class="w-full px-3 py-2 bg-white/80 border-2 border-purple-200 focus:border-purple-500 rounded-xl text-[13px] font-bold text-purple-900 mb-2 outline-none placeholder:text-purple-300 transition-colors">
                            <input type="number" x-model.number="specialPrice" placeholder="Price (Rs.)"
                                @click.stop @keydown.enter.prevent="addSpecialToCart()"
                                class="w-full px-3 py-2 bg-white/80 border-2 border-purple-200 focus:border-purple-500 rounded-xl text-[13px] font-bold text-purple-900 mb-3 outline-none placeholder:text-purple-300 transition-colors" min="0">
                            <button @click="addSpecialToCart()"
                                class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-[13px] font-black rounded-xl shadow-md shadow-purple-500/20 group-hover:shadow-purple-500/40 transition-all hover:-translate-y-0.5">
                                + Add Custom
                            </button>
                        </div>

                        <!-- Service Cards -->
                        <template x-for="service in filteredServices" :key="service.id">
                            <button @click="addToCart(service)"
                                class="col-span-2 sm:col-span-1 xl:col-span-1 flex flex-col p-2.5 rounded-[16px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_16px_rgba(0,0,0,0.06)] hover:-translate-y-1 transition-all duration-300 text-left group overflow-hidden relative"
                                :class="{'border-blue-200': service.color === 'blue', 'border-green-200': service.color === 'green', 'border-purple-200': service.color === 'purple', 'border-orange-200': service.color === 'orange', 'border-teal-200': service.color === 'teal', 'border-red-200': service.color === 'red'}">
                                
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center mb-2 transition-transform duration-300 group-hover:scale-110"
                                    :class="getColorStyle(service.color).bg + ' ' + getColorStyle(service.color).text">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>

                                <div class="flex flex-col gap-0.5 mt-auto">
                                    <span class="text-[11px] font-black text-slate-800 leading-tight tracking-tight line-clamp-2" x-text="service.name"></span>
                                    <div class="flex items-end justify-between mt-1 flex-wrap gap-1">
                                        <span class="text-[12px] font-black text-blue-600" x-text="'Rs. ' + parseFloat(service.base_price).toFixed(0)"></span>
                                        <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest" x-text="service.price_type === 'per_kg' ? '/KG' : '/ITM'"></span>
                                    </div>
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
                <div class="p-5 border-b-2 border-slate-100 bg-white z-20">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Customer Details</span>
                        <button @click="$dispatch('open-customer-modal')"
                            class="text-[11px] font-bold px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            New Customer
                        </button>
                    </div>

                    <!-- Search customers -->
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <input type="text" x-model="customerSearch" @input="filterCustomers"
                            @focus="showCustDropdown = true" @click.away="showCustDropdown = false"
                            :placeholder="selectedCustomer ? 'Change customer…' : 'Search by name or phone…'"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[13px] font-bold text-slate-800 transition-all outline-none placeholder:text-slate-400">

                        <!-- Dropdown -->
                        <div x-show="showCustDropdown && filteredCustomers.length > 0" x-transition
                            class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-xl max-h-[220px] overflow-y-auto custom-scrollbar">
                            <template x-for="cust in filteredCustomers" :key="cust.id">
                                <div @click="selectCustomer(cust)"
                                    class="px-4 py-3 cursor-pointer border-b border-slate-50 hover:bg-blue-50 transition-colors flex items-center justify-between group">
                                    <div>
                                        <p class="text-[13px] font-black text-slate-800 group-hover:text-blue-700 transition-colors" x-text="cust.name"></p>
                                        <p class="text-[11px] font-bold text-slate-500 mt-0.5" x-text="cust.phone"></p>
                                    </div>
                                    <svg class="w-4 h-4 text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Selected Customer badge -->
                    <div x-show="selectedCustomer" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl relative flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                            <span x-text="selectedCustomer?.name?.charAt(0).toUpperCase()"></span>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <div class="flex justify-between items-start">
                                <span class="text-[14px] font-black text-blue-900 truncate pr-6" x-text="selectedCustomer?.name"></span>
                                <button @click="clearCustomer()" class="absolute top-3 right-3 p-1.5 text-blue-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <span class="text-[11px] font-bold text-blue-600 block" x-text="selectedCustomer?.phone"></span>
                            
                            <template x-if="activePackage">
                                <button type="button" @click="openPackageModal()" class="relative z-50 mt-2 text-[11px] font-black px-3 py-1.5 bg-gradient-to-r from-amber-400 to-orange-500 text-white hover:from-amber-500 hover:to-orange-600 rounded-lg shadow-sm hover:shadow-md transition-all flex items-center gap-1.5 w-max">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Use Package: <span x-text="activePackage?.package?.name || 'Active'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                </div>

                <!-- Cart Items (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50 flex flex-col gap-3 custom-scrollbar">
                    <template x-if="cart.length === 0">
                        <div class="flex-1 flex flex-col items-center justify-center opacity-60">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <p class="text-[13px] font-black text-slate-500">Cart is empty</p>
                            <p class="text-[11px] font-semibold text-slate-400 mt-1">Add items from the left to begin</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="index">
                        <div class="group relative bg-white p-2.5 rounded-[12px] border border-slate-200 flex items-center justify-between gap-3 shadow-sm hover:border-blue-200 transition-all">
                            <!-- Left: Name & Base Price -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-[13px] font-black text-slate-800 truncate" x-text="item.name"></h4>
                                <span class="text-[10.5px] font-bold text-slate-400" x-text="'Rs. ' + parseFloat(item.base_price).toFixed(2) + (item.price_type === 'per_kg' ? ' /KG' : '')"></span>
                            </div>
                            
                            <!-- Middle: Qty Editor -->
                            <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg p-0.5">
                                <button @click="updateQty(index,-1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors font-black text-sm">−</button>
                                <input type="number" x-model="item.qty" class="w-12 text-center text-[12px] font-black text-slate-800 bg-transparent border-none outline-none p-0 focus:ring-0" min="0.01" step="any">
                                <button @click="updateQty(index,1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors font-black text-sm">+</button>
                            </div>

                            <!-- Right: Total Price & Remove -->
                            <div class="flex items-center gap-2">
                                <span class="text-[13px] font-black text-blue-700 whitespace-nowrap" x-text="'Rs. ' + (item.base_price * (parseFloat(item.qty) || 0)).toFixed(2)"></span>
                                <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Fixed Checkout Bar -->
                <div class="p-5 bg-white border-t border-slate-100 z-20 shadow-[0_-10px_40px_rgba(0,0,0,0.04)]">
                    <div class="flex items-center justify-between mb-4 px-1">
                        <span class="text-[12px] font-extrabold text-slate-500 uppercase tracking-widest">Total Amount</span>
                        <span class="text-3xl font-black text-slate-900 tracking-tight" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                    </div>
                    <button @click="if(!selectedCustomer) { alert('Please select a customer first.'); return; } if(!showCheckoutModal){ showCheckoutModal = true; if(!dueDate) { let d = new Date(); d.setDate(d.getDate() + 1); d.setHours(17, 0, 0, 0); dueDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16); } }" 
                        :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5'"
                        class="w-full py-4 rounded-2xl text-[15px] font-black flex justify-center items-center gap-3 transition-all duration-300">
                        Proceed to Checkout
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <!-- Delivery Request Button -->
                    <button @click="openDeliveryModal()"
                        class="w-full mt-2 py-3 rounded-2xl text-[13px] font-black flex justify-center items-center gap-2 transition-all duration-300 bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        New Delivery Request
                    </button>
                </div>
            </div>
        </div>

        <!-- ===================== Checkout Modal ===================== -->
        <div x-show="showCheckoutModal" x-cloak
            x-effect="if(showCheckoutModal) $nextTick(() => { const dp = document.getElementById('due-date-input'); if(dp) { try { dp.showPicker(); } catch(e) { dp.focus(); dp.click(); } } })"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="showCheckoutModal = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-md z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop>
                
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Checkout Order
                    </h3>
                    <button @click="showCheckoutModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto max-h-[75vh] custom-scrollbar">
                    
                    <!-- Pickup Date -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Estimated Pickup Date</label>
                        <div class="flex gap-2">
                            <input type="datetime-local" id="due-date-input" x-model="dueDate"
                                class="flex-1 bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all cursor-pointer">
                            <button @click="document.getElementById('due-date-input').blur()" type="button" class="px-4 bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold rounded-xl text-sm transition-colors shadow-sm">
                                Done
                            </button>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Payment Timing</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="paymentMode = 'pay_now'"
                                :class="paymentMode === 'pay_now' ? 'bg-blue-50 border-blue-200 ring-2 ring-blue-500/20' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                class="flex flex-col items-center justify-center p-4 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_now' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400'"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="font-black text-slate-800 text-[13px]">Pay Now</span>
                            </button>

                            <button @click="paymentMode = 'pay_on_pickup'"
                                :class="paymentMode === 'pay_on_pickup' ? 'bg-amber-50 border-amber-200 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 hover:bg-slate-50'"
                                class="flex flex-col items-center justify-center p-4 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_on_pickup' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-400'"
                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-colors mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <span class="font-black text-slate-800 text-[13px]">Pay on Pickup</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sub options for Pay Now -->
                    <div x-show="paymentMode === 'pay_now'" x-transition.opacity
                        class="grid grid-cols-3 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <button @click.stop="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-emerald-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            💵 CASH
                        </button>
                        <button @click.stop="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-violet-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            💳 CARD
                        </button>
                        <button @click.stop="paymentMethod = 'bank'"
                            :class="paymentMethod === 'bank' ? 'bg-blue-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                            class="px-4 py-3 rounded-lg text-[13px] font-black transition-all flex items-center justify-center gap-1">
                            🏦 BANK
                        </button>
                    </div>

                    <!-- Discount -->
                    <div class="mb-3 px-1">
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Discount (%)</label>
                        <input type="number" x-model.number="discountPercentage" min="0" max="100" step="1"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl">
                        <span class="text-[13px] font-black text-blue-900 uppercase tracking-widest">Final Total</span>
                        <div class="text-right">
                            <span x-show="discountPercentage > 0" class="text-sm font-bold text-slate-400 line-through block" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            <span class="text-2xl font-black text-blue-700" x-text="'Rs. ' + total.toFixed(2)"></span>
                        </div>
                    </div>

                </div>

                <div class="p-6 pt-2 bg-white border-t border-slate-100">
                    <button @click="placeOrder" :disabled="isPlacingOrder"
                        :class="isPlacingOrder ? 'opacity-60 cursor-not-allowed' : 'hover:shadow-blue-500/40 hover:-translate-y-0.5'"
                        class="w-full py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[15px] font-black rounded-xl shadow-lg shadow-blue-500/20 transition-all flex items-center justify-center gap-2">
                        <template x-if="!isPlacingOrder">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="isPlacingOrder">
                            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                        </template>
                        <span x-text="isPlacingOrder ? 'Processing...' : 'OK'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ===================== Add New Customer Modal ===================== -->
        <div x-data="{ 
            open: false, 
            isSaving: false,
            async createAndSelectCustomer() {
                let title = document.getElementById('nc-title').value;
                let name = document.getElementById('nc-name').value;
                let phone = document.getElementById('nc-phone').value;
                let email = document.getElementById('nc-email').value;
                let address = document.getElementById('nc-address').value;
                if(!name || !phone) return alert('Name and phone are required.');
                
                this.isSaving = true;
                try {
                    let res = await fetch('/customers', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ title, name, phone, email, address, source: 'pos' })
                    });
                    let data = await res.json();
                    if(data.success) {
                        window.dispatchEvent(new CustomEvent('customer-created', { detail: data.customer }));
                        this.open = false;
                        document.getElementById('new-customer-form').reset();
                    } else {
                        alert('Error: ' + (data.message || 'Validation failed.'));
                    }
                } catch(e) {
                    alert('Network error.');
                } finally {
                    this.isSaving = false;
                }
            }
        }"
            x-on:open-customer-modal.window="open = true; document.getElementById('nc-name').focus(); setTimeout(()=>document.getElementById('nc-name').focus(), 100);"
            x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 100;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" x-transition.opacity @click="open = false"></div>
            
            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-md z-10 animate-slide-up" @click.stop>
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        New Customer
                    </h3>
                    <button @click="open = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form id="new-customer-form" class="p-6 space-y-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Title</label>
                            <select id="nc-title" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all">
                                <option value="">None</option>
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Miss">Miss</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Prof.">Prof.</option>
                                <option value="Rev.">Rev.</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Full Name</label>
                            <input type="text" id="nc-name" required placeholder="e.g. Kamal Perera"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Phone Number</label>
                            <input type="text" id="nc-phone" required placeholder="e.g. 077 123 4567"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Email (Optional)</label>
                            <input type="email" id="nc-email" placeholder="e.g. name@example.com"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Address (Optional)</label>
                        <input type="text" id="nc-address" placeholder="Home address"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-3 text-[14px] font-black text-slate-800 outline-none transition-all placeholder:text-slate-400">
                    </div>
                </form>
                <div class="px-6 py-5 bg-white border-t border-slate-100 flex gap-3">
                    <button @click="open = false" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button @click="createAndSelectCustomer()" id="save-customer-btn" :disabled="isSaving"
                        class="flex-1 py-3.5 bg-green-600 hover:bg-green-700 text-white text-[14px] font-black rounded-xl shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5"
                        x-text="isSaving ? 'Saving...' : 'Save Customer'">
                    </button>
                </div>
            </div>
        </div>

        <!-- ===================== Order Success Modal ===================== -->
        <div x-data="{ show: false, orderId: '', orderNum: '', custName: '', total: '', payMode: '', dueDate: '' }"
            x-on:order-placed.window="show = true; orderId = $event.detail.orderId; orderNum = $event.detail.orderNumber; custName = $event.detail.customerName; total = $event.detail.total; payMode = $event.detail.paymentMode; dueDate = $event.detail.dueDate; printReceipt(orderNum);"
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
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Pickup Date:</span>
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
                    <button @click="printReceipt(orderNum)"
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
                        <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-2">Estimated Pickup Date</label>
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

    </div>

    <!-- ===================== Delivery Request Modal ===================== -->
    <div x-data="deliveryModal()" @open-delivery-modal.window="open = true; pickupDateOnly = ''; pickupTimeSlot = ''; pickupAddress = ''; deliveryAddress = ''; notes = '';"
        x-show="show" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 80;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity @click="show = false"></div>

        <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-lg z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop>
            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-orange-50 to-amber-50 flex justify-between items-center">
                <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    New Delivery Request
                </h3>
                <button @click="show = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto max-h-[75vh] custom-scrollbar">

                <!-- Customer -->
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center gap-3" x-show="selectedCust">
                    <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shrink-0">
                        <span x-text="selectedCust?.name?.charAt(0).toUpperCase()"></span>
                    </div>
                    <div>
                        <div class="text-[13px] font-black text-blue-900" x-text="selectedCust?.name"></div>
                        <div class="text-[11px] font-bold text-blue-600" x-text="selectedCust?.phone"></div>
                    </div>
                </div>
                <p x-show="!selectedCust" class="text-[12px] font-bold text-red-500 bg-red-50 border border-red-100 rounded-xl p-3">⚠️ Please select a customer in the POS first before creating a delivery request.</p>

                <!-- Cart Summary (if any) -->
                <div x-show="cartItems.length > 0" class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                    <p class="text-[11px] font-extrabold text-emerald-700 uppercase tracking-wider mb-2">Cart Items (will be saved with delivery)</p>
                    <template x-for="item in cartItems" :key="item.id">
                        <div class="flex justify-between text-[12px] font-bold text-slate-700 py-0.5">
                            <span x-text="item.name + ' × ' + item.qty"></span>
                            <span class="text-emerald-600" x-text="'Rs. ' + (item.base_price * item.qty).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
                <div x-show="cartItems.length === 0" class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-[11px] font-bold text-amber-700">📦 No items in cart — Rider will count items on-site and create the order from the Rider App.</p>
                </div>

                <!-- Pickup Address -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup Address <span class="text-red-500">*</span></label>
                    <textarea x-model="pickupAddress" rows="2" placeholder="Customer's pickup address…"
                        class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                </div>

                <!-- Delivery Address -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Delivery Address <span class="text-slate-400 font-semibold">(if different from pickup)</span></label>
                    <textarea x-model="deliveryAddress" rows="2" placeholder="Leave blank if same as pickup…"
                        class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                </div>

                <!-- Pickup Date & Time -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup Date &amp; Time <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="date" x-model="pickupDateOnly"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                        <select x-model="pickupTimeSlot" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                            <option value="">Select Time</option>
                            <template x-for="slot in window.posSystemData.timeSlots" :key="slot.id">
                                <option :value="slot.start_time + '-' + slot.end_time" x-text="formatTime(slot.start_time) + ' - ' + formatTime(slot.end_time)"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Notes (Optional)</label>
                    <input type="text" x-model="notes" placeholder="Any special instructions…"
                        class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all placeholder:text-slate-400">
                </div>

            </div>

            <div class="p-6 pt-2 bg-white border-t border-slate-100 flex gap-3">
                <button @click="show = false" class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">Cancel</button>
                <button @click="submitDelivery()" :disabled="isSaving || !selectedCust"
                    :class="isSaving || !selectedCust ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5'"
                    class="flex-2 flex-1 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-[14px] font-black rounded-xl shadow-lg shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                    <svg x-show="!isSaving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <svg x-show="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    <span x-text="isSaving ? 'Creating...' : 'Create Delivery Request'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        window.posSystemData = {
            timeSlots: @json($timeSlots)
        };
        
        window.posSystem = function() {
            return {
                allServices: @json($services),
                allCategories: @json($categories),
                allCustomers: @json($customers),
                allCustomerPackages: @json($customerPackages),
                activeCategory: null,
                searchQuery: '',
                customerSearch: '',
                filteredCustomers: [],
                showCustDropdown: false,
                selectedCustomer: null,
                cart: [],

                activePackage: null,
                showPackageModal: false,
                packageCart: [],

                showCheckoutModal: false,
                isPlacingOrder: false,
                paymentMode: 'pay_on_pickup',
                paymentMethod: 'cash',
                dueDate: '',
                discountPercentage: 0,
                specialName: '',
                specialPrice: 0,
                
                init() {
                    window.posAddCustomer = (customer) => {
                        this.allCustomers.push(customer);
                        this.selectCustomer(customer);
                    };
                },

                get filteredCategories() {
                    let filtered = this.allCategories;
                    if (this.searchQuery) {
                        filtered = filtered.filter(c => c.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    }
                    return filtered;
                },

                get filteredServices() {
                    let filtered = this.allServices;
                    
                    if (this.activeCategory === 'uncategorized') {
                        filtered = filtered.filter(s => s.category_id === null);
                    } else if (this.activeCategory !== null) {
                        filtered = filtered.filter(s => s.category_id === this.activeCategory);
                    }
                    
                    return filtered;
                },

                getColorStyle(colorName) {
                    const styles = {
                        'blue': { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-600' },
                        'green': { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-600' },
                        'purple': { bg: 'bg-purple-50', border: 'border-purple-200', text: 'text-purple-600' },
                        'orange': { bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-600' },
                        'teal': { bg: 'bg-teal-50', border: 'border-teal-200', text: 'text-teal-600' },
                        'red': { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-600' }
                    };
                    return styles[colorName] || styles['blue'];
                },

                filterCustomers() {
                    const q = this.customerSearch.toLowerCase();
                    this.filteredCustomers = q.length > 0
                        ? this.allCustomers.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q))
                        : this.allCustomers.slice(0, 8);
                    this.showCustDropdown = true;
                },

                selectCustomer(cust) {
                    this.selectedCustomer = cust;
                    this.customerSearch = '';
                    this.showCustDropdown = false;
                    
                    if (cust.default_discount) {
                        this.discountPercentage = parseFloat(cust.default_discount) || 0;
                    } else {
                        this.discountPercentage = 0;
                    }

                    let pkgs = this.allCustomerPackages.filter(p => p.customer_id === cust.id && p.status !== 'exhausted');
                    if(pkgs.length > 0) {
                        this.activePackage = pkgs[0];
                        this.packageCart = this.activePackage.services.map(s => {
                            return {
                                service_type_id: s.service_type.id,
                                name: s.service_type.name,
                                base_price: s.service_type.base_price,
                                price_type: s.service_type.price_type,
                                remaining: s.total_quantity - s.used_quantity,
                                qty: 0
                            };
                        });
                        
                        let d = new Date(); d.setDate(d.getDate() + 1); d.setHours(17, 0, 0, 0); 
                        this.dueDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                    } else {
                        this.activePackage = null;
                        this.packageCart = [];
                    }
                },

                openPackageModal() {
                    console.log('Opening package modal', this.activePackage);
                    if(this.activePackage) {
                        this.showPackageModal = true;
                    }
                },

                clearCustomer() {
                    this.selectedCustomer = null;
                    this.customerSearch = '';
                    this.activePackage = null;
                    this.packageCart = [];
                },

                addToCart(service) {
                    let existing = this.cart.find(i => i.id === service.id);
                    if (existing) { existing.qty += 1; } else { this.cart.push({ ...service, qty: 1 }); }
                },

                removeFromCart(index) { this.cart.splice(index, 1); },

                updateQty(index, change) {
                    let currentQty = parseFloat(this.cart[index].qty) || 0;
                    let newQty = currentQty + change;
                    if (newQty > 0) this.cart[index].qty = Math.round(newQty * 100) / 100;
                    else this.removeFromCart(index);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.base_price * (parseFloat(item.qty) || 0)), 0);
                },
                get total() {
                    const discount = this.subtotal * ((parseFloat(this.discountPercentage) || 0) / 100);
                    return this.subtotal - discount;
                }, addSpecialToCart() {
                    if (!this.specialName || !this.specialPrice || this.specialPrice <= 0) {
                        alert('Please enter a service name and price.');
                        return;
                    }
                    this.cart.push({
                        id: 'special_' + Date.now(),
                        service_type_id: 9,
                        name: this.specialName,
                        base_price: parseFloat(this.specialPrice),
                        price_type: 'per_item',
                        qty: 1,
                        is_special: true
                    });
                    this.specialName = '';
                    this.specialPrice = 0;
                },

                placePackageOrder() {
                    this.cart = this.packageCart.filter(item => item.qty > 0).map(item => ({
                        id: item.service_type_id,
                        service_type_id: item.service_type_id,
                        name: item.name,
                        base_price: item.base_price,
                        price_type: item.price_type,
                        qty: item.qty
                    }));
                    
                    if(this.cart.length === 0) {
                        alert("Please enter quantities to proceed.");
                        return;
                    }
                    
                    this.paymentMode = 'pay_on_pickup';
                    this.paymentMethod = 'cash';
                    this.showPackageModal = false;
                    this.placeOrder(this.activePackage.id);
                },

                async placeOrder(packageId = null) {
                    if (this.cart.length === 0) return;
                    if (!this.selectedCustomer) {
                        alert('Please select a customer for this order.');
                        return;
                    }
                    if (!this.dueDate) {
                        alert('Please select a Pickup Date for this order.');
                        return;
                    }
                    if (this.isPlacingOrder) return; // prevent double submit
                    this.isPlacingOrder = true;

                    const payload = {
                        customer_id: this.selectedCustomer ? this.selectedCustomer.id : null,
                        new_customer_name: this.selectedCustomer ? null : 'Walk-in',
                        new_customer_phone: this.selectedCustomer ? null : '0000000000',
                        cart: this.cart,
                        payment_mode: this.paymentMode,
                        payment_method: this.paymentMethod,
                        due_date: this.dueDate,
                        discount_percentage: this.discountPercentage || 0,
                        customer_package_id: typeof packageId === 'number' ? packageId : null
                    };

                    try {
                        const res = await fetch("{{ route('pos.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.showCheckoutModal = false;
                            window.dispatchEvent(new CustomEvent('order-placed', {
                                detail: {
                                    orderId: data.order_id,
                                    orderNumber: data.order_number,
                                    customerName: data.customer_name,
                                    total: parseFloat(data.total).toFixed(2),
                                    paymentMode: this.paymentMode,
                                    dueDate: data.due_date,
                                    cart: data.cart
                                }
                            }));
                            printOrderChit(data.order_number);
                            this.cart = [];
                            this.selectedCustomer = null;
                            this.paymentMode = 'pay_on_pickup';
                            this.paymentMethod = 'cash';
                            this.dueDate = '';
                            this.activePackage = null;
                        } else {
                            console.error('Backend Error Response:', data);
                            alert('Order failed: ' + (data.message || data.error || 'Unknown error'));
                        }
                    } catch (err) { 
                        console.error('Fetch exception:', err); 
                        alert('Network error. Please try again.'); 
                    }
                    finally { this.isPlacingOrder = false; }
                },

                openDeliveryModal() {
                    // Dispatch with current POS state (customer + cart)
                    window.dispatchEvent(new CustomEvent('open-delivery-modal', {
                        detail: {
                            customer: this.selectedCustomer,
                            cart: this.cart
                        }
                    }));
                }
            };
        };

        // Delivery Modal Component
        window.deliveryModal = function() {
            return {
                show: false,
                isSaving: false,
                selectedCust: null,
                cartItems: [],
                pickupAddress: '',
                deliveryAddress: '',
                pickupDateOnly: '',
                pickupTimeSlot: '',
                notes: '',

                formatTime(timeStr) {
                    if(!timeStr) return '';
                    let [h, m] = timeStr.split(':');
                    h = parseInt(h);
                    let ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return h + ':' + m + ' ' + ampm;
                },

                init() {
                    this.$watch('show', v => {
                        if (v) {
                            // Set default pickup date to today
                            let d = new Date();
                            this.pickupDateOnly = d.toISOString().split('T')[0];
                        }
                    });
                    window.addEventListener('open-delivery-modal', (e) => {
                        this.selectedCust = e.detail.customer || null;
                        this.cartItems    = e.detail.cart || [];
                        // Pre-fill address from customer if available
                        if (this.selectedCust && this.selectedCust.address) {
                            this.pickupAddress = this.selectedCust.address;
                        } else {
                            this.pickupAddress = '';
                        }
                        this.deliveryAddress = '';
                        this.notes = '';
                        this.show = true;
                    });
                },

                async submitDelivery() {
                    if (!this.selectedCust) { alert('Please select a customer in POS first.'); return; }
                    if (!this.pickupAddress.trim()) { alert('Please enter the Pickup Address.'); return; }
                    if (!this.pickupDateOnly) { alert('Please select Pickup Date.'); return; }
                    if (!this.pickupTimeSlot) { alert('Please select Pickup Time.'); return; }

                    let selectedTimeStart = this.pickupTimeSlot.split('-')[0]; // e.g. "08:00:00"
                    let combinedDateTime = this.pickupDateOnly + ' ' + selectedTimeStart;

                    this.isSaving = true;
                    try {
                        const payload = {
                            customer_id:      this.selectedCust.id,
                            pickup_address:   this.pickupAddress,
                            delivery_address: this.deliveryAddress || this.pickupAddress,
                            pickup_date:      combinedDateTime, // Used for due_date and pickup_scheduled_at
                            notes:            this.notes,
                            cart:             this.cartItems.length > 0 ? this.cartItems : [],
                            payment_mode:     'pay_on_pickup',
                        };

                        const res = await fetch('{{ route('pos.delivery.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.show = false;
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong.'));
                        }
                    } catch(e) {
                        alert('Network error. Please try again.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            };
        };

        // The save-customer-btn listener is mostly handled by Alpine now, but we keep this just in case or we can rely fully on alpine
        // Wait, the new customer modal Alpine scope already handles it. So we can remove the vanilla JS listener to prevent duplication.

        function printSilent(url) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = url;
        }

        function printOrderChit(orderId) {
            printSilent('/orders/' + orderId + '/chit');
        }

        function printReceipt(orderId) {
            printSilent('/orders/' + orderId + '/print');
        }
    </script>
</x-app-layout>