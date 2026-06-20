<x-app-layout>

    <style>
        .pos-wrapper { margin: -36px -40px; height: calc(100vh - 64px); background: #e2e8f0; padding: 12px; }
        @media (max-width: 768px) {
            .pos-wrapper { margin: -24px -20px; padding: 8px; }
        }
    </style>
    <div x-data="posSystem()" class="pos-wrapper">
        <div class="flex flex-col lg:flex-row gap-3 h-full overflow-hidden">

            <!-- ===================== Left: Services ===================== -->
            <div
                class="flex-1 lg:flex-[1.1] bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col overflow-hidden">
                <!-- Search bar -->
                <div style="padding:10px 14px;border-bottom:1px solid #f1f5f9;background:#f8fafc;">
                    <div style="position:relative;">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);" width="13" height="13" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Search laundry services…"
                            style="width:100%;padding:8px 14px 8px 34px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:white;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>

                <!-- Services Grid -->
                <div style="flex:1;overflow-y:auto;padding:8px 10px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:8px;">
                        <!-- Special Service Card -->
                        <div style="display:flex;flex-direction:column;padding:16px;border-radius:20px;background:linear-gradient(135deg,#fdf4ff,#f3e8ff);border:2px dashed #c084fc;box-shadow:0 2px 8px rgba(168,85,247,0.08);">

                            <input type="text" x-model="specialName" placeholder="Service name…"
                                @click.stop @keydown.enter.prevent="$el.nextElementSibling.focus()"
                                style="width:100%;padding:6px 8px;border:1.5px solid #d8b4fe;border-radius:8px;font-size:12px;font-weight:700;color:#3b0764;background:white;outline:none;box-sizing:border-box;margin-bottom:5px;">
                            <input type="number" x-model.number="specialPrice" placeholder="Price (Rs.)"
                                @click.stop @keydown.enter.prevent="addSpecialToCart()"
                                style="width:100%;padding:6px 8px;border:1.5px solid #d8b4fe;border-radius:8px;font-size:12px;font-weight:700;color:#3b0764;background:white;outline:none;box-sizing:border-box;margin-bottom:8px;" min="0">
                            <button @click="addSpecialToCart()"
                                style="width:100%;padding:7px;background:#7e22ce;color:white;border:none;border-radius:10px;font-size:12px;font-weight:900;cursor:pointer;transition:all 0.15s;"
                                onmouseover="this.style.background='#6b21a8'" onmouseout="this.style.background='#7e22ce'">
                                + Add to Cart
                            </button>
                        </div>

                        <template x-for="service in filteredServices" :key="service.id">
                            <button @click="addToCart(service)"
                                style="display:flex;align-items:center;padding:16px;border-radius:20px;cursor:pointer;transition:all 0.2s cubic-bezier(0.4, 0, 0.2, 1);text-align:left;overflow:hidden;background:white;border:1.5px solid #e2e8f0;box-shadow:0 2px 5px rgba(0,0,0,0.02);"
                                @mouseover="$el.style.transform='translateY(-3px)';$el.style.boxShadow='0 12px 24px rgba(0,0,0,0.06)';$el.style.borderColor=getColorStyle(service.color).border"
                                @mouseout="$el.style.transform='translateY(0)';$el.style.boxShadow='0 2px 5px rgba(0,0,0,0.02)';$el.style.borderColor='#e2e8f0'">

                                <div
                                    :style="`width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-right:16px;background:${getColorStyle(service.color).bg};color:${getColorStyle(service.color).text};`">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>

                                <div style="display:flex;flex-direction:column;gap:2px;">
                                    <span
                                        style="font-size:14px;font-weight:900;color:#0f172a;line-height:1.2;letter-spacing:-0.01em;"
                                        x-text="service.name"></span>
                                    <span
                                        style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;"
                                        x-text="service.price_type === 'per_kg' ? 'PER KG' : 'PER ITEM'"></span>
                                    <span style="font-size:15px;font-weight:900;color:#1d4ed8;margin-top:2px;"
                                        x-text="'Rs. ' + parseFloat(service.base_price).toFixed(2)"></span>
                                </div>
                            </button>
                        </template>
                        <div x-show="filteredServices.length === 0"
                            style="grid-column:1/-1;padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">
                            No services matched your search.
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================== Right: Order Panel ===================== -->
            <div
                class="flex-1 lg:flex-[1] bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col overflow-hidden">

                <!-- Customer Section -->
                <div style="padding:10px 14px;border-bottom:2px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span
                            style="font-size:11px;font-weight:900;color:#0f172a;text-transform:uppercase;letter-spacing:0.08em;">Order</span>
                        <button @click="$dispatch('open-customer-modal')"
                            style="font-size:11px;font-weight:800;padding:5px 10px;background:#f0fdf4;border:1.5px solid #86efac;color:#16a34a;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                            onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                            + New Customer
                        </button>
                    </div>

                    <!-- Search customers -->
                    <div style="position:relative;">
                        <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);" width="13"
                            height="13" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                        </svg>
                        <input type="text" x-model="customerSearch" @input="filterCustomers"
                            @focus="showCustDropdown = true" @click.away="showCustDropdown = false"
                            :placeholder="selectedCustomer ? 'Change customer…' : 'Search by name or phone…'"
                            style="width:100%;padding:8px 32px 8px 32px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:12px;font-weight:600;color:#0f172a;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">

                        <!-- Dropdown -->
                        <div x-show="showCustDropdown && filteredCustomers.length > 0"
                            style="position:absolute;z-index:20;width:100%;top:100%;left:0;margin-top:4px;background:white;border:1.5px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.08);max-height:160px;overflow-y:auto;">
                            <template x-for="cust in filteredCustomers" :key="cust.id">
                                <div @click="selectCustomer(cust)"
                                    style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f8fafc;transition:background 0.1s;"
                                    onmouseover="this.style.background='#eff6ff'"
                                    onmouseout="this.style.background='white'">
                                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;"
                                        x-text="cust.name"></p>
                                    <p style="font-size:10px;color:#64748b;margin:1px 0 0;font-weight:600;"
                                        x-text="cust.phone"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Selected Customer badge -->
                    <div x-show="selectedCustomer"
                        style="margin-top:12px;padding:12px 16px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;display:flex;flex-direction:column;gap:6px;position:relative;">
                        <button @click="clearCustomer()"
                            style="position:absolute;top:10px;right:12px;background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;"
                            title="Clear Customer" onmouseover="this.style.color='#ef4444'"
                            onmouseout="this.style.color='#94a3b8'">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <span style="font-size:14px;font-weight:900;color:#1e40af;padding-right:24px;"
                            x-text="selectedCustomer?.name"></span>
                        <span style="font-size:12px;color:#3b82f6;font-weight:700;"
                            x-text="selectedCustomer?.phone"></span>
                        <span style="font-size:11px;color:#60a5fa;font-weight:600;"
                            x-text="selectedCustomer?.address || 'No Address Provided'"></span>
                    </div>

                    <!-- Walk-in notice -->
                    <div x-show="!selectedCustomer"
                        style="margin-top:10px;font-size:11px;color:#d97706;font-weight:700;">
                        Walk-in customer. Search or create a new one above.
                    </div>
                </div>

                <!-- Cart (Scrollable) -->
                <div
                    style="flex:1;overflow-y:auto;padding:12px;background:#f8fafc;display:flex;flex-direction:column;gap:8px;">
                    <template x-if="cart.length === 0">
                        <div
                            style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#94a3b8;padding:40px 0;">
                            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                style="opacity:0.3;margin-bottom:8px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p style="font-size:11px;font-weight:800;margin:0;">No items added</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in cart" :key="index">
                        <div
                            style="position:relative;background:white;padding:8px 10px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,0.03);display:flex;flex-direction:column;gap:6px;">
                            <button @click="removeFromCart(index)"
                                style="position:absolute;top:8px;right:8px;background:none;border:none;cursor:pointer;color:#fca5a5;padding:3px;border-radius:6px;transition:all 0.15s;"
                                onmouseover="this.style.color='#ef4444';this.style.background='#fee2e2'"
                                onmouseout="this.style.color='#fca5a5';this.style.background='transparent'">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>

                            <div style="display:flex;flex-direction:column;gap:2px;padding-right:24px;">
                                <h4 style="font-size:13px;font-weight:900;color:#0f172a;margin:0;letter-spacing:-0.01em;"
                                    x-text="item.name"></h4>
                                <span style="font-size:10px;color:#64748b;font-weight:700;"
                                    x-text="'Rs. ' + parseFloat(item.base_price).toFixed(2) + (item.price_type === 'per_kg' ? ' /kg' : ' /item')"></span>
                            </div>

                            <div
                                style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f1f5f9;padding-top:8px;">
                                <div
                                    style="display:flex;align-items:center;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                                    <button @click="updateQty(index,-1)"
                                        style="padding:4px 10px;background:none;border:none;cursor:pointer;font-size:15px;font-weight:900;color:#64748b;transition:background 0.1s;"
                                        onmouseover="this.style.background='#e2e8f0'"
                                        onmouseout="this.style.background='transparent'">−</button>
                                    <input type="number" x-model.number="item.qty"
                                        style="width:36px;text-align:center;font-size:12px;font-weight:900;border:none;outline:none;padding:4px 0;background:white;color:#0f172a;"
                                        min="0.5" step="0.5">
                                    <button @click="updateQty(index,1)"
                                        style="padding:4px 10px;background:none;border:none;cursor:pointer;font-size:15px;font-weight:900;color:#64748b;transition:background 0.1s;"
                                        onmouseover="this.style.background='#e2e8f0'"
                                        onmouseout="this.style.background='transparent'">+</button>
                                </div>
                                <span style="font-size:14px;font-weight:900;color:#1d4ed8;"
                                    x-text="'Rs. ' + (item.base_price * item.qty).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Fixed Checkout Bar -->
                <div class="flex flex-col md:flex-row items-center justify-between gap-3"
                    style="padding:10px 14px;background:white;border-top:1.5px solid #e2e8f0;box-shadow:0 -4px 12px rgba(0,0,0,0.03);">
                    <div style="display:flex;flex-direction:column;gap:2px;width:100%;"
                        class="md:w-auto text-center md:text-left">
                        <span
                            style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Total
                            Amount</span>
                        <span style="font-size:22px;font-weight:900;color:#0f172a;letter-spacing:-0.02em;"
                            x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                    </div>
                    <button @click="if(!showCheckoutModal){ showCheckoutModal = true; }" :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'bg-green-500 cursor-not-allowed text-white shadow-none opacity-80' : 'bg-gradient-to-br from-blue-600 to-blue-700 shadow-[0_8px_20px_rgba(29,78,216,0.3)] hover:-translate-y-0.5 text-white cursor-pointer'"
                        class="w-full md:w-auto px-5 py-3 md:px-9 md:py-4 text-sm md:text-[15px] font-black rounded-2xl md:rounded-[40px] flex justify-center items-center gap-2 md:gap-3 transition-all duration-300 tracking-wide outline-none border-none">
                        Checkout
                        <svg width="18" height="18" class="md:w-5 md:h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ===================== Checkout Modal ===================== -->
        <div x-show="showCheckoutModal"
            x-effect="if(showCheckoutModal) $nextTick(() => { const dp = document.getElementById('due-date-input'); if(dp) { try { dp.showPicker(); } catch(e) { dp.focus(); dp.click(); } } })"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;">
            <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm" @click="showCheckoutModal = false">
            </div>
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md z-10 flex flex-col overflow-hidden"
                @click.stop>
                <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-black text-base text-gray-800">🛒 Checkout</h3>
                    <button @click="showCheckoutModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 space-y-3 overflow-y-auto max-h-[80vh]">


                    <div class="border-t border-gray-100"></div>

                    <!-- Pickup Date -->
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Pickup Date</span>
                        <input type="datetime-local" id="due-date-input" x-model="dueDate" @input="setTimeout(() => $el.blur(), 100)"
                            @change="setTimeout(() => $el.blur(), 100)"
                            class="text-xs border-gray-200 rounded-lg p-1.5 font-bold outline-none text-gray-800">
                    </div>

                    <div class="border-t border-gray-100 my-2"></div>

                    <!-- Total -->
                    <div class="flex justify-between items-center py-2 bg-blue-50 rounded-xl px-3">
                        <span class="text-sm font-black text-gray-700">Total</span>
                        <span class="text-lg font-black text-blue-700" x-text="'Rs. ' + total.toFixed(2)"></span>
                    </div>

                    <!-- Payment Methods (Side by Side) -->
                    <div class="pt-1">
                        <p class="text-[10px] font-bold text-gray-400 mb-2 uppercase tracking-wide">Payment Method</p>
                        <div class="flex gap-2">
                            <button @click="paymentMode = 'pay_now'"
                                :class="paymentMode === 'pay_now' ? 'border-green-500 bg-green-50 ring-2 ring-green-100' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                class="flex-1 flex flex-col items-center justify-center p-3 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_now' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'"
                                    class="w-6 h-6 rounded-full flex items-center justify-center transition-all mb-1">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="font-black text-gray-800 text-xs">Pay Now</span>
                            </button>

                            <button @click="paymentMode = 'pay_on_pickup'"
                                :class="paymentMode === 'pay_on_pickup' ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-100' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                class="flex-1 flex flex-col items-center justify-center p-3 border-2 rounded-xl transition-all cursor-pointer">
                                <div :class="paymentMode === 'pay_on_pickup' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-500'"
                                    class="w-6 h-6 rounded-full flex items-center justify-center transition-all mb-1">
                                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="font-black text-gray-800 text-xs">Pay on Pickup</span>
                            </button>
                        </div>

                        <!-- Sub options for Pay Now -->
                        <div x-show="paymentMode === 'pay_now'"
                            class="flex gap-2 mt-2 bg-gray-50 p-2 rounded-xl border border-gray-200">
                            <button @click.stop="paymentMethod = 'cash'"
                                :class="paymentMethod === 'cash' ? 'bg-green-600 text-white shadow-md scale-105' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                                class="flex-1 px-3 py-2 rounded-xl text-xs font-black transition-all">💵 CASH</button>
                            <button @click.stop="paymentMethod = 'card'"
                                :class="paymentMethod === 'card' ? 'bg-blue-600 text-white shadow-md scale-105' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'"
                                class="flex-1 px-3 py-2 rounded-xl text-xs font-black transition-all">💳 CARD</button>
                        </div>
                    </div>

                    <!-- Confirm Order Button -->
                    <div class="pt-2">
                        <button @click="placeOrder" :disabled="isPlacingOrder"
                            :class="isPlacingOrder ? 'opacity-60 cursor-not-allowed' : 'hover:from-blue-700 hover:to-blue-800 hover:shadow-2xl transform hover:-translate-y-0.5'"
                            class="w-full py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-black rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                            <template x-if="!isPlacingOrder">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                            <template x-if="isPlacingOrder">
                                <svg class="animate-spin" width="16" height="16" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </template>
                            <span x-text="isPlacingOrder ? 'Placing Order...' : 'Confirm Order'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ===================== Add New Customer Modal ===================== -->
    <div x-data="{ open: false }" x-on:open-customer-modal.window="open = true"
        x-on:close-customer-modal.window="open = false" x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black bg-opacity-40 backdrop-blur-sm" @click="open = false"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md z-10" @click.stop>
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="font-black text-gray-800 text-lg">Add New Customer</h3>
                </div>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="new-customer-form" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="nc-name" required placeholder="e.g. Kamal Perera"
                        class="w-full text-base border-2 border-gray-200 rounded-xl p-3 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 font-black transition-colors outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="text" id="nc-phone" required placeholder="e.g. 077 123 4567"
                        class="w-full text-base border-2 border-gray-200 rounded-xl p-3 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 font-black transition-colors outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Address (Optional)</label>
                    <input type="text" id="nc-address" placeholder="Home address"
                        class="w-full text-base border-2 border-gray-200 rounded-xl p-3 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 font-black transition-colors outline-none">
                </div>
            </form>
            <div class="flex gap-3 p-5 pt-0 bg-white rounded-b-3xl">
                <button @click="open = false"
                    class="flex-1 py-3 border-2 border-gray-200 text-gray-600 font-black rounded-xl hover:bg-gray-50 transition-colors text-base">Cancel</button>
                <button @click="createAndSelectCustomer(open)" id="save-customer-btn"
                    class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white font-black rounded-xl shadow-lg hover:shadow-xl transition-all text-base transform hover:-translate-y-0.5">
                    Save Customer
                </button>
            </div>
        </div>
    </div>

    <!-- ===================== Order Success Modal ===================== -->
    <div x-data="{ show: false, orderId: '', orderNum: '', custName: '', total: '', payMode: '', dueDate: '' }"
        x-on:order-placed.window="show = true; orderId = $event.detail.orderId; orderNum = $event.detail.orderNumber; custName = $event.detail.customerName; total = $event.detail.total; payMode = $event.detail.paymentMode; dueDate = $event.detail.dueDate; printReceipt(orderId);"
        x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm z-10 text-center p-8" @click.stop>
            <div
                class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Order Placed!</h3>
            <p class="text-sm text-gray-500 mb-6 font-bold">The laundry order has been registered successfully.</p>

            <div class="bg-gray-50 border-2 border-gray-100 rounded-2xl p-5 text-left space-y-3 mb-6 shadow-sm">
                <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                    <span class="text-gray-500 font-bold text-xs uppercase tracking-wide">Order #:</span>
                    <span class="font-black text-blue-700 text-base" x-text="orderNum"></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                    <span class="text-gray-500 font-bold text-xs uppercase tracking-wide">Customer:</span>
                    <span class="font-black text-gray-800 text-sm" x-text="custName"></span>
                </div>
                <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                    <span class="text-gray-500 font-bold text-xs uppercase tracking-wide">Total:</span>
                    <span class="font-black text-green-700 text-base" x-text="'Rs. ' + total"></span>
                </div>
                <div class="flex justify-between items-center pt-1" x-show="dueDate">
                    <span class="text-gray-500 font-bold text-xs uppercase tracking-wide">Pickup Date:</span>
                    <span class="font-black text-orange-600 text-xs"
                        x-text="dueDate ? new Date(dueDate).toLocaleDateString('en-US', {day:'2-digit', month:'short', year:'numeric'}) : ''"></span>
                </div>
                <div class="flex justify-between items-center pt-1">
                    <span class="text-gray-500 font-bold text-xs uppercase tracking-wide">Payment:</span>
                    <span class="font-black text-gray-800 text-xs"
                        x-text="payMode === 'pay_now' ? 'Paid (Cash/Card)' : 'Pay on Pickup'"></span>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button @click="printReceipt(orderId)"
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white text-base font-black rounded-xl shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Thermal Bill
                </button>

                <button @click="show = false"
                    class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition-all">
                    Skip & Create New Order
                </button>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('posSystem', () => ({
                allServices: @json($services),
                allCustomers: @json($customers),
                searchQuery: '',
                customerSearch: '',
                filteredCustomers: [],
                showCustDropdown: false,
                selectedCustomer: null,
                cart: [],

                showCheckoutModal: false,
                isPlacingOrder: false,
                paymentMode: 'pay_now',
                paymentMethod: 'cash',
                dueDate: '',
                specialName: '',
                specialPrice: 0,

                get filteredServices() {
                    if (!this.searchQuery) return this.allServices;
                    return this.allServices.filter(s => s.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                },

                getColorStyle(colorName) {
                    const styles = {
                        'blue': { bg: '#eff6ff', border: '#bfdbfe', text: '#1d4ed8' },
                        'green': { bg: '#f0fdf4', border: '#bbf7d0', text: '#15803d' },
                        'purple': { bg: '#fdf4ff', border: '#e9d5ff', text: '#7e22ce' },
                        'orange': { bg: '#fff7ed', border: '#fed7aa', text: '#c2410c' },
                        'teal': { bg: '#f0fdfa', border: '#99f6e4', text: '#0f766e' },
                        'red': { bg: '#fef2f2', border: '#fecaca', text: '#b91c1c' }
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
                },

                clearCustomer() {
                    this.selectedCustomer = null;
                    this.customerSearch = '';
                },

                addToCart(service) {
                    let existing = this.cart.find(i => i.id === service.id);
                    if (existing) { existing.qty += 1; } else { this.cart.push({ ...service, qty: 1 }); }
                },

                removeFromCart(index) { this.cart.splice(index, 1); },

                updateQty(index, change) {
                    let newQty = parseFloat(this.cart[index].qty) + change;
                    if (newQty > 0) this.cart[index].qty = Math.round(newQty * 10) / 10;
                    else this.removeFromCart(index);
                },

                get subtotal() { return this.cart.reduce((s, i) => s + (i.base_price * i.qty), 0); },

                get total() { return this.subtotal; },

                addSpecialToCart() {
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

                async placeOrder() {
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
                        due_date: this.dueDate
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
                                    total: this.total.toFixed(2),
                                    paymentMode: this.paymentMode,
                                    dueDate: data.due_date,
                                    cart: data.cart
                                }
                            }));
                            printOrderChit(data.order_id);
                            this.cart = [];
                            this.selectedCustomer = null;
                            this.discountValue = 0;
                            this.additionalValue = 0;
                            this.paymentMode = 'pay_now';
                            this.paymentMethod = 'cash';
                            this.dueDate = '';
                        } else {
                            alert('Order failed: ' + (data.error || 'Unknown error'));
                        }
                    } catch (err) { console.error(err); alert('Network error. Please try again.'); }
                    finally { this.isPlacingOrder = false; }
                },

                init() {
                    window.posAddCustomer = (customer) => {
                        this.allCustomers.push(customer);
                        this.selectCustomer(customer);
                    };
                }
            }));
        });

        document.getElementById('save-customer-btn').addEventListener('click', async function () {
            const name = document.getElementById('nc-name').value.trim();
            const phone = document.getElementById('nc-phone').value.trim();
            const address = document.getElementById('nc-address').value.trim();

            if (!name || !phone) { alert('Name and phone are required.'); return; }

            const res = await fetch("{{ route('customers.store.pos') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ name, phone, address, source: 'pos' })
            });
            const data = await res.json();
            if (data.success) {
                window.posAddCustomer(data.customer);
                document.getElementById('nc-name').value = '';
                document.getElementById('nc-phone').value = '';
                document.getElementById('nc-address').value = '';
                window.dispatchEvent(new Event('close-customer-modal'));
            } else {
                alert('Failed to save customer.');
            }
        });

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