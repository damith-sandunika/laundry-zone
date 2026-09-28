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

                    <div class="p-3">
                        <div class="grid grid-cols-2 gap-x-3 gap-y-2 mb-2">

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg">
                                <span
                                    class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[13px] font-black"
                                    x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>

                            <!-- Total -->
                            <div class="flex justify-between items-center bg-blue-900/40 p-2 rounded-lg border border-blue-500/30">
                                <span class="text-blue-300 text-[11px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[15px] font-black" x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-x-2 gap-y-2 mb-1 mt-1">
                            <!-- Discount -->
                            <div class="flex flex-col justify-center bg-slate-800/50 px-2 py-1.5 rounded-lg group">
                                <span class="text-slate-400 text-[9px] font-bold uppercase tracking-wide">Discount (%)</span>
                                <input type="number" x-model.number="discountPercent"
                                    class="w-full bg-slate-800 border border-slate-700/50 mt-1 rounded text-right text-emerald-400 text-[11px] font-black py-0.5 px-1 hide-arrows focus:ring-1 focus:ring-blue-500"
                                    min="0" max="100">
                            </div>

                            <!-- Delivery Fee -->
                            <div class="flex flex-col justify-center bg-slate-800/50 px-2 py-1.5 rounded-lg group">
                                <span class="text-slate-400 text-[9px] font-bold uppercase tracking-wide">Delivery (Rs)</span>
                                <input type="number" x-model.number="deliveryFee"
                                    class="w-full bg-slate-800 border border-slate-700/50 mt-1 rounded text-right text-emerald-400 text-[11px] font-black py-0.5 px-1 hide-arrows focus:ring-1 focus:ring-blue-500"
                                    placeholder="0">
                            </div>

                        </div>
                    </div>

                    <!-- Edit Correction Fields -->
                    <div class="p-2 bg-slate-800/80 rounded-b-[24px] border-t border-slate-700/50 mt-1">
                        <div class="flex items-end gap-2">
                            <div class="flex flex-col gap-0.5 flex-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Reason <span class="text-red-400">*</span></label>
                                <input type="text" x-model="editReason" placeholder="Reason..." class="bg-slate-900 border border-slate-700 rounded-lg text-white text-[12px] font-medium py-1 px-2 focus:ring-1 focus:ring-blue-500 w-full outline-none h-[38px]" required>
                            </div>
                            <button @click="saveOrder()" :disabled="cart.length === 0 || isSaving || !editReason"
                                class="h-[38px] px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5 shrink-0">
                                <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Request
                            </button>
                        </div>
                    </div>
                </div>

            </div>
