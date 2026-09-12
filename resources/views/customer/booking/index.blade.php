<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="font-bold text-xl sm:text-2xl text-slate-800 tracking-tight">Book a Pickup</h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 hidden sm:block">Schedule a laundry pickup right to your doorstep.</p>
                </div>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <!-- Google Maps / Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        .map-container { height: 380px; width: 100%; border-radius: 1rem; z-index: 10; }
        .step-active { border-color: #2563eb; background-color: #eff6ff; color: #1e3a8a; }
        .step-inactive { border-color: #e2e8f0; background-color: #f8fafc; color: #94a3b8; }
        .step-completed { border-color: #10b981; background-color: #ecfdf5; color: #047857; }
        .custom-radio:checked + div { border-color: #2563eb; background-color: #eff6ff; }
        .custom-radio:checked + div .radio-dot { background-color: #2563eb; transform: scale(1); }
        .radio-dot { transform: scale(0); transition: transform 0.2s ease-in-out; }

        /* Map route line animation */
        .map-pulse { animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; }
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.6; }
            100% { transform: scale(1); opacity: 1; }
        }
        /* Custom Leaflet Tile Style */
        .leaflet-tile-pane { filter: saturate(1.1) brightness(1.02); }
    </style>

    <div x-data="bookingWizard({{ Js::from($categories) }}, {{ auth('customer')->check() ? 'true' : 'false' }}, {{ Js::from(auth('customer')->user()) }}, {{ $minimumOrderLimit }}, {{ Js::from($deliveryRanges) }}, {{ Js::from($workshops) }}, {{ Js::from($turnaroundTypes) }}, {{ Js::from($extras) }}, {{ $shopLat }}, {{ $shopLng }}, {{ Js::from($pickupTimePeriods) }})" x-init="initData()">
        
        <!-- Wizard Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative max-w-3xl mx-auto">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-200 rounded-full z-0"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-blue-600 rounded-full z-0 transition-all duration-500" :style="`width: ${((step - 1) / 3) * 100}%`"></div>
                
                <template x-for="i in 4" :key="i">
                    <div class="relative z-10 flex flex-col items-center bg-transparent">
                        <div @click="goToStep(i)" class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-base border-4 transition-all duration-300 cursor-pointer"
                             :class="step === i ? 'step-active scale-110 shadow-md' : (step > i ? 'step-completed' : 'step-inactive bg-white')">
                            <i x-show="step > i" class="fa-solid fa-check"></i>
                            <span x-show="step <= i" x-text="i"></span>
                        </div>
                        <span class="text-[11px] font-extrabold mt-2 uppercase tracking-wider absolute top-14 w-32 text-center" 
                              :class="step === i ? 'text-blue-700' : 'text-slate-400'"
                              x-text="['Services', 'Account', 'Pickup', 'Delivery & Pay'][i-1]"></span>
                    </div>
                </template>
            </div>
        </div>

        <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm" class="mt-16 lg:w-3/4 mx-auto pb-28" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="item_description" x-model="formattedCart">
            <input type="hidden" name="cart_json" x-bind:value="JSON.stringify(cart)">
            <input type="hidden" name="estimated_delivery_fee" x-model="estimatedDeliveryFee">
            
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 mb-6">
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            
            <!-- Step 1: Services (Drill-Down UI) -->
            <div x-show="step === 1" x-transition.opacity.duration.300ms class="pb-24">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-slate-800">Select Services</h3>
                    <p class="text-sm text-slate-500 mt-1">Navigate through categories to add items to your cart.</p>
                </div>

                <!-- Drill Down Container -->
                <div class="bg-white border border-slate-200 rounded-lg shadow-sm flex flex-col min-h-[500px]">
                    
                    <!-- Header / Navigation Bar -->
                    <div class="bg-slate-50 px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                        <!-- Back Button -->
                        <button type="button" x-show="currentViewLevel > 0" @click="goBackLevel()" class="text-slate-600 hover:text-slate-900 flex items-center gap-2 text-sm font-medium transition-colors">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        
                        <!-- Title -->
                        <div x-show="currentViewLevel === 0" class="text-sm font-semibold text-slate-800">All Categories</div>
                        <div x-show="currentViewLevel === 1" class="text-sm font-semibold text-slate-800" x-text="selectedMainCategory?.name"></div>
                        <div x-show="currentViewLevel === 2" class="text-sm font-semibold text-slate-800" x-text="selectedSubcategory?.name || selectedMainCategory?.name"></div>
                    </div>

                    <div class="p-5 flex-1 overflow-y-auto custom-scrollbar">
                        <!-- Level 0: Main Categories Grid -->
                        <div x-show="currentViewLevel === 0">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 sm:gap-4">
                                <template x-for="cat in categories" :key="cat.id">
                                    <button type="button" @click="selectLevel1(cat)" class="bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group w-full">
                                        <div class="w-full aspect-[4/3] bg-blue-50 text-blue-600 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="cat.image_path">
                                                    <img :src="cat.image_path?.startsWith('http') ? cat.image_path : '/' + cat.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!cat.image_path">
                                                    <i class="fa-solid fa-shirt text-4xl opacity-40"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-4 w-full bg-white relative z-10 border-t border-slate-50">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full truncate" x-text="cat.name"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Level 1: Subcategories Grid -->
                        <div x-show="currentViewLevel === 1">
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 sm:gap-4">
                                <template x-for="sub in currentSubcategories" :key="sub.id">
                                    <button type="button" @click="selectLevel2(sub)" class="bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group w-full">
                                        <div class="w-full aspect-[4/3] bg-slate-50 text-slate-400 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="sub.image_path">
                                                    <img :src="sub.image_path?.startsWith('http') ? sub.image_path : '/' + sub.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!sub.image_path">
                                                    <i class="fa-solid fa-layer-group text-3xl opacity-40 group-hover:text-blue-400 transition-colors"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-3 w-full bg-white relative z-10 border-t border-slate-50">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full truncate" x-text="sub.name"></span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="currentSubcategories.length === 0" class="col-span-full py-12 text-center text-slate-500 text-sm font-medium">
                                    No subcategories found.
                                </div>
                            </div>
                        </div>

                        <!-- Level 2: Services List -->
                        <div x-show="currentViewLevel === 2" class="space-y-4">
                            <template x-for="service in currentServices" :key="service.id">
                                <div class="bg-white border-2 border-slate-100 rounded-xl p-4 flex gap-4 transition-all hover:border-blue-200 hover:shadow-md cursor-pointer group" @click="openQtyModal(service)">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-blue-600 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-jug-detergent"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition-colors" x-text="service.name"></div>
                                        <div class="text-[10px] text-slate-500 font-medium mt-0.5 leading-tight" x-text="service.description || 'Professional cleaning'"></div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="text-blue-600 font-bold text-sm">LKR <span x-text="service.base_price"></span></div>
                                            <div class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider" x-text="service.price_type === 'per_kg' ? 'Per KG' : 'Per Item'"></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center">
                                        <button type="button" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors flex items-center justify-center shadow-sm">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="currentServices.length === 0" class="py-12 text-center text-slate-500 text-sm font-medium">
                                No services found in this category.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Special Instructions (Optional)</label>
                    <textarea name="notes" id="booking_notes" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-slate-400" placeholder="E.g., Please fold shirts carefully..."></textarea>
                </div>
            </div>

            <!-- Fixed Bottom Cart Bar -->
            <div x-show="step === 1" x-transition.opacity style="display:none;" class="fixed bottom-0 left-0 w-full z-40 bg-white border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
                
                <!-- Expandable Cart List -->
                <div x-show="isCartExpanded" class="bg-slate-50 border-b border-slate-200 max-h-[40vh] overflow-y-auto custom-scrollbar">
                    <div class="max-w-4xl mx-auto p-4 space-y-2">
                        <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Your Cart Items</h4>
                        <template x-for="(item, index) in cart" :key="item.cart_key">
                            <div class="flex items-center justify-between bg-white p-3 rounded-md border border-slate-200 shadow-sm">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="font-medium text-sm text-slate-800 truncate" x-text="item.name"></div>
                                    <div class="text-xs font-medium text-blue-600 mt-1">LKR <span x-text="item.base_price"></span> x <span x-text="item.qty"></span> = LKR <span x-text="(parseFloat(item.base_price) * item.qty).toFixed(2)"></span></div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="flex items-center bg-slate-50 rounded-md p-1 border border-slate-200">
                                        <button type="button" @click="updateCartQty(index, -1)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 shadow-sm hover:text-red-500 border border-slate-200"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                        <span class="w-8 text-center text-xs font-semibold text-slate-800" x-text="item.qty"></span>
                                        <button type="button" @click="updateCartQty(index, 1)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 shadow-sm hover:text-blue-600 border border-slate-200"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                    </div>
                                    <button type="button" @click="removeFromCartByIndex(index)" class="text-red-400 hover:text-red-600 p-2"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </template>
                        <div x-show="cart.length === 0" class="text-center p-4 text-sm font-bold text-slate-400">Cart is empty</div>
                    </div>
                </div>

                <!-- Main Bar -->
                <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 cursor-pointer" @click="isCartExpanded = !isCartExpanded">
                        <div class="w-10 h-10 rounded-md bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center relative shadow-sm">
                            <i class="fa-solid fa-basket-shopping"></i>
                            <span x-show="cart.length > 0" class="absolute -top-2 -right-2 w-5 h-5 bg-blue-600 text-white text-[10px] font-semibold rounded-full flex items-center justify-center shadow-sm" x-text="cart.reduce((sum, i) => sum + i.qty, 0)"></span>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-slate-500 flex items-center gap-1">Cart Total <i class="fa-solid text-[10px] ml-1 transition-transform" :class="isCartExpanded ? 'fa-chevron-down' : 'fa-chevron-up'"></i></div>
                            <div class="text-lg font-semibold text-slate-800">LKR <span x-text="baseCartTotal.toFixed(2)"></span></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button type="button" @click="goToStep(2)" class="hidden sm:flex text-slate-500 hover:text-slate-800 font-medium items-center gap-2 px-4 py-2 border border-transparent hover:border-slate-200 rounded-md transition-all">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="openGlobalCheckoutModal()" :disabled="cart.length === 0" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 sm:px-8 py-2.5 rounded-md font-semibold transition-colors shadow-sm flex items-center gap-2">
                            Checkout <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quantity Selection Modal -->
            <div x-show="isQtyModalOpen" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div x-show="isQtyModalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isQtyModalOpen = false"></div>
                <div x-show="isQtyModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-lg shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
                    <div class="p-6 text-center">
                        <h3 class="text-lg font-semibold text-slate-800 mb-1" x-text="selectedService?.name"></h3>
                        <p class="text-sm font-medium text-slate-500 mb-6">LKR <span x-text="selectedService?.base_price"></span></p>
                        
                        <div class="flex items-center justify-center gap-6">
                            <button type="button" @click="if(serviceQty > 1) serviceQty--" class="w-10 h-10 rounded-md border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-medium transition-colors"><i class="fa-solid fa-minus"></i></button>
                            <div class="text-2xl font-semibold text-slate-800 w-12 text-center" x-text="serviceQty"></div>
                            <button type="button" @click="serviceQty++" class="w-10 h-10 rounded-md border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-medium transition-colors"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-3">
                        <button type="button" @click="isQtyModalOpen = false" class="flex-1 py-2.5 rounded-md font-medium text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 transition-colors">Cancel</button>
                        <button type="button" @click="confirmAddToCart()" class="flex-1 py-2.5 rounded-md font-medium text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-colors">Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Global Checkout Modal -->
            <div x-show="isGlobalCheckoutModalOpen" style="display:none;" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                <div x-show="isGlobalCheckoutModalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isGlobalCheckoutModalOpen = false"></div>
                <div x-show="isGlobalCheckoutModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-lg shadow-xl w-full max-w-md flex flex-col max-h-[90vh] overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-white">
                        <h3 class="text-lg font-semibold text-slate-800">Finalize Order</h3>
                        <button type="button" @click="isGlobalCheckoutModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-md bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <div class="p-5 overflow-y-auto custom-scrollbar flex-1 space-y-6">
                        <!-- Turnaround Type -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Do you need quick service? <span class="text-red-500">*</span></label>
                            <p class="text-xs text-slate-500 font-medium mb-3">Charges apply only to the base services (not applied to extras).</p>
                            <select x-model="globalTurnaround" class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-md focus:ring-2 focus:ring-blue-500 font-medium">
                                <template x-for="tt in turnaroundTypes" :key="tt.id">
                                    <option :value="tt.id" x-text="tt.name"></option>
                                </template>
                            </select>
                        </div>



                        <!-- Extras -->
                        <div x-show="extras.length > 0">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Do you need any extra services?</label>
                            <p class="text-xs text-slate-500 font-medium mb-3">Add extra services to your order.</p>
                            <div class="space-y-2">
                                <template x-for="extra in extras" :key="extra.id">
                                    <div class="flex items-center justify-between p-3 rounded-md border border-slate-200 bg-white shadow-sm">
                                        <div class="flex-1">
                                            <div class="text-sm font-bold text-slate-800" x-text="extra.name"></div>
                                            <div class="text-xs font-semibold text-blue-600 mt-0.5" x-text="'+ LKR ' + extra.price + ' / item'"></div>
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <button type="button" @click="globalExtras[extra.id] = Math.max(0, (globalExtras[extra.id] || 0) - 1)" class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                            <span class="w-4 text-center text-sm font-bold text-slate-800" x-text="globalExtras[extra.id] || 0"></span>
                                            <button type="button" @click="globalExtras[extra.id] = (globalExtras[extra.id] || 0) + 1" class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-colors"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 border-t border-slate-200 bg-slate-50 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wide">Grand Total</div>
                            <div class="text-xl font-semibold text-blue-600">LKR <span x-text="calculatedGrandTotal.toFixed(2)"></span></div>
                            <div x-show="calculatedGrandTotal < minLimit" class="text-[10px] text-red-500 font-medium mt-1">Min. order: LKR <span x-text="minLimit"></span></div>
                        </div>
                        <button type="button" @click="proceedToDelivery()" :disabled="calculatedGrandTotal < minLimit" class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white py-3 rounded-md font-semibold transition-colors shadow-sm text-center flex items-center justify-center gap-2">
                            Continue <i class="fa-solid fa-arrow-right text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: Authentication -->
            <div x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    
                    <!-- If already logged in, show account info instead of form -->
                    <template x-if="isAuthenticated">
                        <div class="text-center py-8">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 border-4 border-blue-100">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="mb-8">
                                <h3 class="text-xl md:text-2xl font-bold text-slate-800 mb-2">Welcome back, <span x-text="authCustomer.name"></span>!</h3>
                                <p class="text-sm text-slate-500 font-medium">You are currently logged in with <span x-text="authCustomer.phone"></span>.</p>
                            </div>
                        </div>
                    </template>

                    <!-- If not logged in, show auth tabs -->
                    <template x-if="!isAuthenticated">
                        <div>
                            <div class="flex justify-center mb-8">
                                <div class="bg-slate-100 p-1 rounded-xl flex gap-1">
                                    <button type="button" @click="authTab = 'login'" :class="authTab === 'login' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all">Login</button>
                                    <button type="button" @click="authTab = 'register'" :class="authTab === 'register' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700'" class="px-6 py-2 rounded-lg font-bold text-sm transition-all">Register</button>
                                </div>
                            </div>
                            
                            <div x-show="authError" class="p-4 bg-red-50 border border-red-200 text-red-600 text-sm font-bold rounded-xl mb-6 text-center" x-text="authError"></div>

                            <!-- Login Form -->
                            <div x-show="authTab === 'login'">
                                <div class="space-y-4 max-w-md mx-auto">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                                        <input type="text" x-model="loginData.phone" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="07XXXXXXXX">
                                    </div>
                                    <div x-data="{ showPass: false }">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <div class="relative">
                                            <input :type="showPass ? 'text' : 'password'" x-model="loginData.password" class="w-full px-4 py-3 pr-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Password">
                                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" @click="submitLogin()" :disabled="authLoading" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold mt-2 transition-all flex justify-center items-center gap-2">
                                        <span x-show="!authLoading">Login to Continue</span>
                                        <i x-show="authLoading" class="fa-solid fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Register Form -->
                            <div x-show="authTab === 'register'">
                                <div class="space-y-4 max-w-md mx-auto">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                                        <input type="text" x-model="registerData.name" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="John Doe">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                                        <input type="text" x-model="registerData.phone" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="07XXXXXXXX">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Email (Optional)</label>
                                        <input type="email" x-model="registerData.email" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="john@example.com">
                                    </div>
                                    <div x-data="{ showPass: false }">
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <div class="relative">
                                            <input :type="showPass ? 'text' : 'password'" x-model="registerData.password" class="w-full px-4 py-3 pr-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="Password">
                                            <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" @click="submitRegister()" :disabled="authLoading" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-xl font-bold mt-2 transition-all flex justify-center items-center gap-2">
                                        <span x-show="!authLoading">Create Account & Continue</span>
                                        <i x-show="authLoading" class="fa-solid fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="flex justify-between items-center mt-6">
                    <!-- Only show next if authenticated -->
                    <button type="button" x-show="isAuthenticated" @click="goToStep(3)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-md flex items-center gap-2 mt-4 ml-auto">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Pickup Details -->
            <div x-show="step === 3" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h3 class="text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                        <span class="bg-amber-50 text-amber-500 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-truck-pickup"></i></span>
                        Pickup Details
                    </h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">When and where should we pick up your laundry?</p>

                    <div class="grid sm:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Pickup Date</label>
                            <input type="date" name="pickup_date" id="pickup_date_input" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" min="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Pickup Time</label>
                            <select name="pickup_time_period_id" id="pickup_time_input" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold">
                                <option value="">Select a time slot...</option>
                                <template x-for="period in branchPickupTimes" :key="period.id">
                                    <option :value="period.id" x-text="(period.label || '') + ' (' + formatTime(period.start_time) + ' - ' + formatTime(period.end_time) + ')'"></option>
                                </template>
                                <template x-if="branchPickupTimes.length === 0">
                                    <option disabled value="">No time slots available for this branch</option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-bold text-slate-700 flex items-center gap-2">
                                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                Pin your pickup location
                            </label>
                            <button type="button" @click="findMe('pickup')" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm shadow-blue-500/30">
                                <i class="fa-solid fa-location-crosshairs"></i> Find Me
                            </button>
                        </div>
                        
                        <!-- Map Wrapper -->
                        <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-lg">
                            
                            <!-- Centered Pin -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                    class="text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                    <template x-if="!isOutOfRange">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span x-show="pickupDistance > 0" class="text-[9px] font-bold opacity-80 flex items-center gap-1">
                                                <i class="fa-solid fa-route text-blue-300"></i>
                                                <span x-text="pickupDistance"></span> km road distance
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="isOutOfRange">
                                        <span class="animate-pulse"><i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of delivery range</span>
                                    </template>
                                </div>
                                <!-- Pin icon -->
                                <div class="map-pulse">
                                    <i :class="isOutOfRange ? 'text-red-500' : 'text-blue-600'" 
                                        class="fa-solid fa-location-dot text-4xl drop-shadow-xl transition-colors"></i>
                                </div>
                            </div>

                            <!-- Map -->
                            <div id="pickup_map" class="map-container"></div>

                            <!-- Bottom Info Bar inside map -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-900/90 to-transparent p-3 z-[300] pointer-events-none">
                                <div class="flex items-center justify-between">
                                    <div x-show="nearestWorkshop" class="flex items-center gap-2">
                                        <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-store text-white text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-white text-[10px] font-bold opacity-70">Nearest Branch</div>
                                            <div class="text-white text-xs font-bold" x-text="nearestWorkshop ? nearestWorkshop.name : ''"></div>
                                        </div>
                                    </div>
                                    <div x-show="pickupDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                        <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                        <span class="text-white text-xs font-bold"><span x-text="pickupDistance"></span> km</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="pickup_lat">
                        <input type="hidden" name="longitude" id="pickup_lng">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Full Pickup Address</label>
                        <textarea name="pickup_address" id="pickup_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" placeholder="House no, Street name, City"></textarea>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <div class="flex items-center justify-between w-full">
                        <button type="button" @click="goToStep(2)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="goToStep(4)" :disabled="isOutOfRange || servicesLoading" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-md flex items-center gap-2">
                            <span x-show="!servicesLoading">Next: Delivery & Pay <i class="fa-solid fa-arrow-right"></i></span>
                            <span x-show="servicesLoading"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading Services...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 4: Delivery & Payment -->
            <div x-show="step === 4" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="space-y-6">

                    <!-- Requested Delivery Time Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h3 class="text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                            <span class="bg-blue-50 text-blue-500 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-clock"></i></span>
                            Requested Delivery Time
                        </h3>
                        <p class="text-sm text-slate-500 mb-6 font-medium">When would you like to receive your clean items back? (Optional)</p>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Preferred Date</label>
                                <input type="date" name="delivery_date" x-model="deliveryDate" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" min="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Preferred Time Slot</label>
                                <select name="delivery_time_period_id" x-model="deliveryTimePeriodId" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold">
                                    <option value="">Select time slot...</option>
                                    <template x-for="period in branchPickupTimes" :key="period.id">
                                        <option :value="period.id" x-text="formatTime(period.start_time) + ' - ' + formatTime(period.end_time)"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h3 class="text-xl font-bold text-slate-800 mb-2 flex items-center gap-3">
                            <span class="bg-emerald-50 text-emerald-500 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-box-open"></i></span>
                            Return Delivery
                        </h3>
                        <p class="text-sm text-slate-500 mb-6 font-medium">How would you like to receive your clean laundry?</p>

                        <div class="grid sm:grid-cols-2 gap-4 mb-6">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="delivery_preference" value="deliver_to_address" class="custom-radio sr-only" x-model="deliveryPref">
                                <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 transition-all flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                        <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-slate-800">Deliver to me</div>
                                        <div class="text-[11px] font-bold text-slate-500 uppercase mt-0.5">Rider brings it back</div>
                                    </div>
                                </div>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="delivery_preference" value="pickup_from_branch" class="custom-radio sr-only" x-model="deliveryPref">
                                <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 transition-all flex items-center gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                        <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-slate-800">I will pick up</div>
                                        <div class="text-[11px] font-bold text-slate-500 uppercase mt-0.5">Collect from branch</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div x-show="deliveryPref === 'deliver_to_address'" x-collapse>
                            <label class="flex items-center gap-3 cursor-pointer p-4 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                                <input type="checkbox" name="same_as_pickup" value="1" x-model="sameAsPickup" class="w-5 h-5 text-blue-600 rounded border-blue-200 focus:ring-blue-500 bg-white">
                                <span class="font-bold text-sm text-blue-800">Delivery address is same as pickup</span>
                            </label>

                            <div x-show="!sameAsPickup" x-collapse>
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="block text-sm font-bold text-slate-700 flex items-center gap-2">
                                            <span class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                            Pin your delivery location
                                        </label>
                                    </div>
                                    <!-- Delivery Map Wrapper -->
                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 shadow-lg">
                                        <!-- Centered Pin -->
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                            <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                                class="text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                                <template x-if="!isOutOfRange">
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <span x-show="deliveryDistance > 0" class="text-[9px] font-bold opacity-80 flex items-center gap-1">
                                                            <i class="fa-solid fa-route text-blue-300"></i>
                                                            <span x-text="deliveryDistance"></span> km road distance
                                                        </span>
                                                    </div>
                                                </template>
                                                <template x-if="isOutOfRange">
                                                    <span class="animate-pulse"><i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of delivery range</span>
                                                </template>
                                            </div>
                                            <div class="map-pulse">
                                                <i :class="isOutOfRange ? 'text-red-500' : 'text-emerald-500'"
                                                    class="fa-solid fa-location-dot text-4xl drop-shadow-xl transition-colors"></i>
                                            </div>
                                        </div>
                                        <!-- Map -->
                                        <div id="delivery_map" class="map-container"></div>
                                        <!-- Bottom Info Bar -->
                                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-900/90 to-transparent p-3 z-[300] pointer-events-none">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-house text-white text-[10px]"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-white text-[10px] font-bold opacity-70">Delivery Location</div>
                                                        <div class="text-white text-xs font-bold">Move pin to set location</div>
                                                    </div>
                                                </div>
                                                <div x-show="deliveryDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                                    <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                                    <span class="text-white text-xs font-bold"><span x-text="deliveryDistance"></span> km</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="delivery_lat" id="delivery_lat">
                                    <input type="hidden" name="delivery_lng" id="delivery_lng">
                                </div>
                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">Full Delivery Address</label>
                                    <textarea name="delivery_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="House no, Street name, City"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary Card -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-xl shadow-lg border border-slate-700 p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Order Summary</h3>
                                <p class="text-slate-400 text-xs font-medium">Estimated costs — final bill after inspection</p>
                            </div>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-shirt text-blue-400 text-xs"></i> Services (Estimate)
                                </span>
                                <span class="text-white text-sm font-bold">LKR <span x-text="calculatedGrandTotal.toFixed(2)"></span></span>
                            </div>

                            <div x-show="estimatedDeliveryFee > 0" class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-truck text-emerald-400 text-xs"></i> Delivery Fee
                                </span>
                                <span class="text-white text-sm font-bold">LKR <span x-text="estimatedDeliveryFee.toFixed(2)"></span></span>
                            </div>

                            <div x-show="pickupDistance > 0" class="flex justify-between items-center py-1.5 text-slate-400 text-xs">
                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Pickup distance</span>
                                <span class="font-bold"><span x-text="pickupDistance"></span> km from branch</span>
                            </div>
                        </div>

                        <div class="bg-blue-600 rounded-xl p-4 flex justify-between items-center">
                            <span class="text-blue-100 font-bold text-sm">Total Estimated Amount</span>
                            <span class="text-white font-bold text-xl">LKR <span x-text="totalPrice.toFixed(2)"></span></span>
                        </div>

                        <p class="text-[11px] text-slate-500 text-center mt-4">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Payment collected by rider after workshop inspection. WhatsApp notification will be sent.
                        </p>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button type="button" @click="goToStep(3)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" :disabled="isOutOfRange" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2 text-lg">
                        Confirm Booking <i class="fa-solid fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function bookingWizard(categories, isLoggedIn, user, minOrderLimit, deliveryRanges, workshops, turnaroundTypes, extras, shopLat, shopLng, pickupTimePeriods) {
                return {
                    categories: categories,
                    turnaroundTypes: turnaroundTypes,
                    extras: extras,
                    shopLat: shopLat,
                    shopLng: shopLng,
                    branchPickupTimes: pickupTimePeriods || [],
                    servicesLoading: false,
                    detectedBranchId: null,
                    
                    formatTime(timeStr) {
                        if (!timeStr) return '';
                        const parts = timeStr.split(':');
                        const hour = parseInt(parts[0]);
                        const ampm = hour >= 12 ? 'PM' : 'AM';
                        const displayHour = hour % 12 || 12;
                        return `${displayHour}:${parts[1]} ${ampm}`;
                    },

                    async fetchBranchServices(branchId) {
                        if (!branchId) return;
                        if (this.detectedBranchId === branchId) return;
                        this.servicesLoading = true;
                        try {
                            const response = await axios.get(`/customer/branch-services/${branchId}`);
                            if (response.data.categories) {
                                this.categories = response.data.categories;
                            }
                            if (response.data.turnaroundTypes) {
                                this.turnaroundTypes = response.data.turnaroundTypes;
                            }
                            if (response.data.extras) {
                                this.extras = response.data.extras;
                            }
                            if (response.data.pickupTimePeriods) {
                                this.branchPickupTimes = response.data.pickupTimePeriods;
                            }
                            this.detectedBranchId = branchId;
                            this.currentViewLevel = 0;
                            this.selectedMainCategory = null;
                            this.selectedSubcategory = null;
                        } catch(e) {
                            console.error('Failed to fetch branch services', e);
                        } finally {
                            this.servicesLoading = false;
                        }
                    },

                    selectedMainCategory: null,
                    selectedSubcategory: null,
                    currentSubcategories: [],
                    currentServices: [],
                    currentViewLevel: 0, // 0: Categories, 1: Subcategories, 2: Services
                    
                    init() {
                    },
                    
                    // Modal state
                    
                    selectedService: null,
                    serviceQty: 1,
                    selectedTurnaround: '',
                    
                    // New step 3 state
                    isCartExpanded: false,
                    isQtyModalOpen: false,
                    isGlobalCheckoutModalOpen: false,
                    globalTurnaround: '',
                    deliveryDate: '',
                    deliveryTimePeriodId: '',
                    globalExtras: {},                    routeLayer: null,
                    step: 1,
                    highestStepReached: 1,
                    minLimit: Number(minOrderLimit || 0),
                    expandedCategory: null,
                    cart: [],
                    
                    // Auth state
                    isAuthenticated: isLoggedIn,
                    authCustomer: user,
                    authTab: 'login',
                    authLoading: false,
                    authError: null,
                    loginData: { phone: '', password: '' },
                    registerData: { name: '', phone: '', email: '', password: '' },

                    // Delivery state
                    deliveryPref: 'deliver_to_address',
                    sameAsPickup: false,
                    selectedPaymentMethod: '{{ $paymentMethods->first()?->type ?? 'cash_on_delivery' }}',
                    
                    // Maps
                    pickupMap: null,
                    deliveryMap: null,
                    deliveryMapInitialized: false,
                    pickupLat: null,
                    pickupLng: null,
                    deliveryLat: null,
                    deliveryLng: null,
                    estimatedDeliveryFee: 0,
                    pickupDistance: 0,       // km from workshop to pickup
                    deliveryDistance: 0,     // km from workshop to delivery
                    pickupRouteLayer: null,  // OSRM route polyline on pickup map
                    deliveryRouteLayer: null,// OSRM route polyline on delivery map
                    deliveryRanges: deliveryRanges || [],
                    // Workshops array for multi-workshop support
                    workshopsArray: workshops || [],
                    nearestWorkshop: workshops && workshops.length > 0 ? workshops[0] : null,
                    baseLat: (workshops && workshops.length > 0 && !isNaN(parseFloat(workshops[0].latitude))) ? parseFloat(workshops[0].latitude) : 6.9322889,
                    baseLng: (workshops && workshops.length > 0 && !isNaN(parseFloat(workshops[0].longitude))) ? parseFloat(workshops[0].longitude) : 79.8567772,
                    isOutOfRange: false,

                    initData() {
                        // Check for cart passed from Homepage checkout
                        const savedHomepageCart = localStorage.getItem('customer_booking_cart');
                        if (savedHomepageCart) {
                            try {
                                const parsedCart = JSON.parse(savedHomepageCart);
                                if (Array.isArray(parsedCart) && parsedCart.length > 0) {
                                    this.cart = parsedCart;
                                    localStorage.removeItem('customer_booking_cart');
                                    // Auto-advance step: if not logged in -> step 2 (Account), if logged in -> step 3 (Pickup)
                                    if (!this.isAuthenticated) {
                                        this.step = 2;
                                        this.highestStepReached = 2;
                                    } else {
                                        this.step = 3;
                                        this.highestStepReached = 3;
                                    }
                                }
                            } catch(e) {
                                console.error('Error parsing customer_booking_cart from localStorage', e);
                            }
                        }

                        // Check if we are starting on step 3
                        if (this.step === 3) {
                            setTimeout(() => {
                                if (this.pickupMap) {
                                    this.pickupMap.invalidateSize();
                                } else {
                                    this.initPickupMap();
                                }
                            }, 500);
                        }

                        this.$watch('step', value => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Initialize pickup map on step 3 if not already done
                            if (value === 3) {
                                setTimeout(() => {
                                    if (this.pickupMap) {
                                        this.pickupMap.invalidateSize();
                                    } else {
                                        this.initPickupMap();
                                    }
                                }, 500);
                            }
                            
                            // Initialize delivery map on step 4 if not same as pickup
                            if (value === 4 && !this.sameAsPickup && this.deliveryPref === 'deliver_to_address') {
                                setTimeout(() => { if (this.deliveryMap) this.deliveryMap.invalidateSize(); this.initDeliveryMap(); }, 500);
                            }
                        });

                        this.$watch('sameAsPickup', value => {
                            if (!value && this.step === 4) {
                                setTimeout(() => { if (this.deliveryMap) this.deliveryMap.invalidateSize(); this.initDeliveryMap(); }, 500);
                            }
                            this.calculateDeliveryFee();
                        });
                        
                        this.$watch('deliveryPref', () => this.calculateDeliveryFee());
                    },

                    async goToStep(n) {
                        if (n > this.highestStepReached + 1) return; // Must go in order initially

                        // Validation: Step 1 (Services) -> Must have items in cart to proceed
                        if (n > 1 && this.cart.length === 0) {
                            alert("Please select at least one service before proceeding.");
                            this.step = 1;
                            return;
                        }

                        // Validation: Must be authenticated to proceed to Step 3 (Pickup) or Step 4 (Delivery)
                        if (n >= 3 && !this.isAuthenticated) {
                            alert("Please login or register first to continue your booking.");
                            this.step = 2;
                            return;
                        }

                        // Validation logic before moving to step 4
                        if (n === 4) { // Going to Delivery & Pay (Step 4)
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                            const date = document.getElementById('pickup_date_input')?.value;
                            const time = document.getElementById('pickup_time_input')?.value;
                            const addr = document.getElementById('pickup_address')?.value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
                                return;
                            }
                        }

                        this.step = n;
                        if (n > this.highestStepReached) {
                            this.highestStepReached = n;
                        }
                    },

                    async submitLogin() {
                        this.authLoading = true;
                        this.authError = null;
                        try {
                            const response = await axios.post('{{ route("customer.ajax.login") }}', this.loginData);
                            if (response.data.success) {
                                this.isAuthenticated = true;
                                this.authCustomer = response.data.customer;
                                this.goToStep(3);
                            }
                        } catch (error) {
                            this.authError = error.response?.data?.message || 'Login failed. Please check your credentials.';
                        } finally {
                            this.authLoading = false;
                        }
                    },

                    async submitRegister() {
                        this.authLoading = true;
                        this.authError = null;
                        try {
                            const response = await axios.post('{{ route("customer.ajax.register") }}', this.registerData);
                            if (response.data.success) {
                                this.isAuthenticated = true;
                                this.authCustomer = response.data.customer;
                                this.goToStep(3);
                            }
                        } catch (error) {
                            this.authError = error.response?.data?.message || 'Registration failed. Please check the form.';
                        } finally {
                            this.authLoading = false;
                        }
                    },

                    workshopMarkers: {}, // { workshopId: { marker, mapType } }

                    addWorkshopMarkersToMap(map, mapKey) {
                        const self = this;
                        this.workshopsArray.forEach(ws => {
                            if (!ws.latitude || !ws.longitude) return;
                            const isNearest = self.nearestWorkshop && self.nearestWorkshop.id === ws.id;
                            const icon = self.makeWorkshopIcon(isNearest);
                            const marker = L.marker([parseFloat(ws.latitude), parseFloat(ws.longitude)], {icon, interactive: false}).addTo(map);
                            const key = mapKey + '_' + ws.id;
                            self.workshopMarkers[key] = { marker, wsId: ws.id, mapKey };
                        });
                    },

                    makeWorkshopIcon(isNearest) {
                        const color = isNearest ? '#2563eb' : '#94a3b8';
                        const opacity = isNearest ? '1' : '0.4';
                        return L.divIcon({
                            html: `<div style="transform:translate(-50%,-100%);opacity:${opacity};transition:all 0.3s;">
                                    <div style="width:32px;height:32px;background:${color};border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.25);">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22" fill="white" stroke="white"/></svg>
                                    </div>
                                    ${isNearest ? '<div style="width:8px;height:8px;background:'+color+';border-radius:50%;margin:2px auto 0;box-shadow:0 1px 4px rgba(0,0,0,0.2);"></div>' : '<div style="width:5px;height:5px;background:'+color+';border-radius:50%;margin:2px auto 0;opacity:0.4;"></div>'}
                                   </div>`,
                            className: 'bg-transparent',
                            iconSize: [1, 1],
                            iconAnchor: [0, 0]
                        });
                    },

                    updateWorkshopMarkerStyles() {
                        const self = this;
                        Object.values(this.workshopMarkers).forEach(({ marker, wsId }) => {
                            const isNearest = self.nearestWorkshop && self.nearestWorkshop.id == wsId;
                            marker.setIcon(self.makeWorkshopIcon(isNearest));
                        });
                    },

                    findNearestWorkshop(lat, lng) {
                        if (!this.workshopsArray || this.workshopsArray.length === 0) return null;
                        let nearest = null;
                        let minDist = Infinity;
                        this.workshopsArray.forEach(ws => {
                            if (ws.latitude && ws.longitude) {
                                const d = this.calculateDistance(parseFloat(ws.latitude), parseFloat(ws.longitude), lat, lng);
                                if (d < minDist) {
                                    minDist = d;
                                    nearest = ws;
                                }
                            }
                        });
                        return nearest;
                    },

                    initPickupMap() {
                        if (this.pickupMap) {
                            this.pickupMap.invalidateSize();
                            return;
                        }
                        const defaultCenter = [this.baseLat, this.baseLng];
                        this.pickupMap = L.map('pickup_map', { zoomControl: false }).setView(defaultCenter, 15);

                        // Beautiful tile - CartoDB Positron (clean & light)
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.pickupMap);

                        L.control.zoom({ position: 'bottomright' }).addTo(this.pickupMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.pickupMap, 'pickup');

                        const updatePickupCoords = () => {
                            const center = this.pickupMap.getCenter();
                            document.getElementById('pickup_lat').value = center.lat.toFixed(6);
                            document.getElementById('pickup_lng').value = center.lng.toFixed(6);
                            this.pickupLat = center.lat;
                            this.pickupLng = center.lng;
                            // Update nearest workshop dynamically
                            const nearest = this.findNearestWorkshop(center.lat, center.lng);
                            if (nearest && (!this.nearestWorkshop || this.nearestWorkshop.id != nearest.id)) {
                                this.nearestWorkshop = nearest;
                                this.baseLat = parseFloat(nearest.latitude);
                                this.baseLng = parseFloat(nearest.longitude);
                                this.updateWorkshopMarkerStyles();
                            }
                            this.calculateDeliveryFee();
                        };

                        this.pickupMap.on('move', updatePickupCoords);
                        this.pickupMap.on('moveend', updatePickupCoords);
                        this.pickupMap.on('moveend', () => { if (this.baseLat && this.baseLng) this.drawRoute(this.pickupMap, this.baseLat, this.baseLng, this.pickupLat, this.pickupLng); });
                        updatePickupCoords();
                    },

                    initDeliveryMap() {
                        if (this.deliveryMapInitialized) {
                            this.deliveryMap.invalidateSize();
                            return;
                        }
                        const center = this.pickupMap ? this.pickupMap.getCenter() : [this.baseLat, this.baseLng];
                        this.deliveryMap = L.map('delivery_map', { zoomControl: false }).setView(center, 15);

                        // Same beautiful tile
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(this.deliveryMap);

                        L.control.zoom({ position: 'bottomright' }).addTo(this.deliveryMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.deliveryMap, 'delivery');

                        const updateDeliveryCoords = () => {
                            const center = this.deliveryMap.getCenter();
                            document.getElementById('delivery_lat').value = center.lat.toFixed(6);
                            document.getElementById('delivery_lng').value = center.lng.toFixed(6);
                            this.deliveryLat = center.lat;
                            this.deliveryLng = center.lng;
                            this.calculateDeliveryFee();
                            if (this.baseLat && this.baseLng) { this.drawRoute(this.deliveryMap, this.baseLat, this.baseLng, this.deliveryLat, this.deliveryLng); }
                        };

                        this.deliveryMap.on('move', updateDeliveryCoords);
                        this.deliveryMap.on('moveend', () => {
                            updateDeliveryCoords();
                            if (this.deliveryLat && this.deliveryLng) {
                                this.drawRoute(this.deliveryMap, this.baseLat, this.baseLng, this.deliveryLat, this.deliveryLng, 'delivery');
                            }
                        });
                        updateDeliveryCoords();
                        this.deliveryMapInitialized = true;
                    },

                    drawRoute(map, startLat, startLng, endLat, endLng) {
                        if (!map.routeLayer) {
                            map.routeLayer = L.layerGroup().addTo(map);
                        }
                        map.routeLayer.clearLayers();
                        if(!startLat || !startLng || !endLat || !endLng) return;
                        
                        axios.get(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`)
                            .then(res => {
                                if(res.data && res.data.routes && res.data.routes.length > 0) {
                                    const route = res.data.routes[0].geometry;
                                    L.geoJSON(route, {
                                        style: { color: '#2563eb', weight: 4, opacity: 0.8, dashArray: '5, 10' }
                                    }).addTo(map.routeLayer);
                                    
                                    // Fit bounds (Removed to prevent infinite map dragging loop)
                                    // const bounds = L.latLngBounds([
                                    //     [startLat, startLng],
                                    //     [endLat, endLng]
                                    // ]);
                                    // map.fitBounds(bounds, { padding: [50, 50] });
                                }
                            }).catch(err => console.log('OSRM Error', err));
                    },


                    findMe(type) {
                        if ("geolocation" in navigator) {
                            navigator.geolocation.getCurrentPosition(position => {
                                if (type === 'pickup' && this.pickupMap) {
                                    this.pickupMap.setView([position.coords.latitude, position.coords.longitude], 16);
                                }
                            }, error => {
                                alert("Could not get your location. Please check browser permissions.");
                            });
                        }
                    },
                    
                    calculateDistance(lat1, lon1, lat2, lon2) {
                        const R = 6371; // km
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLon = (lon2 - lon1) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        return R * c;
                    },
                    
                    calculateDeliveryFee() {
                        let distanceToPickup = 0;
                        let distanceToDelivery = 0;
                        let pickupFee = 0;
                        let deliveryFee = 0;
                        let isPickupOutOfRange = false;
                        let isDeliveryOutOfRange = false;
                        
                        // Default to 15km if no delivery ranges are set
                        let maxAllowedDistance = this.deliveryRanges.length > 0 ? 
                            Math.max(...this.deliveryRanges.map(r => Number(r.max_distance_km))) : 15;
                        
                        const getFeeForDistance = (distance) => {
                            if (this.deliveryRanges.length === 0) {
                                let rawFee = distance * 100;
                                return rawFee > 0 && rawFee < 300 ? 300 : Math.round(rawFee);
                            }
                            for (let range of this.deliveryRanges) {
                                if (distance >= Number(range.min_distance_km) && distance <= Number(range.max_distance_km)) {
                                    return Number(range.cost);
                                }
                            }
                            return -1; // Out of all ranges
                        };
                        
                        // Use nearest workshop as base
                        const wsLat = this.baseLat;
                        const wsLng = this.baseLng;
                        
                        // Distance to pickup
                        if (this.pickupLat && this.pickupLng) {
                            distanceToPickup = this.calculateDistance(wsLat, wsLng, this.pickupLat, this.pickupLng);
                            this.pickupDistance = Math.round(distanceToPickup * 10) / 10; // 1 decimal
                            if (distanceToPickup > maxAllowedDistance) {
                                isPickupOutOfRange = true;
                                pickupFee = -1;
                            } else {
                                pickupFee = getFeeForDistance(distanceToPickup);
                            }
                        } else {
                            this.pickupDistance = 0;
                        }
                        
                        // Distance to delivery
                        if (this.deliveryPref === 'deliver_to_address') {
                            if (this.sameAsPickup && this.pickupLat && this.pickupLng) {
                                distanceToDelivery = distanceToPickup;
                                this.deliveryDistance = this.pickupDistance;
                                isDeliveryOutOfRange = isPickupOutOfRange;
                                deliveryFee = pickupFee;
                            } else if (!this.sameAsPickup && this.deliveryLat && this.deliveryLng) {
                                distanceToDelivery = this.calculateDistance(wsLat, wsLng, this.deliveryLat, this.deliveryLng);
                                this.deliveryDistance = Math.round(distanceToDelivery * 10) / 10;
                                if (distanceToDelivery > maxAllowedDistance) {
                                    isDeliveryOutOfRange = true;
                                    deliveryFee = -1;
                                } else {
                                    deliveryFee = getFeeForDistance(distanceToDelivery);
                                }
                            } else {
                                this.deliveryDistance = 0;
                            }
                        }
                        
                        this.isOutOfRange = isPickupOutOfRange || (this.deliveryPref === 'deliver_to_address' && !this.sameAsPickup && isDeliveryOutOfRange);
                        const totalFee = (pickupFee > 0 ? pickupFee : 0) + (deliveryFee > 0 ? deliveryFee : 0);
                        this.estimatedDeliveryFee = Math.round(totalFee);
                    },

                    // Cart methods
                    // New step 3 state
                    isCartExpanded: false,
                    isQtyModalOpen: false,
                    isGlobalCheckoutModalOpen: false,
                    globalTurnaround: '',
                    globalExtras: {},

                    selectLevel1(cat) {
                        this.selectedMainCategory = cat;
                        let childrenArray = cat.children ? Object.values(cat.children) : [];
                        
                        if (childrenArray.length > 0) {
                            this.currentSubcategories = childrenArray;
                            this.currentViewLevel = 1;
                        } else {
                            this.currentSubcategories = [];
                            this.selectedSubcategory = null;
                            this.currentServices = cat.services ? Object.values(cat.services) : [];
                            this.currentViewLevel = 2;
                        }
                    },
                    
                    selectLevel2(sub) {
                        this.selectedSubcategory = sub;
                        this.currentServices = sub.services ? Object.values(sub.services) : [];
                        this.currentViewLevel = 2;
                    },
                    
                    goBackLevel() {
                        if (this.currentViewLevel === 2) {
                            if (this.currentSubcategories.length > 0) {
                                this.currentViewLevel = 1;
                                this.selectedSubcategory = null;
                            } else {
                                this.currentViewLevel = 0;
                                this.selectedMainCategory = null;
                            }
                        } else if (this.currentViewLevel === 1) {
                            this.currentViewLevel = 0;
                            this.selectedMainCategory = null;
                        }
                    },
                    openQtyModal(service) {
                        this.selectedService = service;
                        this.serviceQty = 1;
                        this.isQtyModalOpen = true;
                    },
                    confirmAddToCart() {
                        if (!this.selectedService) return;
                        
                        let cartItem = {
                            id: this.selectedService.id,
                            service_type_id: this.selectedService.id,
                            name: this.selectedService.name,
                            base_price: parseFloat(this.selectedService.base_price),
                            price_type: this.selectedService.price_type,
                            qty: this.serviceQty,
                            // Will be filled at checkout
                            turnaround_type_id: null,
                            selected_extras: [],
                            cart_key: Date.now() + Math.random()
                        };
                        
                        let existing = this.cart.find(i => i.id === cartItem.id);
                        if (existing) {
                            existing.qty += cartItem.qty;
                        } else {
                            this.cart.push(cartItem);
                        }
                        
                        this.isQtyModalOpen = false;
                    },
                    updateCartQty(index, amount) {
                        if (this.cart[index].qty + amount > 0) {
                            this.cart[index].qty += amount;
                        }
                    },
                    removeFromCartByIndex(index) {
                        this.cart.splice(index, 1);
                        if (this.cart.length === 0) {
                            this.isCartExpanded = false;
                        }
                    },
                    get baseCartTotal() {
                        return this.cart.reduce((total, item) => total + (parseFloat(item.base_price) * item.qty), 0);
                    },
                    openGlobalCheckoutModal() {
                        let defaultTt = this.turnaroundTypes.find(t => t.is_default);
                        if (!this.globalTurnaround && defaultTt) {
                            this.globalTurnaround = defaultTt.id;
                        }
                        this.isGlobalCheckoutModalOpen = true;
                    },
                    get calculatedGrandTotal() {
                        let totalQty = this.cart.reduce((sum, item) => sum + item.qty, 0);
                        let baseTotal = this.baseCartTotal;
                        
                        let ttMarkupTotal = 0;
                        if (this.globalTurnaround) {
                            let tt = this.turnaroundTypes.find(t => t.id == this.globalTurnaround);
                            if (tt && tt.name !== 'Standard') {
                                this.cart.forEach(item => {
                                    let base = parseFloat(item.base_price);
                                    let itemMarkup = tt.markup_type === 'percentage' ? (base * parseFloat(tt.markup_value) / 100) : parseFloat(tt.markup_value);
                                    ttMarkupTotal += (itemMarkup * item.qty);
                                });
                            }
                        }
                        
                        let extrasMarkupTotal = 0;
                        for (let eId in this.globalExtras) {
                            let qty = this.globalExtras[eId];
                            if (qty > 0) {
                                let ex = this.extras.find(e => e.id == eId);
                                if (ex) {
                                    extrasMarkupTotal += (parseFloat(ex.price) * qty);
                                }
                            }
                        }
                        
                        return baseTotal + ttMarkupTotal + extrasMarkupTotal;
                    },
                    get totalPrice() {
                        return this.calculatedGrandTotal + (this.estimatedDeliveryFee > 0 ? this.estimatedDeliveryFee : 0);
                    },
                    get formattedCart() {
                        let result = '';
                        this.cart.forEach(item => {
                            result += `${item.name} x${item.qty} (LKR ${parseFloat(item.base_price).toFixed(2)}), `;
                        });
                        return result.slice(0, -2);
                    },
                    proceedToDelivery() {
                        // Intercept any ServiceType added to cart that is actually an Extra
                        let cleanCart = [];
                        this.cart.forEach(item => {
                            if (item.name.toLowerCase().startsWith('extra:')) {
                                let extraName = item.name.substring(6).trim();
                                let extraModel = this.extras.find(e => e.name.toLowerCase() === extraName.toLowerCase());
                                if (extraModel) {
                                    if (!this.globalExtras[extraModel.id]) this.globalExtras[extraModel.id] = 0;
                                    this.globalExtras[extraModel.id] += parseInt(item.qty);
                                } else {
                                    cleanCart.push(item);
                                }
                            } else {
                                cleanCart.push(item);
                            }
                        });
                        this.cart = cleanCart;

                        let selectedExtrasList = [];
                        for (let eId in this.globalExtras) {
                            if (this.globalExtras[eId] > 0) {
                                selectedExtrasList.push({ id: parseInt(eId), qty: this.globalExtras[eId] });
                            }
                        }

                        // Apply global turnaround to every cart item, but extras only to the first item
                        this.cart.forEach((item, index) => {
                            item.turnaround_type_id = this.globalTurnaround;
                            if (index === 0) {
                                item.selected_extras = selectedExtrasList;
                            } else {
                                item.selected_extras = [];
                            }
                        });
                        
                        this.isGlobalCheckoutModalOpen = false;
                        if (!this.isAuthenticated) {
                            this.goToStep(2);
                        } else {
                            this.goToStep(3);
                        }
                    },
                }
            }
        </script>
    </x-slot>
</x-customer-layout>


