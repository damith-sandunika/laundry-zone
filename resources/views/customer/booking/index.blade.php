<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Book a Pickup</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Schedule a laundry pickup right to your doorstep.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        .map-container { height: 350px; width: 100%; border-radius: 1rem; z-index: 10; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); }
        .step-active { border-color: #2563eb; background-color: #eff6ff; color: #1e3a8a; }
        .step-inactive { border-color: #e2e8f0; background-color: #f8fafc; color: #94a3b8; }
        .step-completed { border-color: #10b981; background-color: #ecfdf5; color: #047857; }
        
        .custom-radio:checked + div { border-color: #2563eb; background-color: #eff6ff; }
        .custom-radio:checked + div .radio-dot { background-color: #2563eb; transform: scale(1); }
        .radio-dot { transform: scale(0); transition: transform 0.2s ease-in-out; }
    </style>

    <div x-data="bookingWizard({{ Js::from($categories) }}, {{ auth('customer')->check() ? 'true' : 'false' }}, {{ Js::from(auth('customer')->user()) }})" x-init="initData()">
        
        <!-- Wizard Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative max-w-3xl mx-auto">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-200 rounded-full z-0"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-blue-600 rounded-full z-0 transition-all duration-500" :style="`width: ${((step - 1) / 3) * 100}%`"></div>
                
                <template x-for="i in 4" :key="i">
                    <div class="relative z-10 flex flex-col items-center bg-transparent">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-base border-4 transition-all duration-300"
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

        <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm" class="mt-16 lg:w-3/4 mx-auto" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="item_description" x-model="formattedCart">
            <input type="hidden" name="cart_json" x-bind:value="JSON.stringify(cart)">
            
            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 mb-6">
                    <ul class="list-disc list-inside text-sm font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- STEP 1: Services Selection -->
            <div x-show="step === 1" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
                        <span class="bg-blue-50 text-blue-600 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-shirt"></i></span>
                        Select Services
                    </h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Choose the items you want us to clean.</p>
                    
                    <div class="space-y-3">
                        <template x-for="category in categories" :key="category.id">
                            <div class="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden transition-all duration-200" :class="expandedCategory === category.id ? 'ring-2 ring-blue-100' : ''">
                                <div @click="expandedCategory = expandedCategory === category.id ? null : category.id" class="px-5 py-4 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm text-blue-600 flex items-center justify-center border border-slate-100">
                                            <i class="fa-solid fa-layer-group" x-show="category.name !== 'Wash & Fold' && category.name !== 'Dry Cleaning' && category.name !== 'Ironing'"></i>
                                            <i class="fa-solid fa-soap" x-show="category.name === 'Wash & Fold'"></i>
                                            <i class="fa-solid fa-shirt" x-show="category.name === 'Dry Cleaning'"></i>
                                            <i class="fa-solid fa-fire-burner" x-show="category.name === 'Ironing'"></i>
                                        </div>
                                        <span class="font-bold text-base text-slate-800" x-text="category.name"></span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="expandedCategory === category.id ? 'rotate-180' : ''"></i>
                                </div>
                                
                                <div x-show="expandedCategory === category.id" x-collapse>
                                    <div class="border-t border-slate-200 divide-y divide-slate-100 bg-white">
                                        <template x-for="service in category.services" :key="service.id">
                                            <div class="flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition-colors">
                                                <div>
                                                    <p class="font-bold text-slate-700" x-text="service.name"></p>
                                                    <p class="text-sm text-blue-600 font-bold mt-0.5">LKR <span x-text="service.base_price"></span></p>
                                                </div>
                                                <div class="flex items-center gap-4 bg-slate-100 rounded-xl p-1.5 border border-slate-200 shadow-sm">
                                                    <button type="button" @click="removeFromCart(service)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-slate-600 hover:text-red-500 shadow-sm transition-colors border border-slate-200">
                                                        <i class="fa-solid fa-minus text-xs"></i>
                                                    </button>
                                                    <span class="font-black w-6 text-center text-slate-800" x-text="getQuantity(service.id)"></span>
                                                    <button type="button" @click="addToCart(service)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors border border-blue-700">
                                                        <i class="fa-solid fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="category.services.length === 0" class="text-sm font-bold text-slate-400 p-5 text-center">
                                            No services available in this category.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Special Instructions (Optional)</label>
                        <textarea name="notes" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold placeholder-slate-400" placeholder="E.g., Please fold shirts carefully..."></textarea>
                    </div>
                </div>
                
                <!-- Bottom Bar -->
                <div class="flex justify-between items-center mt-6">
                    <div class="text-lg font-black text-slate-800">
                        Total: <span class="text-blue-600">LKR <span x-text="totalPrice"></span></span>
                    </div>
                    <button type="button" @click="goToStep(2)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Authentication -->
            <div x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    
                    <!-- If already logged in, show account info instead of form -->
                    <template x-if="isAuthenticated">
                        <div class="text-center py-8">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4 border-4 border-blue-100">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <h3 class="text-2xl font-black text-slate-800 mb-2">Welcome back, <span x-text="authCustomer.name"></span>!</h3>
                            <p class="text-slate-500 font-medium">You are currently logged in with <span x-text="authCustomer.phone"></span>.</p>
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
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <input type="password" x-model="loginData.password" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="••••••••">
                                    </div>
                                    <button type="button" @click="submitLogin()" :disabled="authLoading" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-black mt-2 transition-all flex justify-center items-center gap-2">
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
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                                        <input type="password" x-model="registerData.password" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500" placeholder="••••••••">
                                    </div>
                                    <button type="button" @click="submitRegister()" :disabled="authLoading" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3.5 rounded-xl font-black mt-2 transition-all flex justify-center items-center gap-2">
                                        <span x-show="!authLoading">Create Account & Continue</span>
                                        <i x-show="authLoading" class="fa-solid fa-spinner fa-spin"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="flex justify-between items-center mt-6">
                    <button type="button" @click="step = 1" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <!-- Only show next if authenticated -->
                    <button type="button" x-show="isAuthenticated" @click="goToStep(3)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Pickup Details -->
            <div x-show="step === 3" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
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
                                @foreach($pickupTimePeriods as $period)
                                    <option value="{{ $period->id }}">{{ $period->label }} ({{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-bold text-slate-700">Pin pickup location on map</label>
                            <button type="button" @click="findMe('pickup')" class="bg-slate-100 hover:bg-slate-200 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                                <i class="fa-solid fa-location-crosshairs"></i> Find Me
                            </button>
                        </div>
                        
                        <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                <div class="text-4xl text-blue-600 drop-shadow-xl pb-8"><i class="fa-solid fa-location-dot"></i></div>
                            </div>
                            <div id="pickup_map" class="map-container"></div>
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
                    <button type="button" @click="step = 2" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="button" @click="goToStep(4)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Delivery & Payment -->
            <div x-show="step === 4" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="space-y-6">
                    <!-- Delivery Section -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
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
                                        <div class="font-black text-sm text-slate-800">Deliver to me</div>
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
                                        <div class="font-black text-sm text-slate-800">I will pick up</div>
                                        <div class="text-[11px] font-bold text-slate-500 uppercase mt-0.5">Collect from branch</div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <div x-show="deliveryPref === 'deliver_to_address'" x-collapse>
                            <label class="flex items-center gap-3 cursor-pointer p-4 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                                <input type="checkbox" name="same_as_pickup" value="1" x-model="sameAsPickup" class="w-5 h-5 text-blue-600 rounded border-blue-200 focus:ring-blue-500 bg-white">
                                <span class="font-black text-sm text-blue-800">Delivery address is same as pickup</span>
                            </label>

                            <div x-show="!sameAsPickup" x-collapse>
                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Pin delivery location on map</label>
                                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                            <div class="text-4xl text-blue-600 drop-shadow-xl pb-8"><i class="fa-solid fa-location-dot"></i></div>
                                        </div>
                                        <div id="delivery_map" class="map-container"></div>
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

                    <!-- Payment Section -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
                            <span class="bg-indigo-50 text-indigo-500 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-credit-card"></i></span>
                            Payment
                        </h3>
                        <p class="text-sm text-slate-500 mb-6 font-medium">How would you like to pay?</p>

                        <div class="grid sm:grid-cols-3 gap-3 mb-6">
                            @foreach($paymentMethods as $method)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="payment_method" value="{{ $method->type }}" class="custom-radio sr-only" x-model="selectedPaymentMethod">
                                    <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 text-center transition-all">
                                        <div class="text-2xl mb-2 text-slate-600">
                                            @if($method->type == 'cash_on_delivery') <i class="fa-solid fa-money-bill-wave"></i>
                                            @elseif($method->type == 'card_transfer') <i class="fa-solid fa-credit-card"></i>
                                            @elseif($method->type == 'bank_transfer') <i class="fa-solid fa-building-columns"></i>
                                            @endif
                                        </div>
                                        <div class="font-black text-sm text-slate-800">{{ $method->name }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <!-- Payment Timing -->
                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl">
                            <label class="block text-sm font-bold text-slate-700 mb-3">When will you pay?</label>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_timing" value="at_pickup" checked class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <span class="text-sm font-bold text-slate-700">At Pickup</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="payment_timing" value="at_delivery" class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <span class="text-sm font-bold text-slate-700">At Delivery</span>
                                </label>
                            </div>
                        </div>

                        <!-- Receipt Upload for Bank Transfer -->
                        <div x-show="selectedPaymentMethod === 'bank_transfer'" x-collapse class="mt-4 pt-4 border-t border-slate-100">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Transfer Receipt (Optional)</label>
                            <input type="file" name="receipt_image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-all cursor-pointer">
                            <p class="text-[11px] text-slate-400 mt-2 font-semibold">You can also provide the receipt to our rider during pickup.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button type="button" @click="step = 3" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-black transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2 text-lg">
                        Confirm Booking <i class="fa-solid fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function bookingWizard(categories, isAuth, customer) {
                return {
                    step: 1,
                    categories: categories,
                    expandedCategory: null,
                    cart: [],
                    
                    // Auth state
                    isAuthenticated: isAuth,
                    authCustomer: customer,
                    authTab: 'login',
                    authLoading: false,
                    authError: null,
                    loginData: { phone: '', password: '' },
                    registerData: { name: '', phone: '', email: '', password: '' },

                    // Delivery state
                    deliveryPref: 'deliver_to_address',
                    sameAsPickup: true,
                    selectedPaymentMethod: '{{ $paymentMethods->first()?->type ?? 'cash_on_delivery' }}',
                    
                    // Maps
                    pickupMap: null,
                    deliveryMap: null,
                    deliveryMapInitialized: false,

                    initData() {
                        this.$watch('step', value => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Initialize map on step 3 if not already done
                            if (value === 3) {
                                setTimeout(() => this.initPickupMap(), 100);
                            }
                            
                            // Initialize delivery map on step 4 if not same as pickup
                            if (value === 4 && !this.sameAsPickup && this.deliveryPref === 'deliver_to_address') {
                                setTimeout(() => this.initDeliveryMap(), 100);
                            }
                        });

                        this.$watch('sameAsPickup', value => {
                            if (!value && this.step === 4) {
                                setTimeout(() => this.initDeliveryMap(), 100);
                            }
                        });
                    },

                    goToStep(n) {
                        // Validation logic before moving to next step
                        if (n === 2 && this.cart.length === 0) {
                            alert("Please select at least one service before proceeding.");
                            return;
                        }
                        
                        if (n === 4) {
                            const date = document.getElementById('pickup_date_input').value;
                            const time = document.getElementById('pickup_time_input').value;
                            const addr = document.getElementById('pickup_address').value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
                                return;
                            }
                        }

                        this.step = n;
                        
                        // If going to step 2 but already authenticated, skip to step 3
                        if (n === 2 && this.isAuthenticated) {
                            this.step = 3;
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

                    initPickupMap() {
                        if (this.pickupMap) {
                            this.pickupMap.invalidateSize();
                            return;
                        }
                        const defaultCenter = [6.9271, 79.8612];
                        this.pickupMap = L.map('pickup_map', { zoomControl: false }).setView(defaultCenter, 13);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.pickupMap);
                        L.control.zoom({ position: 'bottomright' }).addTo(this.pickupMap);

                        const updateCoords = () => {
                            const center = this.pickupMap.getCenter();
                            document.getElementById('pickup_lat').value = center.lat.toFixed(6);
                            document.getElementById('pickup_lng').value = center.lng.toFixed(6);
                        };

                        this.pickupMap.on('move', updateCoords);
                        this.pickupMap.on('moveend', updateCoords);
                        updateCoords();
                    },

                    initDeliveryMap() {
                        if (this.deliveryMapInitialized) {
                            this.deliveryMap.invalidateSize();
                            return;
                        }
                        const center = this.pickupMap ? this.pickupMap.getCenter() : [6.9271, 79.8612];
                        this.deliveryMap = L.map('delivery_map', { zoomControl: false }).setView(center, 13);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(this.deliveryMap);
                        L.control.zoom({ position: 'bottomright' }).addTo(this.deliveryMap);

                        const updateCoords = () => {
                            const center = this.deliveryMap.getCenter();
                            document.getElementById('delivery_lat').value = center.lat.toFixed(6);
                            document.getElementById('delivery_lng').value = center.lng.toFixed(6);
                        };

                        this.deliveryMap.on('move', updateCoords);
                        this.deliveryMap.on('moveend', updateCoords);
                        updateCoords();
                        this.deliveryMapInitialized = true;
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

                    // Cart methods
                    getQuantity(serviceId) {
                        const item = this.cart.find(i => i.service.id === serviceId);
                        return item ? item.quantity : 0;
                    },
                    addToCart(service) {
                        const item = this.cart.find(i => i.service.id === service.id);
                        if (item) item.quantity++;
                        else this.cart.push({ service: service, quantity: 1 });
                    },
                    removeFromCart(service) {
                        const index = this.cart.findIndex(i => i.service.id === service.id);
                        if (index !== -1) {
                            if (this.cart[index].quantity > 1) this.cart[index].quantity--;
                            else this.cart.splice(index, 1);
                        }
                    },
                    get totalPrice() {
                        return this.cart.reduce((total, item) => total + (item.service.base_price * item.quantity), 0);
                    },
                    get formattedCart() {
                        if (this.cart.length === 0) return '';
                        let result = "--- SELECTED SERVICES ---\n";
                        this.cart.forEach(item => {
                            result += `${item.service.name} x ${item.quantity} (LKR ${item.service.base_price * item.quantity})\n`;
                        });
                        result += `\nEstimated Total: LKR ${this.totalPrice}`;
                        return result;
                    }
                }
            }
        </script>
    </x-slot>
</x-customer-layout>
