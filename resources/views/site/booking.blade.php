<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Pickup - Laundry Zone</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .text-brand { color: #1e3a8a; } /* dark blue */
        .text-accent { color: #2563eb; } /* bright blue */
        .bg-brand { background-color: #1e3a8a; }
        .bg-accent { background-color: #2563eb; }
        .map-container { height: 350px; width: 100%; border-radius: 1rem; z-index: 10; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); }
        .glass-header { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        
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
</head>
<body class="antialiased text-slate-800" x-data="bookingForm({{ Js::from($categories) }})">

<nav class="bg-white/95 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo + Name: always left-aligned -->
            <div class="flex items-center gap-2">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <img src="{{ asset('logos/1786348129_admin_logo.png') }}" alt="Logo" class="h-9 w-auto">
                    <span class="font-black text-lg tracking-tight text-brand">Laundry Zone</span>
                </a>
            </div>

            <!-- Right side -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="text-slate-500 hover:text-blue-600 transition text-sm font-medium flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-3 sm:px-4 py-4 sm:py-6 lg:flex lg:gap-6">
    
    <!-- Main Form Section -->
    <div class="lg:w-2/3">
        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 mb-6 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-5 text-white">
                <h1 class="text-xl md:text-2xl font-extrabold mb-0.5">Schedule a Pickup</h1>
                <p class="text-blue-100 text-xs md:text-sm">Fill in the details below and we'll take care of the rest.</p>
            </div>

            <form action="{{ route('booking.store') }}" method="POST" id="bookingForm" class="p-4 md:p-6 space-y-6" enctype="multipart/form-data" x-data="{ selectedPaymentMethod: '{{ $paymentMethods->first()?->type ?? 'cash' }}' }">
                @csrf
                <input type="hidden" name="item_description" id="item_description" x-model="formattedCart">
                <input type="hidden" name="type" value="pickup">
                
                @if ($errors->any())
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-600">
                        <ul class="list-disc list-inside text-xs font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- 1. Contact Details -->
                <div class="space-y-3">
                    <h3 class="text-sm font-black text-brand mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-user"></i></span>
                        Contact Details
                    </h3>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Services Selection -->
                <div class="pt-2 border-t border-slate-100">
                    <h3 class="text-sm font-black text-brand mb-3 flex items-center gap-2 pb-2">
                        <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-shirt"></i></span>
                        Select Services
                    </h3>
                    <p class="text-xs text-slate-500 mb-3">Select the items you want us to clean.</p>
                    
                    <div class="space-y-2">
                        <template x-for="category in categories" :key="category.id">
                            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden" :class="expandedCategory === category.id ? 'ring-2 ring-blue-100' : ''">
                                <div @click="expandedCategory = expandedCategory === category.id ? null : category.id" class="px-3 py-2.5 flex justify-between items-center cursor-pointer hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-accent flex items-center justify-center text-sm">
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
                                    <div class="border-t border-slate-100 divide-y divide-slate-50">
                                        <template x-for="service in category.services" :key="service.id">
                                            <div class="flex items-center justify-between px-3 py-2 hover:bg-slate-50">
                                                <div>
                                                    <p class="font-semibold text-sm text-slate-700" x-text="service.name"></p>
                                                    <p class="text-xs text-accent font-bold">Rs. <span x-text="service.base_price"></span></p>
                                                </div>
                                                <div class="flex items-center gap-2 bg-slate-100 rounded-lg p-0.5">
                                                    <button type="button" @click="removeFromCart(service)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 hover:text-red-500 shadow-sm transition-colors">
                                                        <i class="fa-solid fa-minus text-xs"></i>
                                                    </button>
                                                    <span class="font-bold w-4 text-center text-sm" x-text="getQuantity(service.id)"></span>
                                                    <button type="button" @click="addToCart(service)" class="w-7 h-7 flex items-center justify-center rounded bg-white text-slate-600 hover:text-accent shadow-sm transition-colors">
                                                        <i class="fa-solid fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="category.services.length === 0" class="text-xs text-slate-500 italic p-3 text-center">
                                            No services available in this category.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Special Instructions</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition-all font-medium placeholder-slate-400" placeholder="E.g., Please fold shirts carefully..."></textarea>
                    </div>
                </div>

                <!-- 3. Pickup Details -->
                <div>
                    <h3 class="text-sm font-black text-brand mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-truck-pickup"></i></span>
                        Pickup Location & Time
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pickup Date & Time</label>
                            <input type="datetime-local" name="pickup_scheduled_at" required class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition-all font-medium">
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700">Pin pickup location on map</label>
                                    <p class="text-xs text-slate-400">Move the map to set the pin.</p>
                                </div>
                                <button type="button" id="btn-find-me" class="bg-white border border-slate-200 hover:bg-slate-50 text-brand px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-crosshairs"></i> Find Me
                                </button>
                            </div>
                            
                            <div class="relative rounded-xl overflow-hidden border border-slate-200">
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                    <div class="text-3xl text-accent drop-shadow-lg pb-6"><i class="fa-solid fa-location-dot"></i></div>
                                </div>
                                <div id="pickup_map" class="map-container"></div>
                            </div>
                            
                            <input type="hidden" name="latitude" id="pickup_lat">
                            <input type="hidden" name="longitude" id="pickup_lng">
                            <div class="mt-1 text-xs font-mono text-slate-400 text-right" id="pickup_coords_display">Waiting for map...</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Full Pickup Address</label>
                            <textarea name="pickup_address" id="pickup_address" required rows="2" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition-all font-medium placeholder-slate-400" placeholder="House no, Street name, City"></textarea>
                        </div>
                    </div>
                </div>

                <!-- 4. Delivery Details -->
                <div>
                    <h3 class="text-sm font-black text-brand mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-box-open"></i></span>
                        Return Delivery
                    </h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_preference" value="deliver_to_address" class="custom-radio sr-only" checked onchange="toggleDeliverySection(true)">
                            <div class="p-3 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0">
                                    <div class="radio-dot w-2 h-2 rounded-full"></div>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-slate-800">Deliver to me</div>
                                    <div class="text-xs text-slate-500">Rider brings it back</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_preference" value="pickup_from_branch" class="custom-radio sr-only" onchange="toggleDeliverySection(false)">
                            <div class="p-3 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0">
                                    <div class="radio-dot w-2 h-2 rounded-full"></div>
                                </div>
                                <div>
                                    <div class="font-bold text-sm text-slate-800">I will pick up</div>
                                    <div class="text-xs text-slate-500">Collect from branch</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div id="delivery_address_section">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-blue-50 border border-blue-100 rounded-xl">
                            <input type="checkbox" name="same_as_pickup" id="same_as_pickup" value="1" checked class="w-4 h-4 text-accent rounded border-slate-300 focus:ring-accent">
                            <span class="font-bold text-xs text-brand">Same as pickup address</span>
                        </label>

                        <div id="custom_delivery_map_section" class="hidden space-y-3 mt-3 pt-3 border-t border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pin delivery location on map</label>
                                <div class="relative rounded-xl overflow-hidden border border-slate-200">
                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                        <div class="text-3xl text-accent drop-shadow-lg pb-6"><i class="fa-solid fa-location-dot"></i></div>
                                    </div>
                                    <div id="delivery_map" class="map-container"></div>
                                </div>
                                <input type="hidden" name="delivery_lat" id="delivery_lat">
                                <input type="hidden" name="delivery_lng" id="delivery_lng">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Full Delivery Address</label>
                                <textarea name="delivery_address" id="delivery_address" rows="2" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition-all font-medium placeholder-slate-400" placeholder="House no, Street name, City"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Payment Details -->
                <div>
                    <h3 class="text-sm font-black text-brand mb-3 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-lg flex items-center justify-center text-xs"><i class="fa-solid fa-credit-card"></i></span>
                        Payment Options
                    </h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        @foreach($paymentMethods as $index => $method)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $method->name }}" class="custom-radio sr-only" x-model="selectedPaymentMethod" value="{{ $method->type }}" {{ $index === 0 ? 'checked' : '' }}>
                                <div class="p-3 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                    <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex items-center justify-center shrink-0">
                                        <div class="radio-dot w-2 h-2 rounded-full"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($method->type === 'cash')
                                            <i class="fa-solid fa-money-bill-wave text-green-600 text-base"></i>
                                        @elseif($method->type === 'card_transfer')
                                            <i class="fa-solid fa-credit-card text-blue-600 text-base"></i>
                                        @else
                                            <i class="fa-solid fa-building-columns text-purple-600 text-base"></i>
                                        @endif
                                        <div class="font-bold text-sm text-slate-800">{{ $method->name }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Bank Transfer Receipt Upload -->
                    <div x-show="selectedPaymentMethod === 'bank_transfer'" x-transition class="mb-4 bg-purple-50 p-4 rounded-xl border border-purple-100">
                        @php $bankMethod = $paymentMethods->where('type', 'bank_transfer')->first(); @endphp
                        @if($bankMethod && $bankMethod->instructions)
                            <div class="text-xs text-purple-800 whitespace-pre-line mb-3 font-medium">{{ $bankMethod->instructions }}</div>
                        @endif
                        <label class="block text-xs font-bold text-slate-700 mb-1">Upload Receipt (Optional for now)</label>
                        <input type="file" name="receipt_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">When will you pay?</label>
                        <select name="payment_timing" required class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent transition-all font-medium">
                            <option value="at_pickup">At Pickup</option>
                            <option value="at_delivery">At Delivery</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 lg:hidden">
                    <button type="submit" class="bg-accent text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg w-full">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sidebar: Order Summary -->
    <div class="lg:w-1/3 mt-8 lg:mt-0">
        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 p-6 sticky top-28">
            <h3 class="text-xl font-black text-brand mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
                <i class="fa-solid fa-receipt text-accent"></i> Order Summary
            </h3>
            
            <div x-show="cart.length === 0" class="text-center py-8 text-slate-400">
                <i class="fa-solid fa-basket-shopping text-4xl mb-3 opacity-50"></i>
                <p class="text-sm font-medium">Your cart is empty.</p>
                <p class="text-xs mt-1">Select services to see them here.</p>
            </div>

            <div x-show="cart.length > 0" style="display:none;" class="space-y-4 mb-6 max-h-[300px] overflow-y-auto custom-scrollbar pr-2">
                <template x-for="item in cart" :key="item.service.id">
                    <div class="flex justify-between items-start pb-4 border-b border-slate-50 last:border-0 last:pb-0">
                        <div>
                            <p class="font-bold text-sm text-slate-800" x-text="item.service.name"></p>
                            <p class="text-xs text-slate-500">Rs. <span x-text="item.service.base_price"></span> x <span x-text="item.quantity"></span></p>
                        </div>
                        <p class="font-bold text-sm text-brand">Rs. <span x-text="item.service.base_price * item.quantity"></span></p>
                    </div>
                </template>
            </div>

            <div class="border-t border-slate-200 pt-4" x-show="cart.length > 0" style="display:none;">
                <div class="flex justify-between items-center mb-6">
                    <span class="font-bold text-slate-600">Estimated Total</span>
                    <span class="text-2xl font-black text-accent">Rs. <span x-text="totalPrice"></span></span>
                </div>
                <p class="text-[10px] text-slate-400 text-center mb-4">*Final price may vary based on exact weight and inspection by staff.</p>
            </div>
            
            <button type="submit" form="bookingForm" class="w-full bg-accent text-white px-6 py-4 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg flex justify-center items-center gap-2">
                Confirm Booking <i class="fa-solid fa-check-circle"></i>
            </button>
        </div>
    </div>
</div>

<footer class="bg-white border-t border-slate-200 mt-12 py-8">
    <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm font-medium">
        &copy; {{ date('Y') }} Laundry Zone. All Rights Reserved.
    </div>
</footer>

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
                    result += `${item.service.name} x ${item.quantity} (Rs. ${item.service.base_price * item.quantity})\n`;
                });
                result += `\nEstimated Total: Rs. ${this.totalPrice}`;
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
</body>
</html>
