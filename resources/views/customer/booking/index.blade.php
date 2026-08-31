<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <div>
                    <h2 class="font-black text-xl sm:text-2xl text-slate-800 tracking-tight">Book a Pickup</h2>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1 hidden sm:block">Schedule a laundry pickup right to your doorstep.</p>
                </div>
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

    <div x-data="bookingWizard({{ Js::from($categories) }}, {{ auth('customer')->check() ? 'true' : 'false' }}, {{ Js::from(auth('customer')->user()) }}, {{ $minimumOrderLimit }}, {{ Js::from($deliveryRanges) }}, {{ Js::from($workshops) }})" x-init="initData()">
        
        <!-- Wizard Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative max-w-3xl mx-auto">
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-200 rounded-full z-0"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1.5 bg-blue-600 rounded-full z-0 transition-all duration-500" :style="`width: ${((step - 1) / 3) * 100}%`"></div>
                
                <template x-for="i in 4" :key="i">
                    <div class="relative z-10 flex flex-col items-center bg-transparent">
                        <div @click="goToStep(i)" class="w-12 h-12 rounded-full flex items-center justify-center font-black text-base border-4 transition-all duration-300 cursor-pointer"
                             :class="step === i ? 'step-active scale-110 shadow-md' : (step > i ? 'step-completed' : 'step-inactive bg-white')">
                            <i x-show="step > i" class="fa-solid fa-check"></i>
                            <span x-show="step <= i" x-text="i"></span>
                        </div>
                        <span class="text-[11px] font-extrabold mt-2 uppercase tracking-wider absolute top-14 w-32 text-center" 
                              :class="step === i ? 'text-blue-700' : 'text-slate-400'"
                              x-text="['Account', 'Pickup', 'Services', 'Delivery & Pay'][i-1]"></span>
                    </div>
                </template>
            </div>
        </div>

        <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm" class="mt-16 lg:w-3/4 mx-auto" enctype="multipart/form-data">
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

            <!-- STEP 3: Services Selection -->
            <div x-show="step === 3" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
                        <span class="bg-blue-50 text-blue-600 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-shirt"></i></span>
                        Select Services
                    </h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Choose the items you want us to clean.</p>
                    
                    <!-- Category Cards Horizontal Scroll Carousel (2 rows) -->
                    <div class="relative group mb-6" x-data="{
                        scrollCats(direction) {
                            const container = this.$refs.catScrollContainer;
                            const scrollAmount = container.clientWidth * 0.8;
                            container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
                        }
                    }">
                        <!-- Left Arrow -->
                        <button type="button" @click="scrollCats(-1)" class="absolute left-[-10px] top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white shadow-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        
                        <!-- Right Arrow -->
                        <button type="button" @click="scrollCats(1)" class="absolute right-[-10px] top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white shadow-lg border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>

                        <div x-ref="catScrollContainer" class="overflow-x-auto scrollbar-none custom-scrollbar-hide rounded-2xl pb-2">
                            <div class="grid grid-rows-2 grid-flow-col gap-3 sm:gap-4 w-max">
                                <template x-for="(category, index) in categories" :key="category.id">
                                    <div @click="expandedCategory = expandedCategory === category.id ? null : category.id"
                                         class="relative cursor-pointer rounded-2xl overflow-hidden shadow-sm transition-all aspect-[4/3] w-[140px] sm:w-[160px] group"
                                         :class="expandedCategory === category.id ? 'ring-4 ring-blue-500 shadow-md scale-95' : 'hover:shadow-md'">
                                        
                                        <!-- Category Image -->
                                        <template x-if="category.image_path">
                                            <img :src="'/' + category.image_path" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" alt="Category">
                                        </template>
                                        <template x-if="!category.image_path">
                                            <div class="w-full h-full bg-slate-200 flex items-center justify-center transition-transform duration-500 group-hover:scale-110">
                                                <i class="fa-solid fa-image text-3xl text-slate-400"></i>
                                            </div>
                                        </template>
                                        
                                        <!-- Overlay and Title -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent flex flex-col justify-end p-2 sm:p-3 text-center">
                                            <h4 class="text-white text-[12px] sm:text-sm font-black drop-shadow-md leading-tight" x-text="category.name"></h4>
                                        </div>
                                        
                                        <!-- Selected Checkmark -->
                                        <div x-show="expandedCategory === category.id" class="absolute top-2 right-2 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-sm">
                                            <i class="fa-solid fa-check text-[10px]"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Services List for Selected Category (Shown Below Carousel) -->
                    <div x-show="expandedCategory" x-collapse class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
                        <template x-for="category in categories" :key="'svc-'+category.id">
                            <div x-show="expandedCategory === category.id">
                                <div class="p-4 sm:p-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                    <h4 class="font-black text-slate-800 text-lg flex items-center gap-2">
                                        <i class="fa-solid fa-list-check text-blue-600"></i>
                                        <span x-text="category.name + ' Services'"></span>
                                    </h4>
                                    <button type="button" @click="expandedCategory = null" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-slate-300 hover:text-slate-700 transition-colors">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                                <div class="divide-y divide-slate-100 max-h-[400px] overflow-y-auto custom-scrollbar">
                                    <template x-for="service in category.services" :key="service.id">
                                        <div class="flex items-center justify-between px-4 sm:px-6 py-2.5 sm:py-3 hover:bg-slate-50 transition-colors gap-2">
                                            <div class="flex-1 min-w-0 pr-2">
                                                <p class="font-bold text-slate-800 text-sm truncate" x-text="service.name"></p>
                                                <p class="text-xs text-blue-600 font-extrabold">LKR <span x-text="service.base_price"></span></p>
                                            </div>
                                            <div class="flex items-center gap-2 bg-slate-100 rounded-lg p-1 border border-slate-200 shadow-sm shrink-0">
                                                <button type="button" @click="removeFromCart(service)" class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded bg-white text-slate-600 hover:text-red-500 shadow-sm transition-colors border border-slate-200">
                                                    <i class="fa-solid fa-minus text-[10px]"></i>
                                                </button>
                                                <span class="font-black w-4 sm:w-5 text-center text-slate-800 text-xs sm:text-sm" x-text="getQuantity(service.id)"></span>
                                                <button type="button" @click="addToCart(service)" class="w-6 h-6 sm:w-7 sm:h-7 flex items-center justify-center rounded bg-blue-600 text-white hover:bg-blue-700 shadow-sm transition-colors border border-blue-700">
                                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="category.services.length === 0" class="text-sm font-bold text-slate-400 p-8 text-center bg-slate-50">
                                        <i class="fa-solid fa-box-open text-3xl mb-3 text-slate-300 block"></i>
                                        No services available in this category.
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
                
                <div class="flex justify-between items-center mt-6">
                    <div class="text-lg font-black text-slate-800">
                        Total: <span class="text-blue-600">LKR <span x-text="totalPrice"></span></span>
                        <div x-show="totalPrice < minLimit" class="text-[10px] text-red-500 font-bold mt-1">Min. order: LKR <span x-text="minLimit"></span></div>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button type="button" @click="goToStep(2)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="goToStep(4)" :disabled="totalPrice < minLimit || cart.length === 0" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
                            Next Step <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 1: Authentication -->
            <div x-show="step === 1" x-transition.opacity.duration.300ms>
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
                    <!-- Only show next if authenticated -->
                    <button type="button" x-show="isAuthenticated" @click="goToStep(2)" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2 mt-4 ml-auto">
                        Next Step <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Pickup Details -->
            <div x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms>
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
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none flex flex-col items-center justify-end pb-4">
                                <div x-show="!isOutOfRange" class="bg-slate-900 text-white text-xs font-black px-3 py-1.5 rounded-lg mb-1 shadow-lg whitespace-nowrap border border-slate-700 animate-fade-in flex items-center gap-1.5">
                                    <i class="fa-solid fa-motorcycle text-amber-400"></i> Fee: LKR <span x-text="estimatedDeliveryFee"></span>
                                </div>
                                <div x-show="isOutOfRange" x-cloak class="bg-red-600 text-white text-xs font-black px-3 py-1.5 rounded-lg mb-1 shadow-lg whitespace-nowrap border border-red-700 animate-pulse flex items-center gap-1.5">
                                    <i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of range
                                </div>
                                <div class="text-4xl text-blue-600 drop-shadow-xl" :class="isOutOfRange ? 'text-red-500' : 'text-blue-600'"><i class="fa-solid fa-location-dot"></i></div>
                            </div>
                            <div id="pickup_map" class="map-container"></div>
                        </div>
                        <input type="hidden" name="latitude" id="pickup_lat">
                        <input type="hidden" name="longitude" id="pickup_lng">
                        <!-- Nearest Workshop Banner -->
                        <div x-show="nearestWorkshop" class="mt-2 flex items-center gap-2 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-xl px-3 py-2">
                            <i class="fa-solid fa-circle-check text-blue-500"></i>
                            Your order will be handled by: <span class="text-blue-900" x-text="nearestWorkshop ? nearestWorkshop.name : ''"></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Full Pickup Address</label>
                        <textarea name="pickup_address" id="pickup_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 font-semibold" placeholder="House no, Street name, City"></textarea>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <div class="flex items-center justify-between">
                        <button type="button" @click="goToStep(1)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="goToStep(3)" :disabled="isOutOfRange" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
                            Next Step <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
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
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none flex flex-col items-center justify-end pb-4">
                                            <div x-show="!isOutOfRange" class="bg-slate-900 text-white text-xs font-black px-3 py-1.5 rounded-lg mb-1 shadow-lg whitespace-nowrap border border-slate-700 flex items-center gap-1.5">
                                                <i class="fa-solid fa-motorcycle text-amber-400"></i> Fee: LKR <span x-text="estimatedDeliveryFee"></span>
                                            </div>
                                            <div x-show="isOutOfRange" x-cloak class="bg-red-600 text-white text-xs font-black px-3 py-1.5 rounded-lg mb-1 shadow-lg whitespace-nowrap border border-red-700 animate-pulse flex items-center gap-1.5">
                                                <i class="fa-solid fa-triangle-exclamation text-yellow-300"></i> Out of range
                                            </div>
                                            <div class="text-4xl drop-shadow-xl" :class="isOutOfRange ? 'text-red-500' : 'text-emerald-500'"><i class="fa-solid fa-location-dot"></i></div>
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

                    <!-- Payment Information -->
                    <div class="bg-indigo-50 rounded-2xl shadow-sm border border-indigo-100 p-6 sm:p-8 text-center">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-indigo-500 mx-auto mb-4 shadow-sm text-2xl">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <h3 class="text-lg font-black text-indigo-900 mb-2">Payment Details</h3>
                        <p class="text-sm text-indigo-700 font-medium max-w-md mx-auto mb-4">
                            Payment will be requested after your laundry has been inspected and the total bill is finalized by our workshop. You will be notified via WhatsApp with the exact amount.
                        </p>
                        
                        <div class="bg-white rounded-xl p-4 text-left border border-indigo-100 shadow-sm mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-slate-600">Items Total (Estimate)</span>
                                <span class="text-sm font-black text-slate-800">LKR <span x-text="totalPrice"></span></span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-slate-600">Pickup & Delivery Fee (Estimate)</span>
                                <span class="text-sm font-black text-slate-800">LKR <span x-text="estimatedDeliveryFee"></span></span>
                            </div>
                            <div class="border-t border-slate-100 my-2 pt-2 flex justify-between items-center">
                                <span class="text-base font-black text-indigo-900">Total Estimated Amount</span>
                                <span class="text-base font-black text-indigo-900">LKR <span x-text="totalPrice + estimatedDeliveryFee"></span></span>
                            </div>
                            <p class="text-[10px] text-slate-400 font-medium text-center mt-2">* Delivery fees may vary based on exact route and are finalized by the workshop.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <button type="button" @click="step = 3" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" :disabled="isOutOfRange" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-4 rounded-xl font-black transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2 text-lg">
                        Confirm Booking <i class="fa-solid fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <x-slot name="scripts">
        <script>
            function bookingWizard(categories, isAuth, customer, minLimit, deliveryRanges, workshopsArray) {
                const initialStep = isAuth ? 2 : 1;
                return {
                    step: initialStep,
                    highestStepReached: initialStep,
                    categories: categories,
                    minLimit: Number(minLimit || 0),
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
                    deliveryRanges: deliveryRanges || [],
                    // Workshops array for multi-workshop support
                    workshopsArray: workshopsArray || [],
                    nearestWorkshop: workshopsArray && workshopsArray.length > 0 ? workshopsArray[0] : null,
                    baseLat: (workshopsArray && workshopsArray.length > 0 && !isNaN(parseFloat(workshopsArray[0].latitude))) ? parseFloat(workshopsArray[0].latitude) : 6.9322889,
                    baseLng: (workshopsArray && workshopsArray.length > 0 && !isNaN(parseFloat(workshopsArray[0].longitude))) ? parseFloat(workshopsArray[0].longitude) : 79.8567772,
                    isOutOfRange: false,

                    initData() {
                        // Check if we are starting on step 2 (authenticated)
                        if (this.step === 2) {
                            setTimeout(() => {
                                if (this.pickupMap) {
                                    this.pickupMap.invalidateSize();
                                } else {
                                    this.initPickupMap();
                                }
                            }, 400);
                        }

                        this.$watch('step', value => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Initialize map on step 2 if not already done
                            if (value === 2) {
                                setTimeout(() => {
                                    if (this.pickupMap) {
                                        this.pickupMap.invalidateSize();
                                    } else {
                                        this.initPickupMap();
                                    }
                                }, 400);
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
                            this.calculateDeliveryFee();
                        });
                        
                        this.$watch('deliveryPref', () => this.calculateDeliveryFee());
                    },

                    async goToStep(n) {
                        if (n > this.highestStepReached + 1) return; // Must go in order initially

                        // Validation logic before moving to next step
                        if (n === 3) { // Going to Services
                            const date = document.getElementById('pickup_date_input').value;
                            const time = document.getElementById('pickup_time_input').value;
                            const addr = document.getElementById('pickup_address').value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
                                return;
                            }

                            // Calculate closest branch and fetch services
                            if (this.nearestWorkshop && this.nearestWorkshop.id) {
                                try {
                                    const res = await axios.get('/customer/branch-services/' + this.nearestWorkshop.id);
                                    if (res.data && res.data.categories) {
                                        this.categories = res.data.categories;
                                    }
                                } catch (e) {
                                    console.error("Error fetching branch services", e);
                                }
                            }
                        }
                        
                        if (n === 4) {
                            if (this.cart.length === 0) {
                                alert("Please select at least one service before proceeding.");
                                return;
                            }
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                        }

                        this.step = n;
                        if (n > this.highestStepReached) {
                            this.highestStepReached = n;
                        }
                        
                        // If going to step 1 but already authenticated, skip to step 2
                        if (n === 1 && this.isAuthenticated) {
                            this.step = 2;
                            this.highestStepReached = Math.max(this.highestStepReached, 2);
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
                                this.goToStep(2);
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
                                this.goToStep(2);
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
                        this.pickupMap = L.map('pickup_map', { zoomControl: false }).setView(defaultCenter, 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(this.pickupMap);
                        L.control.zoom({ position: 'bottomright' }).addTo(this.pickupMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.pickupMap, 'pickup');

                        const updateCoords = () => {
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
                                this.cart = []; // clear cart if branch changes
                                this.updateWorkshopMarkerStyles();
                            }
                            this.calculateDeliveryFee();
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
                        const center = this.pickupMap ? this.pickupMap.getCenter() : [this.baseLat, this.baseLng];
                        this.deliveryMap = L.map('delivery_map', { zoomControl: false }).setView(center, 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors',
                            maxZoom: 19
                        }).addTo(this.deliveryMap);
                        L.control.zoom({ position: 'bottomright' }).addTo(this.deliveryMap);

                        // Add all workshop markers
                        this.addWorkshopMarkersToMap(this.deliveryMap, 'delivery');

                        const updateCoords = () => {
                            const center = this.deliveryMap.getCenter();
                            document.getElementById('delivery_lat').value = center.lat.toFixed(6);
                            document.getElementById('delivery_lng').value = center.lng.toFixed(6);
                            this.deliveryLat = center.lat;
                            this.deliveryLng = center.lng;
                            this.calculateDeliveryFee();
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
                        
                        // Use nearest workshop as base (updated dynamically as pin moves)
                        const wsLat = this.baseLat;
                        const wsLng = this.baseLng;
                        
                        // Distance to pickup
                        if (this.pickupLat && this.pickupLng) {
                            distanceToPickup = this.calculateDistance(wsLat, wsLng, this.pickupLat, this.pickupLng);
                            if (distanceToPickup > maxAllowedDistance) {
                                isPickupOutOfRange = true;
                            } else {
                                pickupFee = getFeeForDistance(distanceToPickup);
                            }
                        }
                        
                        // Distance to delivery
                        if (this.deliveryPref === 'deliver_to_address') {
                            if (this.sameAsPickup && this.pickupLat && this.pickupLng) {
                                distanceToDelivery = distanceToPickup;
                                isDeliveryOutOfRange = isPickupOutOfRange;
                                deliveryFee = pickupFee;
                            } else if (!this.sameAsPickup && this.deliveryLat && this.deliveryLng) {
                                distanceToDelivery = this.calculateDistance(wsLat, wsLng, this.deliveryLat, this.deliveryLng);
                                if (distanceToDelivery > maxAllowedDistance) {
                                    isDeliveryOutOfRange = true;
                                } else {
                                    deliveryFee = getFeeForDistance(distanceToDelivery);
                                }
                            }
                        }
                        
                        this.isOutOfRange = isPickupOutOfRange || isDeliveryOutOfRange;
                        this.estimatedDeliveryFee = Math.round(pickupFee + deliveryFee);
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
