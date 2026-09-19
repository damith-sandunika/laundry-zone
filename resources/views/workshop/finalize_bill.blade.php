<x-app-layout>

    <style>
        /* Mobile warning */
        .mobile-pos-warning {
            display: none;
        }

        @media (max-width: 768px) {
            .pos-wrapper {
                display: none !important;
            }

            .mobile-pos-warning {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 60vh;
                text-align: center;
                padding: 20px;
            }
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .custom-scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .custom-scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

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
        <svg class="w-16 h-16 text-slate-400 mb-4" fill="none" stroke="currentColor" stroke-width="1.5"
            viewBox="0 0 24 24">
            <rect x="2" y="4" width="20" height="16" rx="2" />
            <path stroke-linecap="round" d="M10 20v-2m4 2v-2" />
        </svg>
        <h2 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Desktop View Required</h2>
        <p class="text-sm font-semibold text-slate-500">The POS System is designed for wider screens. Please use a
            tablet or desktop computer to process orders.</p>
    </div>

    <!-- Wrapping to override default app layout padding for a full height POS -->
    <div class="pos-wrapper -mt-6 -mx-4 lg:-mx-8 h-[calc(100vh-64px)] bg-slate-100 p-3 lg:p-4" x-data="finalizeBill()">

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
                                        <template x-for="service in services.filter(s => s.category_id === sub.id)" :key="service.id">
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
                                        <div x-show="services.filter(s => s.category_id === sub.id).length === 0" class="w-full text-[11px] font-bold text-slate-400 py-2">
                                            No services found in this category.
                                        </div>
                                    </div>

                                </div>
                            </template>

                        </div>
                    </template>

                    <!-- Main Category Services / Uncategorized -->
                    <div class="flex flex-col gap-3">
                        <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0 && services.filter(s => s.category_id === activeCategory).length > 0">
                            <h3 class="text-[13px] font-black text-slate-700 tracking-wider uppercase border-b-2 border-slate-100 pb-1.5 mt-2">Other Services</h3>
                        </template>
                        <div class="flex flex-wrap gap-2 lg:gap-3">
                            <template x-for="service in (activeCategory === 'uncategorized' ? services.filter(s => s.category_id === null) : services.filter(s => s.category_id === activeCategory))" :key="service.id">
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
                                <div x-show="(activeCategory === 'uncategorized' ? services.filter(s => s.category_id === null) : services.filter(s => s.category_id === activeCategory)).length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-center opacity-70">
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
            <div
                class="flex-1 lg:flex-[0.8] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">

                <!-- Customer Section -->
                <div class="p-4 border-b-2 border-slate-100 bg-white z-20 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Customer
                            Details</span>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl relative flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                            {{ substr($dispatch->order->customer->name ?? $order->customer->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <span
                                class="text-[14px] font-black text-blue-900 block truncate">{{ $dispatch->order->customer->name ?? $order->customer->name ?? 'Unknown Customer' }}</span>
                            <span
                                class="text-[11px] font-bold text-blue-600 block">{{ $dispatch->order->customer->phone ?? $order->customer->phone ?? 'No Phone' }}</span>
                        </div>
                    </div>
                    @if((isset($dispatch) && $dispatch->order->notes) || (isset($order) && $order->notes))
                        <div
                            class="mt-1 p-2 bg-yellow-50 text-yellow-800 text-xs font-bold rounded-lg border border-yellow-200 flex gap-2">
                            <i class="fa-solid fa-note-sticky mt-0.5"></i> {{ $dispatch->order->notes ?? $order->notes }}
                        </div>
                    @endif
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
                                    <span class="text-[9px] font-bold text-slate-400 leading-normal uppercase tracking-widest mb-1 whitespace-normal break-words" x-text="getCategoryString(item.service_type_id || item.id)"></span>
                                    <h4 class="text-[13px] font-black text-slate-800 leading-snug break-words whitespace-normal pr-1" x-text="item.name"></h4>
                                </div>
                                
                                <!-- Qty -->
                                <div class="flex items-center bg-slate-50 border border-slate-200 rounded p-0.5 shadow-sm shrink-0">
                                    <button @click="if(item.qty > 0.5) item.qty = parseFloat(item.qty) - 0.5" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors font-black text-[14px] leading-none">-</button>
                                    <input type="number" x-model="item.qty" :style="'width: ' + Math.max(44, String(item.qty || '').length * 8 + 24) + 'px;'" class="text-center text-[12px] font-black text-slate-800 bg-transparent border-none outline-none p-0 focus:ring-0 hide-arrows transition-all" min="0.01" step="any">
                                    <button @click="item.qty = parseFloat(item.qty) + 0.5" class="w-6 h-6 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors font-black text-[14px] leading-none">+</button>
                                </div>

                                <button @click="cart.splice(index, 1)" class="text-slate-300 hover:text-red-500 transition-colors shrink-0">
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

                <!-- Dark Checkout Summary Bar -->
                <div
                    class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">

                    <div class="p-2.5">
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1.5">

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md">
                                <span
                                    class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[12px] font-black"
                                    x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>

                            <!-- Total -->
                            <div
                                class="flex justify-between items-center bg-blue-900/40 p-1.5 rounded-md border border-blue-500/30">
                                <span class="text-blue-300 text-[10px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[14px] font-black"
                                    x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>

                            <!-- Discount -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md group">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Discount
                                    (%)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="discountPercent"
                                        class="w-10 bg-slate-800 border-none rounded text-right text-white text-[12px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        min="0" max="100">
                                </div>
                            </div>

                            <!-- Advance -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md group">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Advance
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="requestedAdvance"
                                        class="w-14 bg-slate-800 border-none rounded text-right text-white text-[12px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Delivery Fee & Save Button Row -->
                            <div class="col-span-2 grid grid-cols-2 gap-x-2 items-center mt-1">
                                <!-- Delivery Fee -->
                                <div
                                    class="flex justify-between items-center bg-slate-800/50 p-1.5 rounded-md group">
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wide">Delivery Fee
                                        (Rs)</span>
                                    <div class="flex items-center">
                                        <input type="number" x-model.number="deliveryFee"
                                            class="w-14 bg-slate-800 border-none rounded text-right text-white text-[12px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                            placeholder="0">
                                    </div>
                                </div>

                                <!-- Save & Request Button -->
                                <button @click="saveOrder('request_payment')" :disabled="cart.length === 0 || isSaving"
                                    class="w-full py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-md font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5">
                                    <svg x-show="!isSaving" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <svg x-show="isSaving" class="animate-spin w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Save & Request
                                </button>
                            </div>
                        </div>
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
                allTurnaroundTypes: @json($turnaroundTypes ?? []),
                allExtras: @json($systemExtras ?? []),
                searchQuery: "",
                activeCategory: null,
                activeSubcategory: null,
                cart: @json($cartItems),

                deliveryFee: {{ (float) ($dispatch->order->delivery_fee ?? $order->delivery_fee ?? 0) }},
                discountPercent: 0,
                requestedAdvance: 0,
                isSaving: false,
                orderId: {{ $dispatch->order_id ?? $order->id ?? 0 }},
                orderNumber: '{{ $dispatch->order->order_number ?? $order->order_number ?? '' }}',

                init() {
                    if (this.categories.length > 0) {
                        this.activeCategory = this.categories[0].id;
                        // Auto-select first sub-category if it exists
                        const firstCat = this.categories[0];
                        if (firstCat.children && firstCat.children.length > 0) {
                            this.activeSubcategory = firstCat.children[0].id;
                        }
                    }
                },

                get filteredCategories() {
                    if (!this.searchQuery) return this.categories;
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

                    let defaultTt = this.allTurnaroundTypes.find(tt => tt.is_default);
                    let defaultTtId = defaultTt ? defaultTt.id : null;
                    
                    let existing = this.cart.find(i => 
                        i.service_type_id === service.id && 
                        parseFloat(i.base_price) === parseFloat(service.base_price) &&
                        i.turnaround_type_id == defaultTtId &&
                        (!i.selected_extras || i.selected_extras.length === 0)
                    );
                    
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            service_type_id: service.id,
                            name: service.name,
                            base_price: service.base_price,
                            qty: 1,
                            turnaround_type_id: defaultTtId,
                            custom_turnaround_percentage: null,
                            selected_extras: []
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
                getItemTotal(item) {
                    let base = parseFloat(item.base_price || 0) * parseFloat(item.qty || 1);
                    let turnaroundCharge = 0;
                    if (item.turnaround_type_id === 'custom') {
                        turnaroundCharge = base * (parseFloat(item.custom_turnaround_percentage || 0) / 100);
                    } else if (item.turnaround_type_id) {
                        let tt = this.allTurnaroundTypes.find(t => t.id == item.turnaround_type_id);
                        if (tt) {
                            if (tt.markup_type === 'percentage') {
                                turnaroundCharge = base * (parseFloat(tt.markup_value) / 100);
                            } else {
                                turnaroundCharge = parseFloat(tt.markup_value) * (parseFloat(item.qty) || 1);
                            }
                        }
                    }
                    let extrasTotal = 0;
                    if (item.selected_extras && item.selected_extras.length > 0) {
                        for (let ex of item.selected_extras) {
                            let eData = this.allExtras.find(e => e.id == ex.id);
                            if (eData) {
                                extrasTotal += parseFloat(eData.price) * parseFloat(ex.qty || 1);
                            }
                        }
                    }
                    return base + turnaroundCharge + extrasTotal;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.getItemTotal(item), 0);
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
                                delivery_fee: this.deliveryFee,
                                requested_advance: this.requestedAdvance,
                                next_action: action
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && document.referrer.indexOf('finalize-bill') === -1) {
                                window.location.href = document.referrer;
                            } else {
                                window.location.href = `/orders/view/${this.orderNumber}`;
                            }
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