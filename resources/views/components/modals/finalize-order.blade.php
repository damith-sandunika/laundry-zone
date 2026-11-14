<template x-teleport="body">
    <div x-data="finalizeOrderModal()" x-cloak x-show="showFinalizeModal"
        @open-finalize-modal.window="openModal($event.detail)"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:1000px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;"
            @click.away="showFinalizeModal = false">

            <div
                style="padding:16px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <h3 class="text-lg font-black text-slate-800">Finalize Bill - <span
                        x-text="orderData ? 'Order #' + orderData.order_number : ''"></span></h3>
                <button @click="showFinalizeModal = false"
                    style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;">&times;</button>
            </div>

            <div class="flex flex-col lg:flex-row h-full overflow-hidden"
                style="min-height: 500px; max-height: calc(90vh - 70px);">
                <!-- left side: services -->
                <div class="flex-1 bg-white flex flex-col overflow-hidden border-r border-slate-200">
                    <div class="p-4 border-b border-slate-100 bg-slate-50">
                        <input type="text" x-model="search" placeholder="Search services..."
                            class="w-full px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                    <!-- Category Filters -->
                    <div class="px-4 py-2 border-b border-slate-100 bg-white z-10 overflow-x-auto scrollbar-none">
                        <div class="flex gap-2 w-max">
                            <button @click="activeCategory = null"
                                :class="activeCategory === null ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 text-[12px] font-black rounded-xl transition-all whitespace-nowrap">
                                All
                            </button>
                            <template x-for="cat in categories" :key="cat.id">
                                <button @click="activeCategory = cat.id"
                                    :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-4 py-2 text-[12px] font-black rounded-xl transition-all whitespace-nowrap"
                                    x-text="cat.name">
                                </button>
                            </template>
                            <button @click="activeCategory = 'uncategorized'"
                                :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-4 py-2 text-[12px] font-black rounded-xl transition-all whitespace-nowrap">
                                Uncategorized
                            </button>
                        </div>
                    </div>
                    <div
                        class="flex-1 overflow-y-auto p-4 grid grid-cols-2 md:grid-cols-3 gap-3 content-start custom-scrollbar">
                        <template x-for="service in filteredServices" :key="service.id">
                            <button @click="addToCart(service)"
                                class="p-3 border border-slate-200 rounded-xl hover:border-blue-500 hover:shadow-md transition-all text-left group bg-white">
                                <div class="text-xs font-black text-slate-800 mb-1" x-text="service.name"></div>
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-black text-blue-600"
                                        x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                    <span class="text-[10px] font-bold text-slate-400"
                                        x-text="service.price_type === 'per_kg' ? '/KG' : (service.price_type === 'per_sqft' ? '/SQFT' : (service.price_type === 'per_pair' ? '/PAIR' : '/ITEM'))"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- right side: cart -->
                <div class="w-full lg:w-[400px] bg-slate-50 flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-200 flex flex-col gap-3">
                        <div>
                            <h3 class="font-black text-slate-800 text-base"
                                x-text="orderData?.customer?.name || 'Unknown Customer'"></h3>
                            <p class="text-[11px] font-bold text-slate-500"
                                x-text="orderData?.customer?.phone || 'No Phone'"></p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 custom-scrollbar">
                        <template x-for="(item, index) in cart" :key="index">
                            <div
                                class="p-3 border border-slate-200 rounded-xl flex items-center justify-between gap-2 bg-white">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-black text-slate-800 truncate" x-text="item.name"></div>
                                    <div class="text-[10px] font-bold text-slate-500"
                                        x-text="'Rs.' + parseFloat(item.base_price).toFixed(2)"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="any" x-model.number="item.qty"
                                        class="w-24 text-center text-sm font-black bg-white border border-slate-300 rounded-lg outline-none py-1.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        min="0">
                                    <button @click="cart.splice(index, 1)"
                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="cart.length === 0" class="text-center py-8 text-sm font-bold text-slate-400">
                            No items added yet.
                        </div>
                    </div>

                    <div class="p-4 bg-white border-t border-slate-200 shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
                        <div class="flex justify-between items-center mb-2 border-b border-slate-100 pb-2">
                            <span class="text-xs font-bold text-slate-500">Subtotal</span>
                            <span class="text-sm font-black text-slate-800" x-text="'Rs.' + subtotal.toFixed(2)"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Discount (%)</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">%</span>
                                    <input type="number" x-model.number="discountPercentage"
                                        class="w-full pl-9 pr-2 py-2 text-sm font-black bg-white border border-slate-300 rounded-lg outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        placeholder="0" min="0" max="100">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Fee</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rs.</span>
                                    <input type="number" x-model.number="deliveryFee"
                                        class="w-full pl-9 pr-2 py-2 text-sm font-black bg-white border border-slate-300 rounded-lg outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        placeholder="0.00" min="0">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Advance Amount</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">Rs.</span>
                                    <input type="number" x-model.number="requestedAdvance"
                                        class="w-full pl-9 pr-2 py-2 text-sm font-black bg-white border border-slate-300 rounded-lg outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                        placeholder="0.00" min="0">
                                </div>
                            </div>

                            <div x-show="context === 'correction'">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
                                <select x-model="paymentMethod"
                                    class="w-full px-3 py-2 text-sm font-black bg-white border border-slate-300 rounded-lg outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between items-center mb-4 pt-2 border-t border-slate-100">
                            <span class="text-xs font-bold text-slate-500"
                                x-text="'Total' + (minimumDeliveryCost > 0 ? ' (Min Rs.' + minimumDeliveryCost + ')' : '')"></span>
                            <span class="text-lg font-black text-blue-700" x-text="'Rs.' + total.toFixed(2)"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-2" x-show="context === 'workshop'">
                            <button @click="saveOrder('request_payment')" :disabled="isSaving"
                                class="w-full py-2.5 bg-white border-2 border-blue-600 text-blue-600 hover:bg-blue-50 font-black text-xs rounded-xl transition-colors">
                                <span x-text="isSaving ? '...' : 'Save & Request Payment'"></span>
                            </button>
                            <button @click="saveOrder('start_processing')" :disabled="isSaving"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl transition-colors shadow-md shadow-blue-500/30">
                                <span x-text="isSaving ? '...' : 'Save & Start Processing'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-2" x-show="context === 'branch'">
                            <button @click="saveOrder('save')" :disabled="isSaving"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl transition-colors shadow-md shadow-blue-500/30">
                                <span x-text="isSaving ? '...' : 'Save'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-2" x-show="context === 'correction'">
                            <button @click="saveOrder('correction')" :disabled="isSaving"
                                class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-black text-xs rounded-xl transition-colors shadow-md shadow-amber-500/30">
                                <span x-text="isSaving ? 'Submitting...' : 'Submit Correction Request'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

@include('components.modals.finalize-order-scripts')
