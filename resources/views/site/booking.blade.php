<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Pickup - Laundry Zone</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .map-container { height: 350px; width: 100%; border-radius: 0.75rem; z-index: 10; box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); }
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
<body class="antialiased text-slate-800">

<nav class="glass-header shadow-sm sticky top-0 z-50 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="text-3xl drop-shadow-sm">👕</span>
                    <span class="font-black text-2xl tracking-tight bg-gradient-to-r from-blue-700 to-indigo-600 bg-clip-text text-transparent">Laundry Zone</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-10 text-white relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-indigo-400 opacity-20 rounded-full blur-2xl"></div>
            
            <h1 class="text-3xl md:text-4xl font-black mb-2 relative z-10">Schedule a Pickup</h1>
            <p class="text-blue-100 font-medium relative z-10 text-lg">Quick, easy, and right to your door.</p>
        </div>

        <div class="p-6 md:p-10">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl">
                    <h4 class="font-bold mb-2">Please fix the following errors:</h4>
                    <ul class="list-disc pl-5 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('booking.store') }}" method="POST">
                @csrf
                
                <!-- 1. Basic Info -->
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span> 
                    Contact Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Your Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="John Doe">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">WhatsApp / Phone Number</label>
                        <input type="text" name="phone" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="07XXXXXXXX">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nearest Branch</label>
                        <select name="branch_id" id="branch_selector" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium">
                            <option value="">Select a branch...</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }} ({{ $branch->address }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 2. Pickup Details -->
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span> 
                    Pickup Location & Time
                </h3>
                <div class="mb-8 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Pickup Date & Time</label>
                        <input type="datetime-local" name="pickup_scheduled_at" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium">
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Pin pickup location on map</label>
                                <p class="text-xs text-slate-500 font-medium">Move the map to set the exact pin.</p>
                            </div>
                            <button type="button" id="btn-find-me" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                                📍 Find Me
                            </button>
                        </div>
                        
                        <div class="relative rounded-xl overflow-hidden border-2 border-slate-200">
                            <!-- Center Marker Overlay -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                <div class="text-4xl drop-shadow-md pb-8">📍</div>
                            </div>
                            <div id="pickup_map" class="map-container"></div>
                        </div>
                        
                        <input type="hidden" name="latitude" id="pickup_lat">
                        <input type="hidden" name="longitude" id="pickup_lng">
                        <div class="mt-2 text-xs font-mono text-slate-400 text-right" id="pickup_coords_display">Waiting for map...</div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Pickup Address</label>
                        <textarea name="pickup_address" id="pickup_address" required rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="House no, Street name, City"></textarea>
                    </div>
                </div>

                <!-- 3. Delivery Details -->
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span> 
                    Return Delivery
                </h3>
                <div class="mb-8 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_preference" value="deliver_to_address" class="custom-radio sr-only" checked onchange="toggleDeliverySection(true)">
                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                                    <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">Deliver to me</div>
                                    <div class="text-xs text-slate-500">Rider will bring it back</div>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="delivery_preference" value="pickup_from_branch" class="custom-radio sr-only" onchange="toggleDeliverySection(false)">
                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                                    <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">I will pick up</div>
                                    <div class="text-xs text-slate-500">Collect from branch</div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div id="delivery_address_section" class="space-y-6">
                        <label class="flex items-center gap-3 cursor-pointer p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                            <input type="checkbox" name="same_as_pickup" id="same_as_pickup" value="1" checked class="w-5 h-5 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <span class="font-bold text-sm text-slate-700">Same as pickup address</span>
                        </label>

                        <div id="custom_delivery_map_section" class="hidden space-y-6 mt-4 pt-4 border-t border-slate-200">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Pin delivery location on map</label>
                                <div class="relative rounded-xl overflow-hidden border-2 border-slate-200">
                                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[400] pointer-events-none">
                                        <div class="text-4xl drop-shadow-md pb-8">📍</div>
                                    </div>
                                    <div id="delivery_map" class="map-container"></div>
                                </div>
                                <input type="hidden" name="delivery_lat" id="delivery_lat">
                                <input type="hidden" name="delivery_lng" id="delivery_lng">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Delivery Address</label>
                                <textarea name="delivery_address" id="delivery_address" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="House no, Street name, City"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Payment Details -->
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 w-8 h-8 rounded-full flex items-center justify-center text-sm">4</span> 
                    Payment Options
                </h3>
                <div class="mb-8 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <label class="block text-sm font-bold text-slate-700 mb-3">When would you like to pay?</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_timing" value="pay_now" class="custom-radio sr-only" checked>
                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                                    <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                </div>
                                <div class="font-bold text-slate-800">Pay Now</div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_timing" value="pay_on_delivery" class="custom-radio sr-only">
                            <div class="p-4 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex items-center justify-center">
                                    <div class="radio-dot w-2.5 h-2.5 rounded-full"></div>
                                </div>
                                <div class="font-bold text-slate-800">Pay on Delivery / Pickup</div>
                            </div>
                        </label>
                    </div>

                    <label class="block text-sm font-bold text-slate-700 mb-3">Preferred Payment Method</label>
                    <div id="payment_methods_container" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="text-sm text-slate-500 font-medium italic">Please select a branch first...</div>
                    </div>
                </div>

                <!-- 5. Additional Information -->
                <h3 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
                    <span class="bg-slate-200 text-slate-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">5</span> 
                    Additional Details
                </h3>
                <div class="mb-8 p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">What are we picking up? (Optional)</label>
                        <textarea name="item_description" rows="2" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="E.g., 5 shirts, 2 trousers, 1 bedsheet"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Additional Notes (Optional)</label>
                        <input type="text" name="notes" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium" placeholder="Any instructions for the rider?">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-lg py-4 px-6 rounded-2xl hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    Place Order & Schedule Pickup
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Payment Settings passed from backend
    const paymentSettings = @json($paymentSettings);
    
    document.addEventListener('DOMContentLoaded', function() {
        // --- MAPS INITIALIZATION ---
        // CartoDB Voyager tiles (Modern, clean UI)
        const tileUrl = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';
        const tileAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';

        let defaultLat = 6.9271;
        let defaultLng = 79.8612;
        let zoomLevel = 15;

        // Pickup Map (Center-fixed marker pattern)
        const pickupMap = L.map('pickup_map', { zoomControl: false }).setView([defaultLat, defaultLng], zoomLevel);
        L.control.zoom({ position: 'bottomright' }).addTo(pickupMap);
        L.tileLayer(tileUrl, { maxZoom: 19, attribution: tileAttribution }).addTo(pickupMap);

        function updatePickupCoords() {
            const center = pickupMap.getCenter();
            document.getElementById('pickup_lat').value = center.lat.toFixed(7);
            document.getElementById('pickup_lng').value = center.lng.toFixed(7);
            document.getElementById('pickup_coords_display').innerText = `${center.lat.toFixed(6)}, ${center.lng.toFixed(6)}`;
        }
        
        pickupMap.on('move', updatePickupCoords);
        pickupMap.on('moveend', updatePickupCoords);
        updatePickupCoords(); // initial set

        // Delivery Map
        const deliveryMap = L.map('delivery_map', { zoomControl: false }).setView([defaultLat, defaultLng], zoomLevel);
        L.control.zoom({ position: 'bottomright' }).addTo(deliveryMap);
        L.tileLayer(tileUrl, { maxZoom: 19, attribution: tileAttribution }).addTo(deliveryMap);

        function updateDeliveryCoords() {
            const center = deliveryMap.getCenter();
            document.getElementById('delivery_lat').value = center.lat.toFixed(7);
            document.getElementById('delivery_lng').value = center.lng.toFixed(7);
        }
        
        deliveryMap.on('move', updateDeliveryCoords);
        deliveryMap.on('moveend', updateDeliveryCoords);
        updateDeliveryCoords();

        // Fix map rendering issues when unhiding
        function invalidateDeliveryMap() {
            setTimeout(() => { deliveryMap.invalidateSize(); }, 100);
        }

        // --- DELIVERY TOGGLES ---
        const sameAsPickupCheckbox = document.getElementById('same_as_pickup');
        const customDeliverySection = document.getElementById('custom_delivery_map_section');
        const deliveryAddressSection = document.getElementById('delivery_address_section');

        window.toggleDeliverySection = function(show) {
            if (show) {
                deliveryAddressSection.classList.remove('hidden');
            } else {
                deliveryAddressSection.classList.add('hidden');
            }
        };

        sameAsPickupCheckbox.addEventListener('change', function() {
            if (this.checked) {
                customDeliverySection.classList.add('hidden');
                document.getElementById('delivery_address').removeAttribute('required');
            } else {
                customDeliverySection.classList.remove('hidden');
                document.getElementById('delivery_address').setAttribute('required', 'required');
                
                // Copy current pickup map center to delivery map as a starting point
                const pCenter = pickupMap.getCenter();
                deliveryMap.setView(pCenter, pickupMap.getZoom());
                invalidateDeliveryMap();
            }
        });

        // --- GEOLOCATION ---
        document.getElementById('btn-find-me').addEventListener('click', function() {
            const btn = this;
            if (navigator.geolocation) {
                btn.innerHTML = "⏳ Locating...";
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        pickupMap.setView([lat, lng], 17);
                        btn.innerHTML = "✅ Found You!";
                        setTimeout(() => btn.innerHTML = "📍 Find Me", 3000);
                    },
                    function() {
                        alert("Could not get your location. Please check permissions.");
                        btn.innerHTML = "📍 Find Me";
                    }
                );
            }
        });

        // --- DYNAMIC PAYMENT METHODS ---
        const branchSelector = document.getElementById('branch_selector');
        const paymentContainer = document.getElementById('payment_methods_container');

        function updatePaymentMethods() {
            const branchId = branchSelector.value;
            paymentContainer.innerHTML = '';

            if (!branchId || !paymentSettings[branchId]) {
                paymentContainer.innerHTML = '<div class="col-span-2 text-sm text-slate-500 font-medium italic">No payment methods available for this branch. Will pay in cash.</div><input type="hidden" name="payment_method" value="cash">';
                return;
            }

            const branchSettings = paymentSettings[branchId];
            let html = '';
            let first = true;

            // Iterate over object
            Object.values(branchSettings).forEach(setting => {
                if(setting.is_enabled) {
                    const checkedStr = first ? 'checked' : '';
                    first = false;
                    html += `
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_method" value="${setting.method}" class="custom-radio sr-only" ${checkedStr} required>
                            <div class="p-3 border-2 border-slate-200 rounded-xl bg-white hover:border-blue-300 transition-all flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-300 flex-shrink-0 flex items-center justify-center">
                                    <div class="radio-dot w-2 h-2 rounded-full"></div>
                                </div>
                                <div class="font-bold text-slate-700 text-sm truncate">${setting.display_name}</div>
                            </div>
                        </label>
                    `;
                }
            });

            if (html === '') {
                html = '<div class="col-span-2 text-sm text-slate-500 font-medium italic">No payment methods configured. Defaulting to Cash.</div><input type="hidden" name="payment_method" value="cash">';
            }

            paymentContainer.innerHTML = html;
        }

        branchSelector.addEventListener('change', updatePaymentMethods);
        
        // Trigger on load if a branch is already selected
        if (branchSelector.value) {
            updatePaymentMethods();
        }
    });
</script>

</body>
</html>
