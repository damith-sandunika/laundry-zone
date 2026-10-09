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

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto bg-slate-50/50 p-2 custom-scrollbar">
                    <template x-for="(item, index) in cart" :key="index">
                        <div
                            class="bg-white border-b lg:border border-slate-100 lg:rounded-xl p-3 mb-2 flex flex-col gap-2 shadow-sm relative group transition-all">

                            <!-- Top: Base Item Info -->
                            <div class="flex gap-3 items-center w-full">
                                <div
                                    class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <div class="text-[9px] font-bold text-slate-400 leading-none uppercase tracking-widest mb-0.5 truncate"
                                        x-text="getCategoryString(item.service_type_id)"></div>
                                    <div class="text-[13px] font-black text-slate-800 leading-tight mb-0.5 line-clamp-2"
                                        x-text="item.name"></div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg p-0.5">
                                        <button
                                            @click="if(parseFloat(item.qty) > 0.5) item.qty = parseFloat(item.qty) - 0.5"
                                            class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-white rounded-md transition-colors"><svg
                                                class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                            </svg></button>
                                        <input type="number" step="any" x-model.number="item.qty"
                                            class="w-10 text-center text-[13px] font-black text-slate-800 bg-transparent border-none focus:ring-0 p-0 hide-arrows">
                                        <button @click="item.qty = parseFloat(item.qty) + 0.5"
                                            class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-800 hover:bg-white rounded-md transition-colors"><svg
                                                class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4v16m8-8H4" />
                                            </svg></button>
                                    </div>
                                    <div class="flex items-center gap-1 w-[80px]">
                                        <span class="text-[12px] font-bold text-slate-400">Rs.</span>
                                        <input type="number" step="any" x-model.number="item.base_price"
                                            class="w-full text-right text-[14px] font-black text-blue-600 bg-transparent border-b border-transparent hover:border-slate-300 focus:border-blue-500 focus:ring-0 p-0 hide-arrows">
                                    </div>
                                    <button @click="cart.splice(index, 1)"
                                        class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Bottom: Extras & Turnaround Type -->
                            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2"
                                x-data="{ showExtras: (item.selected_extras && item.selected_extras.length > 0), addMode: false }">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1.5 cursor-pointer group">
                                            <input type="checkbox" x-model="showExtras"
                                                class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                                            <span
                                                class="text-[10px] font-bold text-slate-500 uppercase tracking-wide group-hover:text-blue-600 transition-colors">Extras</span>
                                            <template x-if="item.selected_extras && item.selected_extras.length > 0">
                                                <span
                                                    class="bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full text-[9px] font-black"
                                                    x-text="item.selected_extras.length"></span>
                                            </template>
                                        </label>
                                        <button x-show="showExtras" @click="addMode = !addMode"
                                            class="text-[9px] bg-blue-50 text-blue-600 font-bold px-1.5 py-0.5 rounded border border-blue-100 hover:bg-blue-100 transition-colors">
                                            + Add Extra
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[13px] font-black text-blue-700 whitespace-nowrap"
                                            x-text="'Rs. ' + getItemTotal(item).toFixed(2)"></span>
                                        <select x-model="item.turnaround_type_id"
                                            class="text-[10px] font-bold text-slate-700 bg-slate-50 border border-slate-200 rounded-md p-1 outline-none max-w-[120px] truncate shrink-0">
                                            <option value="">Standard</option>
                                            @foreach($turnaroundTypes as $tt)
                                                <option value="{{ $tt->id }}">{{ $tt->name }}
                                                    ({{ $tt->markup_type == 'percentage' ? $tt->markup_value . '%' : 'Rs. ' . $tt->markup_value }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div x-show="showExtras" class="flex flex-col gap-1.5">
                                    <div x-show="addMode"
                                        class="bg-slate-50 p-1.5 rounded-lg border border-slate-200 shadow-inner">
                                        <select @change="
                                            if($event.target.value) {
                                                let exId = parseInt($event.target.value);
                                                if(!item.selected_extras) item.selected_extras = [];
                                                let existing = item.selected_extras.find(e => e.id === exId);
                                                if(existing) {
                                                    existing.qty++;
                                                } else {
                                                    item.selected_extras.push({ id: exId, qty: 1 });
                                                }
                                                $event.target.value = '';
                                                addMode = false;
                                            }
                                        "
                                            class="text-[11px] font-bold text-slate-700 bg-white border border-slate-200 rounded-md p-1.5 outline-none w-full shadow-sm focus:border-blue-400">
                                            <option value="">-- Select Extra --</option>
                                            <template x-for="extra in allExtras" :key="extra.id">
                                                <option :value="extra.id"
                                                    x-text="extra.name + ' (+Rs.' + extra.price + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div x-show="item.selected_extras && item.selected_extras.length > 0"
                                        class="flex flex-col gap-1.5">
                                        <template x-for="(selEx, idx) in item.selected_extras" :key="idx">
                                            <div
                                                class="flex items-center justify-between bg-white border border-slate-200 rounded-md p-1.5 shadow-sm">
                                                <span class="text-[10px] font-bold text-slate-700"
                                                    x-text="allExtras.find(e => e.id === selEx.id)?.name"></span>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-blue-600 font-bold"
                                                        x-text="'Rs.' + (allExtras.find(e => e.id === selEx.id)?.price * selEx.qty).toFixed(2)"></span>
                                                    <div
                                                        class="flex items-center bg-slate-50 border border-slate-200 rounded px-1">
                                                        <button
                                                            @click="if(selEx.qty > 1) selEx.qty = parseFloat(selEx.qty) - 1; else item.selected_extras.splice(idx, 1);"
                                                            class="text-slate-500 hover:text-blue-600 font-black px-1.5 py-0.5 text-[10px]">-</button>
                                                        <span class="text-[10px] font-black w-3 text-center"
                                                            x-text="selEx.qty"></span>
                                                        <button @click="selEx.qty = parseFloat(selEx.qty) + 1"
                                                            class="text-slate-500 hover:text-blue-600 font-black px-1.5 py-0.5 text-[10px]">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Cart -->
                    <div x-show="cart.length === 0"
                        class="h-full flex flex-col items-center justify-center text-center opacity-60">
                        <div class="w-20 h-20 bg-white rounded-full shadow-sm flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <p class="text-[15px] font-black text-slate-400">Cart is empty</p>
                        <p class="text-[12px] font-bold text-slate-400 mt-1">Select services to add.</p>
                    </div>
                </div>

                <!-- Dark Checkout Summary Bar -->
                <div
                    class="bg-slate-900 text-white rounded-t-[24px] shadow-[0_-10px_25px_rgba(0,0,0,0.15)] relative z-30 flex flex-col">

                    <div class="p-3">
                        <div class="grid grid-cols-2 gap-x-3 gap-y-2">

                            <!-- Subtotal -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg">
                                <span
                                    class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Subtotal</span>
                                <span class="text-white text-[13px] font-black"
                                    x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                            </div>

                            <!-- Total -->
                            <div
                                class="flex justify-between items-center bg-blue-900/40 p-2 rounded-lg border border-blue-500/30">
                                <span class="text-blue-300 text-[11px] font-bold uppercase tracking-wide">Total</span>
                                <span class="text-blue-400 text-[15px] font-black"
                                    x-text="'Rs. ' + total.toFixed(2)"></span>
                            </div>

                            <!-- Discount -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Discount
                                    (%)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="discountPercent"
                                        class="w-12 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        min="0" max="100">
                                </div>
                            </div>

                            <!-- Advance -->
                            <div class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Advance
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="requestedAdvance"
                                        class="w-16 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>

                            <!-- Delivery Fee -->
                            @if(config('app.delivery_enabled'))
                            <div
                                class="flex justify-between items-center bg-slate-800/50 p-2 rounded-lg group col-span-2">
                                <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wide">Delivery Fee
                                    (Rs)</span>
                                <div class="flex items-center">
                                    <input type="number" x-model.number="deliveryFee"
                                        class="w-20 bg-slate-800 border-none rounded text-right text-white text-[13px] font-black p-0.5 hide-arrows focus:ring-1 focus:ring-blue-500"
                                        placeholder="0">
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-950/50 rounded-b-[24px]">
                        <button @click="saveOrder('request_payment')" :disabled="cart.length === 0 || isSaving"
                            class="py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-black text-[12px] tracking-wide transition-all disabled:opacity-50 flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Save & Request
                        </button>

                        <button @click="saveOrder('start_processing')" :disabled="cart.length === 0 || isSaving"
                            class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-black text-[12px] tracking-wide transition-all shadow-[0_4px_15px_rgba(37,99,235,0.4)] hover:shadow-[0_6px_20px_rgba(37,99,235,0.6)] disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-1.5">
                            <svg x-show="!isSaving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="isSaving" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Save & Process
                        </button>
                    </div>
                </div>
