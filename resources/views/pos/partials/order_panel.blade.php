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

                                        <button @click="openNoteModal(index)" 
                                            :class="item.note ? 'text-green-600 bg-green-50 border-green-200 hover:bg-green-100' : 'text-slate-500 bg-slate-50 border-slate-200 hover:bg-slate-100'"
                                            class="flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded border transition-colors ml-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            <span x-text="item.note ? 'Note Added' : 'Add Note'"></span>
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
