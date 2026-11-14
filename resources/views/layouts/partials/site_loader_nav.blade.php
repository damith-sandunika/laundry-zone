<!-- Welcome Loader -->
<div id="welcome-loader" class="fixed inset-0 z-[9999] liquid-bg flex flex-col items-center justify-center overflow-hidden transition-opacity duration-700">
    <div class="absolute top-0 left-0 w-[200%] h-[150px] overflow-hidden opacity-40 pointer-events-none" style="-webkit-mask-image: linear-gradient(to bottom, black 30%, transparent 95%); mask-image: linear-gradient(to bottom, black 30%, transparent 95%);">
        <div class="wave-layer wave-1"></div>
        <div class="wave-layer wave-2"></div>
    </div>
    <div class="bubbles-container absolute inset-0 pointer-events-none"></div>
    <div class="relative z-10 flex flex-col items-center animate-pulse-slow">
        <img src="{{ asset('images/laundry_main_logo.png') }}" alt="{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}" class="h-24 w-auto mb-6 bg-white/60 p-2 rounded-3xl border border-white/20 backdrop-blur-md shadow-2xl">
        <h1 class="text-4xl font-black text-white tracking-wider drop-shadow-lg">{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</h1>
        <p class="text-blue-100 mt-2 font-bold tracking-[0.2em] text-sm uppercase">Premium Care</p>
    </div>
    <div class="relative z-10 mt-16 w-72">
        <div class="flex justify-between text-xs font-bold text-white mb-3 uppercase tracking-wider">
            <span>Loading Experience</span>
            <span id="loader-percentage" class="text-white">0%</span>
        </div>
        <div class="w-full h-2 bg-blue-950/50 rounded-full overflow-hidden shadow-inner">
            <div id="loader-progress" class="h-full bg-white rounded-full w-0 transition-all duration-300 ease-out relative shadow-[0_0_15px_rgba(255,255,255,0.8)]"></div>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/laundry_site_logo.png') }}" alt="{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} Logo" class="h-10 w-auto">
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.login') }}" class="text-slate-600 font-semibold hover:text-blue-600 transition text-sm hidden sm:block">My Account</a>
                <a href="{{ route('customer.login') }}" class="bg-blue-600 text-white px-5 py-2 rounded-full font-bold hover:bg-blue-700 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                    Book Now <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

