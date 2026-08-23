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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        .text-brand { color: #1e3a8a; } 
        .text-accent { color: #2563eb; } 
        .bg-brand { background-color: #1e3a8a; }
        .bg-accent { background-color: #2563eb; }
        .map-container { height: 350px; width: 100%; border-radius: 1rem; z-index: 10; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); }
        
        /* Modern Radio Buttons */
        .custom-radio:checked + div {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
        .custom-radio:checked + div .radio-dot {
            background-color: #2563eb;
            transform: scale(1);
        }
        .radio-dot { transform: scale(0); transition: transform 0.2s ease-in-out; }
    </style>

    <div x-data="bookingForm({{ Js::from($categories) }})">
        <div class="lg:flex lg:gap-6">
            
            <!-- Main Form Section -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 mb-6 overflow-hidden">
                    
                    <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm" class="p-6 space-y-6" enctype="multipart/form-data" x-data="{ selectedPaymentMethod: '{{ $paymentMethods->first()?->type ?? 'cash' }}' }">
                        @csrf
                        <input type="hidden" name="item_description" id="item_description" x-model="formattedCart">
                        <input type="hidden" name="type" value="pickup">
                        
                        @if ($errors->any())
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-600 mb-6">
                                <ul class="list-disc list-inside text-xs font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- 1. Contact Details & Branch -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="bg-slate-100 text-slate-600 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-user"></i></span>
                                Booking Details
                            </h3>
                            
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl text-sm text-slate-700 font-medium mb-4 flex justify-between items-center">
                                <span>Booking as: <strong>{{ auth('customer')->user()->name }}</strong> ({{ auth('customer')->user()->phone }})</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Select Nearest Branch</label>
                                <select name="branch_id" id="branch_selector" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold cursor-pointer">
                                    <option value="">Select a branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->address }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 2. Services Selection -->
                        <div class="pt-4 border-t border-slate-100 mt-6">
                            <h3 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2 pb-2 border-b border-slate-100">
                                <span class="bg-slate-100 text-slate-600 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-shirt"></i></span>
                                Select Services
                            </h3>
                            <p class="text-xs text-slate-500 mb-4 font-medium">Select the items you want us to clean.</p>
                            
                            <div class="space-y-3">
                                <template x-for="category in categories" :key="category.id">
                                    <div class="bg-slate-50 rounded-xl border border-slate-200 overflow-hidden" :class="expandedCategory === category.id ? 'ring-2 ring-blue-100' : ''">
                                        <div @click="expandedCategory = expandedCategory === category.id ? null : category.id" class="px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition-colors">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-white shadow-sm text-blue-600 flex items-center justify-center text-sm border border-slate-100">
                                                    <i class="fa-solid fa-layer-group" x-show="category.name !== 'Wash & Fold' && category.name !== 'Dry Cleaning' && category.name !== 'Ironing'"></i>
                                                    <i class="fa-solid fa-soap" x-show="category.name === 'Wash & Fold'"></i>
                                                    <i class="fa-solid fa-shirt" x-show="category.name === 'Dry Cleaning'"></i>
                                                    <i class="fa-solid fa-fire-burner" x-show="category.name === 'Ironing'"></i>
                                                </div>
                                                <span class="font-bold text-sm text-slate-800" x-text="category.name"></span>
                                            </div>
                                            <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-300" :class="expandedCategory === category.id ? 'rotate-180' : ''"></i>
                                        </div>
                                        
                                        <div x-show="expandedCategory === category.id" 
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0"
                                             x-transition:enter-end="opacity-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-end="opacity-0"
                                             style="display:none;">
                                            <div class="border-t border-slate-200 divide-y divide-slate-100 bg-white">
                                                <template x-for="service in category.services" :key="service.id">
                                                    <div class="flex items-center justify-between px-4 py-3 hover:bg-slate-50">
                                                        <div>
                                                            <p class="font-bold text-sm text-slate-700" x-text="service.name"></p>
                                                            <p class="text-xs text-blue-600 font-bold">LKR <span x-text="service.base_price"></span></p>
                                                        </div>
                                                        <div class="flex items-center gap-3 bg-slate-100 rounded-lg p-1">
                                                            <button type="button" @click="removeFromCart(service)" class="w-8 h-8 flex items-center justify-center rounded-md bg-white text-slate-600 hover:text-red-500 shadow-sm transition-colors border border-slate-200">
                                                                <i class="fa-solid fa-minus text-xs"></i>
                                                            </button>
                                                            <span class="font-black w-4 text-center text-sm" x-text="getQuantity(service.id)"></span>
                                                            <button type="button" @click="addToCart(service)" class="w-8 h-8 flex items-center justify-center rounded-md bg-white text-slate-600 hover:text-blue-600 shadow-sm transition-colors border border-slate-200">
                                                                <i class="fa-solid fa-plus text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="category.services.length === 0" class="text-xs font-bold text-slate-400 p-4 text-center">
                                                    No services available in this category.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Special Instructions</label>
                                <textarea name="notes" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold placeholder-slate-400" placeholder="E.g., Please fold shirts carefully..."></textarea>
                            </div>
                        </div>

                        <!-- 3. Pickup Details -->
                        <div class="pt-4 border-t border-slate-100 mt-6">
                            <h3 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="bg-slate-100 text-slate-600 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-truck-pickup"></i></span>
                                Pickup Location & Time
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Pickup Date & Time</label>
                                    <input type="datetime-local" name="pickup_scheduled_at" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold">
                                </div>

                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700">Pin pickup location on map</label>
                                            <p class="text-xs text-slate-400 font-medium">Move the map to set the pin.</p>
                                        </div>
                                        <button type="button" id="btn-find-me" class="bg-white border border-slate-200 hover:bg-slate-50 text-blue-600 px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                                            <i class="fa-solid fa-location-crosshairs"></i> Find Me
                                        </button>
                                    </div>
                                    
                                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                            <div class="text-3xl text-blue-600 drop-shadow-lg pb-6"><i class="fa-solid fa-location-dot"></i></div>
                                        </div>
                                        <div id="pickup_map" class="map-container"></div>
                                    </div>
                                    
                                    <input type="hidden" name="latitude" id="pickup_lat">
                                    <input type="hidden" name="longitude" id="pickup_lng">
                                    <div class="mt-2 text-[10px] font-mono text-slate-400 text-right font-semibold" id="pickup_coords_display">Waiting for map...</div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Pickup Address</label>
                                    <textarea name="pickup_address" id="pickup_address" required rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold placeholder-slate-400" placeholder="House no, Street name, City"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Delivery Details -->
                        <div class="pt-4 border-t border-slate-100 mt-6">
                            <h3 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="bg-slate-100 text-slate-600 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-box-open"></i></span>
                                Return Delivery
                            </h3>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="delivery_preference" value="deliver_to_address" class="custom-radio sr-only" checked onchange="toggleDeliverySection(true)">
                                    <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 hover:border-blue-300 transition-all flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                            <div class="radio-dot w-2 h-2 rounded-full"></div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-800">Deliver to me</div>
                                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Rider brings it back</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="delivery_preference" value="pickup_from_branch" class="custom-radio sr-only" onchange="toggleDeliverySection(false)">
                                    <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 hover:border-blue-300 transition-all flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                            <div class="radio-dot w-2 h-2 rounded-full"></div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-800">I will pick up</div>
                                            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Collect from branch</div>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div id="delivery_address_section">
                                <label class="flex items-center gap-3 cursor-pointer p-4 bg-blue-50 border border-blue-100 rounded-xl">
                                    <input type="checkbox" name="same_as_pickup" id="same_as_pickup" value="1" checked class="w-4 h-4 text-blue-600 rounded border-blue-200 focus:ring-blue-500 bg-white">
                                    <span class="font-bold text-sm text-blue-800">Same as pickup address</span>
                                </label>

                                <div id="custom_delivery_map_section" class="hidden space-y-4 mt-4 pt-4 border-t border-slate-100">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Pin delivery location on map</label>
                                        <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                                <div class="text-3xl text-blue-600 drop-shadow-lg pb-6"><i class="fa-solid fa-location-dot"></i></div>
                                            </div>
                                            <div id="delivery_map" class="map-container"></div>
                                        </div>
                                        <input type="hidden" name="delivery_lat" id="delivery_lat">
                                        <input type="hidden" name="delivery_lng" id="delivery_lng">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Delivery Address</label>
                                        <textarea name="delivery_address" id="delivery_address" rows="2" class="w-full px-4 py-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold placeholder-slate-400" placeholder="House no, Street name, City"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Payment Details -->
                        <div class="pt-4 border-t border-slate-100 mt-6">
                            <h3 class="text-sm font-black text-slate-800 mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                                <span class="bg-slate-100 text-slate-600 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-credit-card"></i></span>
                                Payment Options
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                @foreach($paymentMethods as $index => $method)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $method->name }}" class="custom-radio sr-only" x-model="selectedPaymentMethod" value="{{ $method->type }}" {{ $index === 0 ? 'checked' : '' }}>
                                        <div class="p-4 border-2 border-slate-200 rounded-xl bg-slate-50 hover:border-blue-300 transition-all flex items-center gap-3">
                                            <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0 bg-white">
                                                <div class="radio-dot w-2 h-2 rounded-full"></div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                @if($method->type === 'cash')
                                                    <i class="fa-solid fa-money-bill-wave text-green-600 text-lg"></i>
                                                @elseif($method->type === 'card_transfer')
                                                    <i class="fa-solid fa-credit-card text-blue-600 text-lg"></i>
                                                @else
                                                    <i class="fa-solid fa-building-columns text-purple-600 text-lg"></i>
                                                @endif
                                                <div class="font-bold text-sm text-slate-800">{{ $method->name }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Bank Transfer Receipt Upload -->
                            <div x-show="selectedPaymentMethod === 'bank_transfer'" x-transition class="mb-4 bg-purple-50 p-5 rounded-2xl border border-purple-100 shadow-inner">
                                @php $bankMethod = $paymentMethods->where('type', 'bank_transfer')->first(); @endphp
                                @if($bankMethod && $bankMethod->instructions)
                                    <div class="text-xs text-purple-800 whitespace-pre-line mb-4 font-bold bg-white p-3 rounded-lg border border-purple-100">{{ $bankMethod->instructions }}</div>
                                @endif
                                <label class="block text-xs font-black text-purple-900 mb-2">Upload Receipt (Optional for now)</label>
                                <input type="file" name="receipt_image" accept="image/*" class="w-full text-xs text-purple-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-white file:text-purple-700 hover:file:bg-purple-100 file:shadow-sm file:cursor-pointer">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">When will you pay?</label>
                                <select name="payment_timing" required class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-semibold">
                                    <option value="at_pickup">At Pickup</option>
                                    <option value="at_delivery">At Delivery</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 lg:hidden">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-4 rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-200 w-full flex justify-center items-center gap-2">
                                Confirm Booking <i class="fa-solid fa-check-circle"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sticky top-24">
                    <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i class="fa-solid fa-receipt text-blue-600"></i> Order Summary
                    </h3>
                    
                    <div x-show="cart.length === 0" class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-basket-shopping text-3xl mb-3 opacity-30"></i>
                        <p class="text-sm font-bold text-slate-500">Your cart is empty.</p>
                        <p class="text-xs mt-1 font-medium text-slate-400">Select services to see them here.</p>
                    </div>

                    <div x-show="cart.length > 0" style="display:none;" class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                        <template x-for="item in cart" :key="item.service.id">
                            <div class="flex justify-between items-start pb-3 border-b border-slate-50 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-bold text-sm text-slate-800" x-text="item.service.name"></p>
                                    <p class="text-xs font-bold text-slate-500">LKR <span x-text="item.service.base_price"></span> x <span x-text="item.quantity"></span></p>
                                </div>
                                <p class="font-black text-sm text-blue-600">LKR <span x-text="item.service.base_price * item.quantity"></span></p>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-slate-100 pt-4" x-show="cart.length > 0" style="display:none;">
                        <div class="flex justify-between items-center mb-4 bg-slate-50 p-4 rounded-xl">
                            <span class="font-bold text-slate-600">Estimated Total</span>
                            <span class="text-xl font-black text-blue-600">LKR <span x-text="totalPrice"></span></span>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 text-center mb-6 uppercase tracking-wider">*Final price may vary based on exact weight and inspection by staff.</p>
                    </div>
                    
                    <button type="submit" form="bookingForm" class="w-full bg-blue-600 text-white px-6 py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200 flex justify-center items-center gap-2">
                        Confirm Booking <i class="fa-solid fa-check-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts Section for x-customer-layout -->
    <x-slot name="scripts">
        <script>
            function bookingForm(categories) {
                return {
                    categories: categories,
                    expandedCategory: null,
                    cart: [],

                    getQuantity(serviceId) {
                        const item = this.cart.find(i => i.service.id === serviceId);
                        return item ? item.quantity : 0;
                    },

                    addToCart(service) {
                        const item = this.cart.find(i => i.service.id === service.id);
                        if (item) {
                            item.quantity++;
                        } else {
                            this.cart.push({ service: service, quantity: 1 });
                        }
                    },

                    removeFromCart(service) {
                        const index = this.cart.findIndex(i => i.service.id === service.id);
                        if (index !== -1) {
                            if (this.cart[index].quantity > 1) {
                                this.cart[index].quantity--;
                            } else {
                                this.cart.splice(index, 1);
                            }
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

        <script>
            // Leaflet Maps Setup
            document.addEventListener('DOMContentLoaded', function() {
                const defaultCenter = [6.9271, 79.8612]; // Colombo
                
                // 1. Pickup Map
                const pickupMap = L.map('pickup_map', { zoomControl: false }).setView(defaultCenter, 13);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(pickupMap);
                
                L.control.zoom({ position: 'bottomright' }).addTo(pickupMap);

                const pickupLatInput = document.getElementById('pickup_lat');
                const pickupLngInput = document.getElementById('pickup_lng');
                const pickupCoordsDisplay = document.getElementById('pickup_coords_display');

                function updatePickupCoords() {
                    const center = pickupMap.getCenter();
                    pickupLatInput.value = center.lat.toFixed(6);
                    pickupLngInput.value = center.lng.toFixed(6);
                    pickupCoordsDisplay.innerText = `Lat: ${center.lat.toFixed(4)}, Lng: ${center.lng.toFixed(4)}`;
                }

                pickupMap.on('move', updatePickupCoords);
                pickupMap.on('moveend', updatePickupCoords);
                updatePickupCoords(); // Initial call

                // Find Me Button
                document.getElementById('btn-find-me').addEventListener('click', function() {
                    if ("geolocation" in navigator) {
                        navigator.geolocation.getCurrentPosition(function(position) {
                            pickupMap.setView([position.coords.latitude, position.coords.longitude], 16);
                        }, function(error) {
                            alert("Could not get your location. Please check browser permissions.");
                        });
                    } else {
                        alert("Geolocation is not supported by your browser.");
                    }
                });

                // 2. Delivery Map
                let deliveryMapInitialized = false;
                let deliveryMap;
                const deliveryLatInput = document.getElementById('delivery_lat');
                const deliveryLngInput = document.getElementById('delivery_lng');

                window.initDeliveryMap = function() {
                    if (deliveryMapInitialized) return;
                    
                    deliveryMap = L.map('delivery_map', { zoomControl: false }).setView(pickupMap.getCenter(), 13);
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(deliveryMap);
                    
                    L.control.zoom({ position: 'bottomright' }).addTo(deliveryMap);

                    function updateDeliveryCoords() {
                        const center = deliveryMap.getCenter();
                        deliveryLatInput.value = center.lat.toFixed(6);
                        deliveryLngInput.value = center.lng.toFixed(6);
                    }

                    deliveryMap.on('move', updateDeliveryCoords);
                    deliveryMap.on('moveend', updateDeliveryCoords);
                    updateDeliveryCoords();
                    
                    deliveryMapInitialized = true;
                    
                    // Invalidate size after map becomes visible
                    setTimeout(() => { deliveryMap.invalidateSize(); }, 100);
                };
            });

            // Toggle Delivery Section Logic
            function toggleDeliverySection(show) {
                const section = document.getElementById('delivery_address_section');
                if (show) {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            }

            // Toggle Custom Delivery Map based on Checkbox
            document.getElementById('same_as_pickup').addEventListener('change', function(e) {
                const customSection = document.getElementById('custom_delivery_map_section');
                if (e.target.checked) {
                    customSection.classList.add('hidden');
                } else {
                    customSection.classList.remove('hidden');
                    if (typeof window.initDeliveryMap === 'function') {
                        window.initDeliveryMap();
                    }
                }
            });
        </script>
    </x-slot>
</x-customer-layout>
