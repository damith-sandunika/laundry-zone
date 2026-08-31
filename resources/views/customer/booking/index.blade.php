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
                              x-text="['Account', 'Services', 'Pickup', 'Delivery & Pay'][i-1]"></span>
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

            <!-- STEP 2: Services Selection -->
            <div x-show="step === 2" style="display:none;" x-transition.opacity.duration.300ms>
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <h3 class="text-xl font-black text-slate-800 mb-2 flex items-center gap-3">
                        <span class="bg-blue-50 text-blue-600 w-10 h-10 rounded-xl flex items-center justify-center"><i class="fa-solid fa-shirt"></i></span>
                        Select Services
                    </h3>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Choose the items you want us to clean.</p>
                    
                    <!-- Category Slider with 2 rows + arrows -->
                    <div x-data="{
                        sliderScrollLeft() { $refs.catSlider.scrollBy({ left: -280, behavior: 'smooth' }); },
                        sliderScrollRight() { $refs.catSlider.scrollBy({ left: 280, behavior: 'smooth' }); }
                    }" class="relative mb-4 group">

                        <!-- Left Arrow -->
                        <button type="button" @click="sliderScrollLeft()"
                            class="absolute left-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-full shadow-md text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-all opacity-0 group-hover:opacity-100 -ml-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>

                        <!-- 2-row slider grid -->
                        <div x-ref="catSlider"
                            class="grid grid-rows-2 grid-flow-col gap-2.5 overflow-x-hidden scroll-smooth w-full py-1 px-1">
                            <template x-for="(category, index) in categories" :key="category.id">
                                <button type="button"
                                    @click="expandedCategory = (expandedCategory === category.id ? null : category.id)"
                                    class="relative rounded-xl overflow-hidden shadow-sm transition-all group/cat w-32 h-16 shrink-0 border-2"
                                    :class="expandedCategory === category.id
                                        ? 'border-blue-500 ring-2 ring-blue-400 scale-95 shadow-md'
                                        : 'border-transparent hover:shadow-md'">

                                    <!-- Image or placeholder -->
                                    <template x-if="category.image_path">
                                        <img :src="'/' + category.image_path" class="w-full h-full object-cover transition-transform duration-500 group-hover/cat:scale-110" alt="">
                                    </template>
                                    <template x-if="!category.image_path">
                                        <div class="w-full h-full bg-gradient-to-br from-blue-50 to-slate-100 flex items-center justify-center">
                                            <i class="fa-solid fa-basket-shopping text-xl text-slate-300"></i>
                                        </div>
                                    </template>

                                    <!-- Overlay label -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/10 to-transparent flex items-end p-1.5">
                                        <span class="text-white text-[10px] font-black leading-tight text-left drop-shadow-sm line-clamp-2" x-text="category.name"></span>
                                    </div>

                                    <!-- Check badge -->
                                    <div x-show="expandedCategory === category.id"
                                        class="absolute top-1.5 right-1.5 w-4 h-4 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-sm">
                                        <i class="fa-solid fa-check text-[8px]"></i>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <!-- Right Arrow -->
                        <button type="button" @click="sliderScrollRight()"
                            class="absolute right-0 top-1/2 -translate-y-1/2 z-20 w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-full shadow-md text-slate-500 hover:text-blue-600 hover:bg-slate-50 transition-all opacity-0 group-hover:opacity-100 -mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <!-- Services List for Selected Category (inline expansion) -->
                    <div x-show="expandedCategory" x-collapse class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-4">
                        <template x-for="category in categories" :key="category.id">
                            <div x-show="expandedCategory === category.id">
                                <div class="p-4 sm:p-5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                    <h4 class="font-black text-slate-800 text-base flex items-center gap-2">
                                        <i class="fa-solid fa-list-check text-blue-600"></i>
                                        <span x-text="category.name + ' Services'"></span>
                                    </h4>
                                    <button type="button" @click="expandedCategory = null" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-slate-300 hover:text-slate-700 transition-colors">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>
                                </div>
                                <div class="divide-y divide-slate-100">
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
                        <button type="button" @click="goToStep(1)" class="text-slate-500 hover:text-slate-800 font-bold flex items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" @click="goToStep(3)" :disabled="totalPrice < minLimit || cart.length === 0" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
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
                        <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-lg">
                            
                            <!-- Centered Pin -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                    class="text-white text-[11px] font-black px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                    <template x-if="!isOutOfRange">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span><i class="fa-solid fa-motorcycle text-amber-400"></i> Fee: LKR <span x-text="estimatedDeliveryFee"></span></span>
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
                                            <div class="text-white text-xs font-black" x-text="nearestWorkshop ? nearestWorkshop.name : ''"></div>
                                        </div>
                                    </div>
                                    <div x-show="pickupDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                        <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                        <span class="text-white text-xs font-black"><span x-text="pickupDistance"></span> km</span>
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
                        <button type="button" @click="goToStep(4)" :disabled="isOutOfRange" class="bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-3.5 rounded-xl font-black transition-all shadow-md flex items-center gap-2">
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
                                    <div class="flex justify-between items-center mb-3">
                                        <label class="block text-sm font-bold text-slate-700 flex items-center gap-2">
                                            <span class="w-6 h-6 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs"><i class="fa-solid fa-map-pin"></i></span>
                                            Pin your delivery location
                                        </label>
                                    </div>
                                    <!-- Delivery Map Wrapper -->
                                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-lg">
                                        <!-- Centered Pin -->
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-full z-[400] pointer-events-none flex flex-col items-center">
                                            <div :class="isOutOfRange ? 'bg-red-600 border-red-700' : 'bg-slate-900 border-slate-700'"
                                                class="text-white text-[11px] font-black px-2.5 py-1.5 rounded-lg mb-1 shadow-xl whitespace-nowrap border flex flex-col items-center gap-0.5 transition-colors">
                                                <template x-if="!isOutOfRange">
                                                    <div class="flex flex-col items-center gap-0.5">
                                                        <span><i class="fa-solid fa-motorcycle text-amber-400"></i> Fee: LKR <span x-text="estimatedDeliveryFee"></span></span>
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
                                                        <div class="text-white text-xs font-black">Move pin to set location</div>
                                                    </div>
                                                </div>
                                                <div x-show="deliveryDistance > 0" class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-lg px-2.5 py-1.5">
                                                    <i class="fa-solid fa-route text-amber-300 text-xs"></i>
                                                    <span class="text-white text-xs font-black"><span x-text="deliveryDistance"></span> km</span>
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
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-lg border border-slate-700 p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-white text-xl shadow-lg">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-white">Order Summary</h3>
                                <p class="text-slate-400 text-xs font-medium">Estimated costs — final bill after inspection</p>
                            </div>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-shirt text-blue-400 text-xs"></i> Services (Estimate)
                                </span>
                                <span class="text-white text-sm font-black">LKR <span x-text="totalPrice"></span></span>
                            </div>
                            <div class="flex justify-between items-center py-2.5 border-b border-slate-700">
                                <span class="text-slate-300 text-sm font-bold flex items-center gap-2">
                                    <i class="fa-solid fa-motorcycle text-amber-400 text-xs"></i> Pickup & Delivery Fee
                                </span>
                                <span class="text-white text-sm font-black">LKR <span x-text="estimatedDeliveryFee"></span></span>
                            </div>
                            <div x-show="pickupDistance > 0" class="flex justify-between items-center py-1.5 text-slate-400 text-xs">
                                <span class="flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Pickup distance</span>
                                <span class="font-bold"><span x-text="pickupDistance"></span> km from branch</span>
                            </div>
                        </div>

                        <div class="bg-blue-600 rounded-xl p-4 flex justify-between items-center">
                            <span class="text-blue-100 font-black text-sm">Total Estimated Amount</span>
                            <span class="text-white font-black text-xl">LKR <span x-text="totalPrice + estimatedDeliveryFee"></span></span>
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
                    <button type="submit" :disabled="isOutOfRange" class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-8 py-4 rounded-xl font-black transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2 text-lg">
                        Confirm Booking <i class="fa-solid fa-check-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Floating Total Bar (Glassmorphic) -->
            <div x-show="totalPrice > 0" x-transition.opacity.duration.300ms
                 class="fixed bottom-0 left-0 right-0 z-[1000] p-4 pointer-events-none pb-4 sm:pb-6" style="display: none;">
                <div class="max-w-3xl mx-auto bg-slate-900/95 backdrop-blur-md border border-slate-700/50 rounded-2xl shadow-2xl p-3 sm:p-4 flex flex-col pointer-events-auto transition-transform">
                    
                    <!-- Min Limit Alert -->
                    <div x-show="totalPrice < minLimit" class="bg-red-500/10 border border-red-500/20 rounded-lg p-2 mb-3 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-400 text-xs"></i>
                        <span class="text-red-300 text-xs font-bold">Minimum order of LKR <span x-text="minLimit"></span> not reached yet.</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-400 shrink-0">
                                <i class="fa-solid fa-cart-shopping text-lg sm:text-xl"></i>
                            </div>
                            <div>
                                <div class="text-slate-400 text-[9px] sm:text-[10px] font-bold uppercase tracking-wider">Your Order Total</div>
                                <div class="text-white font-black text-base sm:text-lg leading-tight mt-0.5">
                                    LKR <span x-text="totalPrice + estimatedDeliveryFee"></span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <div class="text-slate-300 text-[10px] sm:text-xs font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-shirt text-[9px] text-blue-400"></i> Items: LKR <span x-text="totalPrice"></span>
                            </div>
                            <div x-show="estimatedDeliveryFee > 0" class="text-slate-300 text-[10px] sm:text-xs font-bold flex items-center gap-1.5 mt-0.5">
                                <i class="fa-solid fa-motorcycle text-[9px] text-amber-400"></i> Fee: LKR <span x-text="estimatedDeliveryFee"></span>
                            </div>
                        </div>
                    </div>
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
                    pickupDistance: 0,       // km from workshop to pickup
                    deliveryDistance: 0,     // km from workshop to delivery
                    pickupRouteLayer: null,  // OSRM route polyline on pickup map
                    deliveryRouteLayer: null,// OSRM route polyline on delivery map
                    deliveryRanges: deliveryRanges || [],
                    // Workshops array for multi-workshop support
                    workshopsArray: workshopsArray || [],
                    nearestWorkshop: workshopsArray && workshopsArray.length > 0 ? workshopsArray[0] : null,
                    baseLat: (workshopsArray && workshopsArray.length > 0 && !isNaN(parseFloat(workshopsArray[0].latitude))) ? parseFloat(workshopsArray[0].latitude) : 6.9322889,
                    baseLng: (workshopsArray && workshopsArray.length > 0 && !isNaN(parseFloat(workshopsArray[0].longitude))) ? parseFloat(workshopsArray[0].longitude) : 79.8567772,
                    isOutOfRange: false,

                    initData() {
                        // Check if we are starting on step 3
                        if (this.step === 3) {
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
                            
                            // Initialize map on step 3 if not already done
                            if (value === 3) {
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
                        if (n === 3) { // Going to Pickup
                            if (this.cart.length === 0) {
                                alert("Please select at least one service before proceeding.");
                                return;
                            }
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                        }
                        
                        if (n === 4) { // Going to Delivery
                            const date = document.getElementById('pickup_date_input').value;
                            const time = document.getElementById('pickup_time_input').value;
                            const addr = document.getElementById('pickup_address').value;
                            
                            if (!date || !time || !addr) {
                                alert("Please fill in all pickup details (Date, Time, Address).");
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
                        this.pickupMap.on('moveend', () => {
                            updatePickupCoords();
                            // Draw route from workshop to pickup pin
                            if (this.pickupLat && this.pickupLng) {
                                this.drawRoute(this.pickupMap, this.baseLat, this.baseLng, this.pickupLat, this.pickupLng, 'pickup');
                            }
                        });
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

                    // Draw real road route from workshop to pin using OSRM
                    async drawRoute(map, fromLat, fromLng, toLat, toLng, type) {
                        try {
                            const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`;
                            const res = await fetch(url);
                            const data = await res.json();
                            if (data.code !== 'Ok' || !data.routes || !data.routes[0]) return;

                            const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                            const routeDistKm = (data.routes[0].distance / 1000).toFixed(1);

                            // Remove old route
                            if (type === 'pickup' && this.pickupRouteLayer) {
                                map.removeLayer(this.pickupRouteLayer);
                            } else if (type === 'delivery' && this.deliveryRouteLayer) {
                                map.removeLayer(this.deliveryRouteLayer);
                            }

                            // Draw dashed route line
                            const routeLine = L.polyline(coords, {
                                color: '#3b82f6',
                                weight: 4,
                                opacity: 0.85,
                                dashArray: '10, 8',
                                lineJoin: 'round',
                            }).addTo(map);

                            if (type === 'pickup') {
                                this.pickupRouteLayer = routeLine;
                                this.pickupDistance = parseFloat(routeDistKm);
                            } else {
                                this.deliveryRouteLayer = routeLine;
                                this.deliveryDistance = parseFloat(routeDistKm);
                            }
                        } catch (e) {
                            // Silently fail - network issue
                        }
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
