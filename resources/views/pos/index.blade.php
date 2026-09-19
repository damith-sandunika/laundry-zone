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
    <div class="pos-wrapper -mt-6 -mx-4 lg:-mx-8 h-[calc(100vh-64px)] bg-slate-100 p-3 lg:p-4" x-data="posSystem()" @customer-created.window="allCustomers.unshift($event.detail); filterCustomers(); selectCustomer($event.detail);">
        
        <div class="flex flex-col lg:flex-row gap-3 lg:gap-4 h-full overflow-hidden">

            <!-- ===================== Left: Services ===================== -->
            <div class="flex-1 lg:flex-[1.2] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">
                
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

                <!-- Main Category Filters (Wrapped) -->
                <div class="border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10 relative p-3">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="cat in filteredCategories" :key="cat.id">
                            <button @click="activeCategory = cat.id; activeSubcategory = null" 
                                :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                class="px-3 py-1.5 text-[12px] font-bold rounded-lg transition-all flex items-center gap-1 justify-center">
                                <span x-text="cat.name"></span>
                            </button>
                        </template>
                        <button @click="activeCategory = 'uncategorized'; activeSubcategory = null" 
                            :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                            class="px-3 py-1.5 text-[12px] font-bold rounded-lg transition-all">
                            Uncategorized
                        </button>
                    </div>
                </div>

                <!-- Services Grid grouped by Subcategory -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar z-10 flex flex-col gap-6">
                    
                    <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0">
                        <div class="flex flex-col gap-6">
                            
                            <!-- Loop through all subcategories -->
                            <template x-for="sub in activeCategoryData.children" :key="sub.id">
                                <div class="flex flex-col md:flex-row gap-3 sm:gap-4 bg-slate-50/50 rounded-[16px] p-3 sm:p-4 border border-slate-100">
                                    
                                    <!-- Subcategory Name (Left Side) -->
                                    <div class="flex flex-col items-start justify-center p-1 sm:p-2 shrink-0 w-full md:w-[100px]">
                                        <span class="text-[11px] sm:text-[12px] font-black text-black uppercase tracking-widest break-words whitespace-normal leading-tight w-full" x-text="sub.name"></span>
                                    </div>

                                    <!-- Services List (Right Side) -->
                                    <div class="flex flex-wrap gap-2 lg:gap-2.5 flex-1 items-start">
                                        <template x-for="service in allServices.filter(s => s.category_id === sub.id)" :key="service.id">
                                            <!-- SMALL Service Card -->
                                            <button @click="addToCart(service)"
                                                class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                                :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                                <div class="flex flex-col items-start mr-3">
                                                    <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                                    <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : 'Per Item')"></span>
                                                </div>
                                                <span class="text-[13px] font-black text-blue-700 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                            </button>
                                        </template>
                                        <div x-show="allServices.filter(s => s.category_id === sub.id).length === 0" class="w-full text-[11px] font-bold text-slate-400 py-2">
                                            No services found in this category.
                                        </div>
                                    </div>

                                </div>
                            </template>

                        </div>
                    </template>

                    <!-- Main Category Services / Uncategorized -->
                    <div class="flex flex-col gap-3">
                        <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0 && allServices.filter(s => s.category_id === activeCategory).length > 0">
                            <h3 class="text-[13px] font-black text-slate-700 tracking-wider uppercase border-b-2 border-slate-100 pb-1.5 mt-2">Other Services</h3>
                        </template>
                        <div class="flex flex-wrap gap-2 lg:gap-3">
                            <template x-for="service in (activeCategory === 'uncategorized' ? allServices.filter(s => s.category_id === null) : allServices.filter(s => s.category_id === activeCategory))" :key="service.id">
                                <button @click="addToCart(service)"
                                    class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                    :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                    <div class="flex flex-col items-start mr-3">
                                        <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                        <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : 'Per Item')"></span>
                                    </div>
                                    <span class="text-[12px] font-black text-blue-600 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                </button>
                            </template>
                            
                            <template x-if="!(activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0)">
                                <div x-show="(activeCategory === 'uncategorized' ? allServices.filter(s => s.category_id === null) : allServices.filter(s => s.category_id === activeCategory)).length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-center opacity-70">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">No services found</p>
                                </div>
                            </template>
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
                        <div class="group relative bg-white p-2.5 rounded-[12px] border border-slate-200 flex flex-col gap-1.5 shadow-sm hover:border-blue-200 transition-all">
                            
                            <!-- Top: Name, Category, Qty & Remove -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex flex-col flex-1 min-w-0 pt-0.5">
                                    <span class="text-[9px] font-bold text-slate-400 leading-normal uppercase tracking-widest mb-1 whitespace-normal break-words" x-text="getCategoryString(item.id)"></span>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-snug break-words whitespace-normal pr-1" x-text="item.name"></h4>
                                </div>
                                
                                <!-- Qty -->
                                <div class="flex items-center bg-slate-50 border border-slate-200 rounded p-0.5 shadow-sm shrink-0">
                                    <button @click="updateQty(index,-1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors font-black text-[14px] leading-none">-</button>
                                    <input type="number" x-model="item.qty" :style="'width: ' + Math.max(44, String(item.qty || '').length * 8 + 24) + 'px;'" class="text-center text-[12px] font-black text-slate-800 bg-transparent border-none outline-none p-0 focus:ring-0 hide-arrows transition-all" min="0.01" step="any">
                                    <button @click="updateQty(index,1)" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors font-black text-[14px] leading-none">+</button>
                                </div>

                                <button @click="removeFromCart(index)" class="text-slate-300 hover:text-red-500 transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <!-- Bottom: Base Price, Total -->
                            <div class="flex items-center justify-between gap-1 pt-1.5 border-t border-slate-50 mt-0.5">
                                <!-- Base Price -->
                                <div class="flex items-center gap-0.5 bg-slate-50 px-1.5 py-1 rounded border border-slate-100 shrink-0">
                                    <span class="text-[10px] font-bold text-slate-400">Rs.</span>
                                    <input type="number" x-model.number="item.base_price" class="w-14 sm:w-16 text-[12px] font-black text-slate-700 bg-transparent border-none focus:ring-0 outline-none p-0 hide-arrows text-center" min="0" step="0.01">
                                    <span class="text-[9px] font-extrabold text-slate-400" x-text="item.price_type === 'per_kg' ? '/KG' : (item.price_type === 'per_sqft' ? '/SQFT' : '')"></span>
                                </div>
                                
                                <!-- Total -->
                                <div class="text-right shrink-0 min-w-max">
                                    <span class="text-[14px] font-black text-blue-700 whitespace-nowrap" x-text="'Rs. ' + getItemTotal(item).toFixed(2)"></span>
                                </div>
                            </div>

                            <!-- Bottom: Extras & Turnaround Type -->
                            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2" x-data="{ showExtras: false, addMode: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1.5 cursor-pointer group">
                                            <input type="checkbox" x-model="showExtras" class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide group-hover:text-blue-600 transition-colors">Extras</span>
                                        </label>
                                        
                                        <button x-show="showExtras" @click="addMode = !addMode" class="text-[9px] bg-blue-50 text-blue-600 font-bold px-1.5 py-0.5 rounded border border-blue-100 hover:bg-blue-100 transition-colors">
                                            + Add Extra
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <select x-model="item.turnaround_type_id" class="text-[10px] font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-md p-1 outline-none max-w-[120px] truncate">
                                            <option value="">Standard</option>
                                            @foreach($turnaroundTypes as $tt)
                                                <option value="{{ $tt->id }}">{{ $tt->name }} ({{ $tt->markup_type == 'percentage' ? $tt->markup_value.'%' : 'Rs. '.$tt->markup_value }})</option>
                                            @endforeach
                                            <option value="custom">Express (%)</option>
                                        </select>
                                        <div x-show="item.turnaround_type_id === 'custom'" class="flex items-center gap-0.5">
                                            <input type="number" x-model.number="item.custom_turnaround_percentage" class="w-12 text-[10px] font-bold text-slate-700 bg-white border border-slate-200 rounded-md p-1 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hide-arrows text-center" placeholder="0">
                                            <span class="text-[10px] font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div x-show="showExtras" class="flex flex-col gap-1.5">
                                    <div x-show="addMode" class="bg-slate-50 p-1.5 rounded-lg border border-slate-200 shadow-inner">
                                        <select @change="
                                            if($event.target.value) {
                                                let exId = parseInt($event.target.value);
                                                let existing = item.selected_extras.find(e => e.id === exId);
                                                if(existing) {
                                                    existing.qty++;
                                                } else {
                                                    item.selected_extras.push({ id: exId, qty: 1 });
                                                }
                                                $event.target.value = '';
                                                addMode = false;
                                            }
                                        " class="text-[11px] font-bold text-slate-700 bg-white border border-slate-200 rounded-md p-1.5 outline-none w-full shadow-sm focus:border-blue-400">
                                            <option value="">-- Select Extra --</option>
                                            <template x-for="extra in allExtras" :key="extra.id">
                                                <option :value="extra.id" x-text="extra.name + ' (+Rs.' + extra.price + ')'"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div x-show="item.selected_extras && item.selected_extras.length > 0" class="flex flex-col gap-1.5">
                                        <template x-for="(selEx, idx) in item.selected_extras" :key="idx">
                                            <div class="flex items-center justify-between bg-white border border-slate-200 rounded-md p-1.5 shadow-sm">
                                                <span class="text-[10px] font-bold text-slate-700" x-text="allExtras.find(e => e.id === selEx.id)?.name"></span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-blue-600 font-bold" x-text="'Rs.' + (allExtras.find(e => e.id === selEx.id)?.price * selEx.qty).toFixed(2)"></span>
                                                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded px-1">
                                                        <button @click="if(selEx.qty > 1) selEx.qty--; else item.selected_extras.splice(idx, 1);" class="text-slate-500 hover:text-blue-600 font-black px-1.5 py-0.5 text-[10px]">-</button>
                                                        <span class="text-[10px] font-black w-3 text-center" x-text="selEx.qty"></span>
                                                        <button @click="selEx.qty++" class="text-slate-500 hover:text-blue-600 font-black px-1.5 py-0.5 text-[10px]">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Fixed Checkout Bar -->
                <div class="p-3 bg-white border-t border-slate-100 z-20 shadow-[0_-10px_40px_rgba(0,0,0,0.04)] flex items-center justify-between gap-3">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest leading-none mb-1">Total</span>
                        <span class="text-[18px] font-black text-slate-900 tracking-tight leading-none" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                    </div>
                    
                    <div class="flex gap-2 flex-1 justify-end">
                        <button @click="openDeliveryModal()"
                            class="px-4 py-2.5 rounded-xl text-[12px] font-black flex justify-center items-center gap-1.5 transition-all duration-300 bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Delivery
                        </button>

                        <button @click="if(!selectedCustomer) { alert('Please select a customer first.'); return; } if(!showCheckoutModal){ showCheckoutModal = true; if(!dueDate) { let d = new Date(); d.setDate(d.getDate() + 1); d.setHours(17, 0, 0, 0); dueDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16); } }" 
                            :disabled="cart.length === 0"
                            :class="cart.length === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed shadow-none' : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5'"
                            class="px-4 py-2.5 rounded-xl text-[12px] font-black flex justify-center items-center gap-1.5 transition-all duration-300">
                            Checkout
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </button>
                    </div>
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

                    <!-- Discount & Advance Amount -->
                    <div class="mb-3 px-1 flex gap-3">
                        <div class="flex-1">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Discount (%)</label>
                            <input type="number" x-model.number="discountPercentage" min="0" max="100" step="1"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                        </div>
                        <div class="flex-1" x-show="paymentMode === 'pay_now'">
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Advance Amount</label>
                            <input type="number" x-model="advanceAmount" min="0" step="0.01" :placeholder="'Full: Rs. ' + total.toFixed(2)"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl px-4 py-2 text-[14px] font-black text-slate-800 outline-none transition-all">
                        </div>
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
            x-on:order-placed.window="show = true; orderId = $event.detail.orderId; orderNum = $event.detail.orderNumber; custName = $event.detail.customerName; total = $event.detail.total; payMode = $event.detail.paymentMode; dueDate = $event.detail.dueDate;"
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
    <div x-data="deliveryModal()" @open-delivery-modal.window="open = true; pickupDateOnly = ''; pickupTimeSlot = ''; deliveryDateOnly = ''; deliveryTimeSlot = ''; pickupAddress = ''; deliveryAddress = ''; notes = '';">
        <template x-teleport="body">
            <div x-show="show" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 9999;">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity @click="show = false"></div>

                <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden animate-slide-up" @click.stop style="max-height:92vh;">
                    <!-- Header -->
                    <div class="px-7 py-5 border-b border-slate-100 bg-gradient-to-r from-orange-50 to-amber-50 flex justify-between items-center shrink-0">
                        <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            New Delivery Request
                        </h3>
                        <button @click="show = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-white/70 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="p-7 space-y-5 overflow-y-auto custom-scrollbar">

                        <!-- Customer & Cart Summary Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Customer -->
                            <div>
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2">Customer</p>
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
                            </div>

                            <!-- Cart Summary -->
                            <div>
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2">Cart Items</p>
                                <div x-show="cartItems.length > 0" class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl max-h-[80px] overflow-y-auto custom-scrollbar">
                                    <template x-for="item in cartItems" :key="item.id">
                                        <div class="flex justify-between text-[12px] font-bold text-slate-700 py-0.5">
                                            <span x-text="item.name + ' × ' + item.qty"></span>
                                            <span class="text-emerald-600" x-text="'Rs. ' + (item.base_price * item.qty).toFixed(2)"></span>
                                        </div>
                                    </template>
                                </div>
                                <div x-show="cartItems.length === 0" class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                                    <p class="text-[11px] font-bold text-amber-700">📦 No items in cart — Rider will count items on-site.</p>
                                </div>
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Address Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Pickup Address -->
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup Address <span class="text-red-500">*</span></label>
                                <textarea x-model="pickupAddress" rows="3" placeholder="Customer's pickup address…"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                            </div>

                            <!-- Delivery Address -->
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Delivery Address <span class="text-slate-400 font-semibold normal-case">(if different)</span></label>
                                <textarea x-model="deliveryAddress" rows="3" placeholder="Leave blank if same as pickup…"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                            </div>
                        </div>

                        <hr class="border-slate-100">

                        <!-- Date & Time Row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Pickup Date & Time -->
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup Date <span class="text-red-500">*</span></label>
                                <input type="date" x-model="pickupDateOnly"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 mt-3">Pickup Time Slot <span class="text-red-500">*</span></label>
                                <select x-model="pickupTimeSlot" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                    <option value="">— Select a time slot —</option>
                                    <template x-for="slot in window.posSystemData.timeSlots" :key="slot.id">
                                        <option :value="slot.start_time + '-' + slot.end_time" x-text="formatTime(slot.start_time) + ' – ' + formatTime(slot.end_time)"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Delivery Date & Time -->
                            <div>
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Delivery Date <span class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                                <input type="date" x-model="deliveryDateOnly"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 mt-3">Delivery Time Slot <span class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                                <select x-model="deliveryTimeSlot" class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                    <option value="">— Select a time slot —</option>
                                    <template x-for="slot in window.posSystemData.timeSlots" :key="slot.id">
                                        <option :value="slot.start_time + '-' + slot.end_time" x-text="formatTime(slot.start_time) + ' – ' + formatTime(slot.end_time)"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Notes <span class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                            <textarea x-model="notes" rows="2" placeholder="Any special instructions…"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                        </div>

                    </div>

                    <!-- Footer -->
                    <div class="px-7 py-5 bg-white border-t border-slate-100 flex gap-3 shrink-0">
                        <button @click="show = false" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">Cancel</button>
                        <button @click="submitDelivery()" :disabled="isSaving || !selectedCust"
                            :class="isSaving || !selectedCust ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5'"
                            class="flex-1 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-[14px] font-black rounded-xl shadow-lg shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                            <span x-text="isSaving ? 'Creating...' : 'Create Delivery Request'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>


    <script>
        window.posSystemData = {
            timeSlots: @json($timeSlots),
            extras: @json($extras),
            turnaroundTypes: @json($turnaroundTypes)
        };
        
        window.posSystem = function() {
            return {
                allServices: @json($services),
                allCategories: @json($categories),
                allCustomers: @json($customers),
                allCustomerPackages: @json($customerPackages),
                allExtras: window.posSystemData.extras,
                allTurnaroundTypes: window.posSystemData.turnaroundTypes,
                activeCategory: @json(count($categories) > 0 ? $categories[0]->id : null),
                activeSubcategory: null,
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
                advanceAmount: '',
                specialName: '',
                specialPrice: 0,
                
                init() {
                    window.posAddCustomer = (customer) => {
                        this.allCustomers.push(customer);
                        this.selectCustomer(customer);
                    };
                    // Auto-select first parent category and first sub-category on load
                    this.$nextTick(() => {
                        const parentCats = this.allCategories.filter(c => !c.parent_id);
                        if (parentCats.length > 0) {
                            this.activeCategory = parentCats[0].id;
                            if (parentCats[0].children && parentCats[0].children.length > 0) {
                                this.activeSubcategory = parentCats[0].children[0].id;
                            }
                        }
                    });
                },

                get filteredCategories() {
                    let filtered = this.allCategories;
                    // Only show parent categories in the main bar
                    filtered = filtered.filter(c => !c.parent_id);
                    return filtered;
                },

                get activeCategoryData() {
                    if (this.activeCategory && this.activeCategory !== 'uncategorized') {
                        return this.allCategories.find(c => c.id === this.activeCategory);
                    }
                    return null;
                },

                get filteredServices() {
                    let filtered = this.allServices;
                    
                    if (this.activeCategory === 'uncategorized') {
                        filtered = filtered.filter(s => s.category_id === null);
                    } else if (this.activeCategory !== null) {
                        if (this.activeSubcategory) {
                            // Show services in the selected sub-category AND services directly under the parent
                            filtered = filtered.filter(s => 
                                s.category_id === this.activeSubcategory || 
                                s.category_id === this.activeCategory
                            );
                        } else {
                            const cat = this.activeCategoryData;
                            if (cat) {
                                const validIds = [cat.id];
                                if (cat.children) {
                                    cat.children.forEach(child => validIds.push(child.id));
                                }
                                filtered = filtered.filter(s => validIds.includes(s.category_id));
                            } else {
                                filtered = filtered.filter(s => s.category_id === this.activeCategory);
                            }
                        }
                    }
                    
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        filtered = filtered.filter(s => s.name.toLowerCase().includes(q));
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
                    const q = this.customerSearch.trim();
                    if (q.length === 0) {
                        this.filteredCustomers = [];
                        this.showCustDropdown = false;
                        return;
                    }

                    fetch('/customers/search?q=' + encodeURIComponent(q) + '&with_packages=1', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.filteredCustomers = data;
                        this.showCustDropdown = true;
                    })
                    .catch(err => console.error(err));
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

                    let pkgs = cust.customer_packages || [];
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
                    // Intercept ServiceTypes that are actually Extras
                    if (service.name.toLowerCase().startsWith('extra:')) {
                        let extraName = service.name.substring(6).trim();
                        let extraModel = this.allExtras.find(e => e.name.toLowerCase() === extraName.toLowerCase());
                        
                        if (extraModel && this.cart.length > 0) {
                            let firstItem = this.cart[0];
                            if (!firstItem.selected_extras) firstItem.selected_extras = [];
                            let existingEx = firstItem.selected_extras.find(e => e.id === extraModel.id);
                            if (existingEx) {
                                existingEx.qty++;
                            } else {
                                firstItem.selected_extras.push({ id: extraModel.id, qty: 1 });
                            }
                            return;
                        }
                    }

                    let defaultPrice = parseFloat(service.base_price);
                    let existing = this.cart.find(i => i.id === service.id && !i.is_special && parseFloat(i.base_price) === defaultPrice);
                    if (existing) { 
                        existing.qty += 1; 
                    } else { 
                        let defaultTt = this.allTurnaroundTypes.find(tt => tt.is_default);
                        this.cart.push({ 
                            ...service, 
                            qty: 1,
                            base_price: defaultPrice,
                            turnaround_type_id: defaultTt ? defaultTt.id : null,
                            custom_turnaround_percentage: null,
                            turnaround_multiplier: defaultTt ? defaultTt.multiplier : 1,
                            is_special: false,
                            selected_extras: []
                        }); 
                    }
                },

                getCategoryString(serviceId) {
                    let service = this.allServices.find(s => s.id === serviceId) || this.cart.find(i => i.id === serviceId);
                    if (!service) return '';
                    let cId = service.category_id;
                    for (let cat of this.allCategories) {
                        if (cat.id == cId) return cat.name;
                        if (cat.children) {
                            for (let sub of cat.children) {
                                if (sub.id == cId) return cat.name + ' > ' + sub.name;
                            }
                        }
                    }
                    return '';
                },

                removeFromCart(index) { this.cart.splice(index, 1); },

                updateQty(index, change) {
                    let currentQty = parseFloat(this.cart[index].qty) || 0;
                    let newQty = currentQty + change;
                    if (newQty > 0) this.cart[index].qty = Math.round(newQty * 100) / 100;
                    else this.removeFromCart(index);
                },

                getItemTotal(item) {
                    let base = parseFloat(item.base_price || 0) * (parseFloat(item.qty) || 0);
                    
                    let turnaroundCharge = 0;
                    if (item.turnaround_type_id === 'custom') {
                        turnaroundCharge = base * (parseFloat(item.custom_turnaround_percentage || 0) / 100);
                    } else if (item.turnaround_type_id) {
                        let tt = this.allTurnaroundTypes.find(t => t.id == item.turnaround_type_id);
                        if (tt) {
                            if (tt.markup_type === 'percentage') {
                                turnaroundCharge = base * (parseFloat(tt.markup_value) / 100);
                            } else {
                                turnaroundCharge = parseFloat(tt.markup_value) * (parseFloat(item.qty) || 0);
                            }
                        }
                    }

                    let extrasTotal = 0;
                    if (item.selected_extras && item.selected_extras.length > 0) {
                        item.selected_extras.forEach(selEx => {
                            let extra = this.allExtras.find(e => e.id == selEx.id);
                            if (extra) {
                                extrasTotal += parseFloat(extra.price) * (parseFloat(selEx.qty) || 1);
                            }
                        });
                    }
                    
                    return base + turnaroundCharge + extrasTotal;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.getItemTotal(item), 0);
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
                        advance_amount: this.advanceAmount !== '' ? parseFloat(this.advanceAmount) : null,
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
                deliveryDateOnly: '',
                deliveryTimeSlot: '',
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

                    let selectedTimeStart = this.pickupTimeSlot.split('-')[0];
                    let combinedDateTime = this.pickupDateOnly + ' ' + selectedTimeStart;
                    
                    let deliveryCombined = null;
                    if (this.deliveryDateOnly && this.deliveryTimeSlot) {
                        let delTimeStart = this.deliveryTimeSlot.split('-')[0];
                        deliveryCombined = this.deliveryDateOnly + ' ' + delTimeStart;
                    }

                    this.isSaving = true;
                    try {
                        const payload = {
                            customer_id:      this.selectedCust.id,
                            pickup_address:   this.pickupAddress,
                            delivery_address: this.deliveryAddress || this.pickupAddress,
                            pickup_date:      combinedDateTime,
                            delivery_date:    deliveryCombined,
                            notes:            this.notes,
                            cart:             this.cartItems.length > 0 ? this.cartItems : [],
                            payment_mode:     'pay_on_pickup',
                        };

                        const res = await fetch('/pos/delivery', {
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
