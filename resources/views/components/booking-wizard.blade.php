@props([
    'categories',
    'minimumOrderLimit',
    'deliveryRanges',
    'workshops',
    'turnaroundTypes',
    'extras',
    'shopLat',
    'shopLng',
    'pickupTimePeriods',
    'paymentMethods' => collect(),
    'isHomepage' => false,
    'isCentralized' => true,
])


    <!-- Google Maps / Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        .map-container { height: 380px; width: 100%; border-radius: 1rem; z-index: 10; }
        @media (max-width: 640px) {
            .map-container { height: 240px; }
        }
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

@include('components.wizard.scripts')

    <div id="booking-wizard-container" x-data="bookingWizard({{ Js::from($categories) }}, {{ auth('customer')->check() ? 'true' : 'false' }}, {{ Js::from(auth('customer')->user()) }}, {{ $minimumOrderLimit }}, {{ Js::from($deliveryRanges) }}, {{ Js::from($workshops) }}, {{ Js::from($turnaroundTypes) }}, {{ Js::from($extras) }}, {{ $shopLat }}, {{ $shopLng }}, {{ Js::from($pickupTimePeriods) }}, {{ $isCentralized ? 'true' : 'false' }}, {{ $isHomepage ? 'true' : 'false' }}, {{ Js::from($paymentMethods) }})" x-init="initData()">
        
        @if(!$isHomepage)
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
                              x-text="viewLabels[i-1]"></span>
                    </div>
                </template>
            </div>
        </div>
        @endif


        <form action="{{ route('customer.booking.store') }}" method="POST" id="bookingForm" class="mt-10 sm:mt-16 lg:w-3/4 mx-auto pb-4 sm:pb-8" enctype="multipart/form-data">
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

            
@include('components.wizard.step-services')

@include('components.wizard.step-account')

@include('components.wizard.step-pickup')

@include('components.wizard.step-delivery')

        </form>
    </div>

