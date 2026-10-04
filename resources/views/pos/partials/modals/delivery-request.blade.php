<!-- ===================== Delivery Request Modal ===================== -->
<div x-data="deliveryModal()">
    <template x-teleport="body">
        <div x-show="show" x-cloak class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 9999;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-transition.opacity @click="show = false">
            </div>

            <div class="relative bg-white rounded-[24px] shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden animate-slide-up"
                @click.stop style="max-height:92vh;">
                <!-- Header -->
                <div
                    class="px-7 py-5 border-b border-slate-100 bg-gradient-to-r from-orange-50 to-amber-50 flex justify-between items-center shrink-0">
                    <h3 class="font-black text-lg text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        New Delivery Request
                    </h3>
                    <button @click="show = false"
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-white/70 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-7 space-y-5 overflow-y-auto custom-scrollbar">

                    <!-- Customer & Cart Summary Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div>
                            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2">Customer
                            </p>
                            <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center gap-3"
                                x-show="selectedCust">
                                <div
                                    class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shrink-0">
                                    <span x-text="selectedCust?.name?.charAt(0).toUpperCase()"></span>
                                </div>
                                <div>
                                    <div class="text-[13px] font-black text-blue-900" x-text="selectedCust?.name"></div>
                                    <div class="text-[11px] font-bold text-blue-600" x-text="selectedCust?.phone"></div>
                                </div>
                            </div>
                            <p x-show="!selectedCust"
                                class="text-[12px] font-bold text-red-500 bg-red-50 border border-red-100 rounded-xl p-3">
                                ⚠️ Please select a customer in the POS first before creating a delivery request.</p>
                        </div>

                        <!-- Cart Summary -->
                        <div>
                            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-2">Cart
                                Items</p>
                            <div x-show="cartItems.length > 0"
                                class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl max-h-[80px] overflow-y-auto custom-scrollbar">
                                <template x-for="item in cartItems" :key="item.id">
                                    <div class="flex justify-between text-[12px] font-bold text-slate-700 py-0.5">
                                        <span x-text="item.name + ' × ' + item.qty"></span>
                                        <span class="text-emerald-600"
                                            x-text="'Rs. ' + (item.base_price * item.qty).toFixed(2)"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="cartItems.length === 0"
                                class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
                                <p class="text-[11px] font-bold text-amber-700">📦 No items in cart — Rider will count
                                    items on-site.</p>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Address Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Pickup Address -->
                        <div>
                            <label
                                class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup
                                Address <span class="text-red-500">*</span></label>
                            <textarea x-model="pickupAddress" rows="3" placeholder="Customer's pickup address…"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                        </div>

                        <!-- Delivery Address -->
                        <div>
                            <label class="flex items-center space-x-2 mb-2 cursor-pointer">
                                <input type="checkbox" x-model="customerWillPickup"
                                    class="form-checkbox text-orange-500 rounded border-slate-300 w-4 h-4">
                                <span class="text-[12px] font-extrabold text-slate-700">Customer will pickup from
                                    branch</span>
                            </label>

                            <div x-show="!customerWillPickup" x-transition>
                                <label
                                    class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Delivery
                                    Address <span class="text-slate-400 font-semibold normal-case">(if
                                        different)</span></label>
                                <textarea x-model="deliveryAddress" rows="3"
                                    placeholder="Leave blank if same as pickup…"
                                    class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    <!-- Date & Time Row -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Pickup Date & Time -->
                        <div>
                            <label
                                class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Pickup
                                Scheduled Date <span class="text-red-500">*</span></label>
                            <input type="date" x-model="pickupDateOnly"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                            <label
                                class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 mt-3">Pickup
                                Time Slot <span
                                    class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                            <select x-model="pickupTimeSlot"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                <option value="">— Any time —</option>
                                <template x-for="slot in window.posSystemData.timeSlots" :key="slot.id">
                                    <option :value="slot.start_time + '-' + slot.end_time"
                                        x-text="formatTime(slot.start_time) + ' – ' + formatTime(slot.end_time)">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- Delivery Date & Time -->
                        <div x-show="!customerWillPickup">
                            <label
                                class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Delivery
                                Date <span class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                            <input type="date" x-model="deliveryDateOnly"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                            <label
                                class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5 mt-3">Delivery
                                Time Slot <span
                                    class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                            <select x-model="deliveryTimeSlot"
                                class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-black text-slate-800 outline-none transition-all cursor-pointer">
                                <option value="">— Select a time slot —</option>
                                <template x-for="slot in window.posSystemData.timeSlots" :key="slot.id">
                                    <option :value="slot.start_time + '-' + slot.end_time"
                                        x-text="formatTime(slot.start_time) + ' – ' + formatTime(slot.end_time)">
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label
                            class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Notes
                            <span class="text-slate-400 font-semibold normal-case">(optional)</span></label>
                        <textarea x-model="notes" rows="2" placeholder="Any special instructions…"
                            class="w-full bg-slate-50 border-2 border-slate-200 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/10 rounded-xl px-4 py-3 text-[13px] font-bold text-slate-800 outline-none transition-all resize-none placeholder:text-slate-400"></textarea>
                    </div>

                </div>

                <!-- Footer -->
                <div class="px-7 py-5 bg-white border-t border-slate-100 flex gap-3 shrink-0">
                    <button @click="show = false"
                        class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-black rounded-xl transition-colors">Cancel</button>
                    <button @click="submitDelivery()" :disabled="isSaving || !selectedCust"
                        :class="isSaving || !selectedCust ? 'opacity-50 cursor-not-allowed' : 'hover:-translate-y-0.5'"
                        class="flex-1 py-3.5 bg-gradient-to-r from-orange-500 to-amber-500 text-white text-[14px] font-black rounded-xl shadow-lg shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                        <svg x-show="!isSaving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="isSaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span x-text="isSaving ? 'Creating...' : 'Create Delivery Request'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>