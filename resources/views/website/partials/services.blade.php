    <div id="services">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-2">
            <div class="text-center mb-4">
                <span class="bg-blue-100 text-blue-700 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">What We Offer</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-3">Our Services</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base font-medium">Select a category below to explore services, prices, and build your order.</p>
                <div class="w-16 h-1 bg-accent mx-auto rounded-full mt-4"></div>
            </div>
        </div>

        <x-booking-wizard 
            :categories="$categories"
            :minimumOrderLimit="$minimumOrderLimit"
            :deliveryRanges="$deliveryRanges"
            :workshops="$workshops"
            :turnaroundTypes="$turnaroundTypes"
            :extras="$extras"
            :shopLat="$shopLat"
            :shopLng="$shopLng"
            :pickupTimePeriods="$pickupTimePeriods"
            :paymentMethods="$paymentMethods"
            :isHomepage="true"
            :isCentralized="$isCentralized"
        />
    </div>