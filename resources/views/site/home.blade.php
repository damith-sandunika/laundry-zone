<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Zone - Premium Laundry Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #fcfdfd; }
        .text-brand { color: #1e3a8a; } /* dark blue */
        .text-accent { color: #2563eb; } /* bright blue */
        .bg-brand { background-color: #1e3a8a; }
        .bg-accent { background-color: #2563eb; }
        .hero-pattern {
            background-color: #f0fdf4;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%232563eb' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .blob-bg {
            position: absolute;
            z-index: 0;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-radius: 50%;
            filter: blur(40px);
        }
        .hero-img-container {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="antialiased text-gray-800 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden">

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <div class="flex-shrink-0 flex items-center">
                <img src="{{ asset('logos/1786348129_admin_logo.png') }}" alt="Laundry Zone" class="h-10 w-auto mr-2">
            </div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-8 items-center font-medium text-sm">
                <a href="#" class="text-blue-600 border-b-2 border-blue-600 pb-1">Home</a>
                <a href="#services" class="text-slate-600 hover:text-blue-600 transition">Services</a>
                <a href="#pricing" class="text-slate-600 hover:text-blue-600 transition">Pricing</a>
                <a href="#how-it-works" class="text-slate-600 hover:text-blue-600 transition">How It Works</a>
                <a href="#testimonials" class="text-slate-600 hover:text-blue-600 transition">Testimonials</a>
                <a href="#contact" class="text-slate-600 hover:text-blue-600 transition">Contact</a>
            </div>

            <div class="hidden md:flex items-center space-x-4">
                <a href="{{ route('login') }}" class="text-slate-600 font-medium hover:text-blue-600 transition text-sm">Sign In</a>
                <a href="{{ route('booking.page') }}" class="bg-blue-600 text-white px-6 py-2.5 rounded-full font-bold hover:bg-blue-700 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                    Book Now <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" style="display:none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" style="display:none" class="md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg">
        <div class="px-4 pt-2 pb-6 space-y-1">
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 bg-blue-50">Home</a>
            <a href="#services" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Services</a>
            <a href="#pricing" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Pricing</a>
            <a href="#how-it-works" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">How It Works</a>
            <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:bg-gray-50">Contact</a>
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col gap-3">
                <a href="{{ route('login') }}" class="text-center text-slate-600 font-medium py-2">Sign In</a>
                <a href="{{ route('booking.page') }}" class="bg-blue-600 text-white px-5 py-3 text-center rounded-xl font-bold">Book Now</a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="relative pt-12 pb-20 lg:pt-20 lg:pb-28 overflow-hidden bg-gradient-to-br from-blue-50/50 to-white">
    <div class="blob-bg w-[600px] h-[600px] top-[-100px] right-[-200px]"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-8">
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-5xl lg:text-7xl font-extrabold text-brand leading-tight mb-6 tracking-tight">
                    Fresh Clothes, <br>
                    <span class="text-accent">Better Life</span>
                </h1>
                <p class="text-lg text-slate-600 mb-10 max-w-lg mx-auto lg:mx-0">
                    Professional laundry & dry cleaning services delivered with care right to your door.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start mb-12">
                    <a href="{{ route('booking.page') }}" class="bg-accent text-white px-8 py-4 rounded-full font-bold text-lg shadow-lg shadow-blue-200 hover:shadow-xl hover:-translate-y-1 hover:bg-blue-700 transition-all flex items-center justify-center gap-3">
                        <i class="fa-solid fa-truck-fast"></i> Book a Pickup
                    </a>
                    <a href="#services" class="bg-white text-accent border border-blue-100 px-8 py-4 rounded-full font-bold text-lg hover:bg-blue-50 transition-all flex items-center justify-center gap-3">
                        Our Services <i class="fa-solid fa-boxes-stacked"></i>
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row gap-6 justify-center lg:justify-start text-left">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Free Pickup & Delivery</p>
                            <p class="text-xs text-slate-500">Hassle-free service</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-leaf"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Eco-Friendly Products</p>
                            <p class="text-xs text-slate-500">Safe for you & planet</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-slate-800">Premium Quality Care</p>
                            <p class="text-xs text-slate-500">Best care for clothes</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex-1 hero-img-container w-full mt-10 lg:mt-0">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500 rounded-[3rem] rotate-3 opacity-10"></div>
                    <img src="{{ asset('images/hero_laundry.png') }}" alt="Fresh Laundry" class="relative z-10 w-full h-64 md:h-auto object-cover rounded-[3rem] shadow-2xl border-4 border-white">
                    
                    <!-- Floating badge -->
                    <div class="absolute -left-4 md:-left-8 top-1/4 bg-white p-3 md:p-4 rounded-2xl shadow-xl z-20 flex items-center gap-3 md:gap-4 animate-bounce" style="animation-duration: 3s;">
                        <div class="bg-blue-600 text-white w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-xl text-lg md:text-xl">
                            <i class="fa-solid fa-droplet"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800 leading-tight text-sm md:text-base">100%</p>
                            <p class="text-[10px] md:text-xs text-slate-500">Clean Guarantee</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-20 bg-white" x-data="{ 
    activeCategory: {{ $categories->first()->id ?? 0 }},
    categories: {{ Js::from($categories) }}
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">What We Offer</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Our Services</h2>
            <div class="w-16 h-1 bg-accent mx-auto rounded-full"></div>
        </div>

        <!-- Categories Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-12">
            @foreach($categories as $category)
            <button @click="activeCategory = {{ $category->id }}" 
                :class="activeCategory === {{ $category->id }} ? 'bg-accent text-white shadow-md' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200'"
                class="px-6 py-3 rounded-full font-semibold transition-all duration-300 flex items-center gap-2">
                @if($category->name == 'Wash & Fold') <i class="fa-solid fa-soap"></i>
                @elseif($category->name == 'Dry Cleaning') <i class="fa-solid fa-shirt"></i>
                @elseif($category->name == 'Ironing') <i class="fa-solid fa-fire-burner"></i>
                @else <i class="fa-solid fa-layer-group"></i> @endif
                {{ $category->name }}
            </button>
            @endforeach
        </div>

        <!-- Services Grid -->
        <div>
            @foreach($categories as $category)
            <div x-show="activeCategory === {{ $category->id }}" x-transition.opacity style="display:none;" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($category->services as $service)
                <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center group cursor-pointer">
                    <div class="w-20 h-20 mx-auto bg-blue-50 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-accent group-hover:text-white transition-colors">
                        @if(str_contains(strtolower($service->name), 'wash'))
                            <i class="fa-solid fa-jug-detergent text-3xl text-accent group-hover:text-white transition-colors"></i>
                        @elseif(str_contains(strtolower($service->name), 'iron'))
                            <i class="fa-solid fa-fire-burner text-3xl text-accent group-hover:text-white transition-colors"></i>
                        @elseif(str_contains(strtolower($service->name), 'dry'))
                            <i class="fa-solid fa-shirt text-3xl text-accent group-hover:text-white transition-colors"></i>
                        @else
                            <i class="fa-solid fa-hands-bubbles text-3xl text-accent group-hover:text-white transition-colors"></i>
                        @endif
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $service->name }}</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ $service->description ?? 'Professional care for your clothes.' }}</p>
                    <div class="w-10 h-1 bg-blue-200 mx-auto rounded-full group-hover:bg-accent group-hover:w-16 transition-all duration-300"></div>
                </div>
                @endforeach
                @if($category->services->isEmpty())
                    <div class="col-span-full text-center text-slate-500 py-10">No services found in this category.</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-20 bg-slate-50 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-100 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <span class="bg-blue-100 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Affordable & Transparent</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Simple & Transparent Pricing</h2>
            <p class="text-slate-500 max-w-2xl mx-auto">Choose a package that best fits your laundry needs. Save more with our bundle deals.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center max-w-6xl mx-auto">
            @foreach($packages->take(3) as $index => $package)
            <div class="bg-white rounded-3xl p-8 border border-slate-100 {{ $index === 1 ? 'shadow-2xl ring-4 ring-blue-500/20 md:-translate-y-4 scale-105 relative' : 'shadow-lg hover:shadow-xl' }} transition-all">
                @if($index === 1)
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <span class="bg-accent text-white text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-md">Most Popular</span>
                </div>
                @endif
                <h3 class="text-2xl font-bold text-center text-brand mb-2">{{ $package->name }}</h3>
                <div class="text-center mb-6">
                    <span class="text-sm text-slate-500 font-medium">From</span><br>
                    <span class="text-4xl font-extrabold text-slate-800">Rs. {{ number_format($package->price, 0) }}</span>
                </div>
                
                <ul class="space-y-4 mb-8">
                    @foreach($package->services as $ps)
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <i class="fa-solid fa-check text-accent bg-blue-50 w-6 h-6 rounded-full flex items-center justify-center text-xs"></i>
                        {{ $ps->serviceType->name ?? 'Service' }} <span class="text-slate-400 text-xs ml-auto">x{{ $ps->total_quantity }}</span>
                    </li>
                    @endforeach
                    <li class="flex items-center gap-3 text-sm text-slate-600">
                        <i class="fa-solid fa-check text-accent bg-blue-50 w-6 h-6 rounded-full flex items-center justify-center text-xs"></i>
                        Free Delivery
                    </li>
                </ul>
                
                <a href="{{ route('booking.page') }}" class="block w-full text-center py-3 rounded-xl font-bold transition-all {{ $index === 1 ? 'bg-accent text-white hover:bg-blue-700 shadow-lg' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
                    Book Now
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Easy Steps</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">How It Works</h2>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center relative">
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-blue-100 -translate-y-1/2 z-0"></div>
            
            <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                <div class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                    <i class="fa-regular fa-calendar-check text-4xl text-accent"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">1. Book</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">Schedule a pickup at your convenience.</p>
            </div>
            
            <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-2xl"></i></div>

            <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                <div class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                    <i class="fa-solid fa-truck-pickup text-4xl text-accent"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">2. Pickup</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">We collect laundry from your doorstep.</p>
            </div>

            <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-2xl"></i></div>

            <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                <div class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                    <i class="fa-solid fa-hands-bubbles text-4xl text-accent"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">3. Clean</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">Clothes are cleaned with premium products.</p>
            </div>

            <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-2xl"></i></div>

            <div class="relative z-10 bg-white p-4 text-center group">
                <div class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                    <i class="fa-solid fa-box-open text-4xl text-accent"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-800">4. Deliver</h3>
                <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">We deliver fresh, clean clothes back to you.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us & Features -->
<section class="py-20 bg-slate-50 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12 items-center">
            <div class="flex-1">
                <h2 class="text-3xl md:text-4xl font-extrabold text-brand mb-8">Why Choose Laundry Zone?</h2>
                <div class="grid grid-cols-2 gap-8">
                    <div class="text-center lg:text-left">
                        <div class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2">Experienced Team</h4>
                        <p class="text-sm text-slate-500">Trained professionals with years of experience.</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                            <i class="fa-solid fa-cogs"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2">Advanced Equipment</h4>
                        <p class="text-sm text-slate-500">Modern machines & safe cleaning methods.</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                            <i class="fa-solid fa-shield-virus"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2">Hygienic Cleaning</h4>
                        <p class="text-sm text-slate-500">Deep cleaning for a healthier life.</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                            <i class="fa-solid fa-thumbs-up"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 mb-2">100% Satisfaction</h4>
                        <p class="text-sm text-slate-500">We ensure you get the best service.</p>
                    </div>
                </div>
            </div>
            <div class="flex-1 flex justify-center">
                <!-- A nice stacked towels image -->
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-600 rounded-[2rem] rotate-6 opacity-10"></div>
                    <img src="{{ asset('images/choose_laundry.png') }}" alt="Clean Towels" class="relative z-10 rounded-[2rem] shadow-2xl object-cover h-[400px] w-[500px]">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">What Our Customers Say</span>
            <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Testimonials</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <img src="https://i.pravatar.cc/150?img=5" alt="User" class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                    <div>
                        <h4 class="font-bold text-slate-800">Nirosha W.</h4>
                        <div class="flex text-yellow-400 text-xs">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-slate-600 relative z-10">"Amazing service! My clothes came back so fresh and perfectly folded. Saves me so much time every week."</p>
            </div>
            
            <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <img src="https://i.pravatar.cc/150?img=11" alt="User" class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                    <div>
                        <h4 class="font-bold text-slate-800">Gayan P.</h4>
                        <div class="flex text-yellow-400 text-xs">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                    </div>
                </div>
                <p class="text-slate-600 relative z-10">"Very reliable pickup and delivery. Their dry cleaning removed a tough stain from my suit. Highly recommended!"</p>
            </div>

            <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <img src="https://i.pravatar.cc/150?img=9" alt="User" class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                    <div>
                        <h4 class="font-bold text-slate-800">Tharindi S.</h4>
                        <div class="flex text-yellow-400 text-xs">
                            <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-slate-600 relative z-10">"Great quality ironing. My clothes look brand new every time. The booking process is extremely easy."</p>
            </div>
        </div>
    </div>
</section>

<!-- App Promo -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
    <div class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-[3rem] p-10 lg:p-16 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center gap-10">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-black/10 rounded-full blur-xl"></div>
        
        <div class="flex-1 relative z-10 text-center md:text-left">
            <h2 class="text-3xl md:text-5xl font-extrabold mb-4">Laundry at Your Fingertips</h2>
            <p class="text-blue-100 text-lg mb-8 max-w-lg">Book, track & manage your laundry easily from your phone. Download our app today!</p>
            <div class="flex flex-wrap justify-center md:justify-start gap-4">
                <a href="#" class="bg-black hover:bg-gray-800 transition rounded-xl px-5 py-2.5 flex items-center gap-3">
                    <i class="fa-brands fa-google-play text-2xl"></i>
                    <div class="text-left">
                        <p class="text-[10px] uppercase tracking-wider leading-none">GET IT ON</p>
                        <p class="font-bold leading-none mt-1">Google Play</p>
                    </div>
                </a>
                <a href="#" class="bg-black hover:bg-gray-800 transition rounded-xl px-5 py-2.5 flex items-center gap-3">
                    <i class="fa-brands fa-apple text-3xl mb-1"></i>
                    <div class="text-left">
                        <p class="text-[10px] uppercase tracking-wider leading-none">Download on the</p>
                        <p class="font-bold leading-none mt-1">App Store</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="flex-1 relative z-10 flex justify-center lg:justify-end">
            <!-- App mockup image instead of list -->
            <img src="{{ asset('images/app_mockup.png') }}" alt="App Mockup" class="w-full max-w-[300px] h-auto drop-shadow-2xl rounded-[3rem] transform lg:-translate-y-4 hover:-translate-y-6 transition-transform duration-500">
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-20 bg-white border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Get In Touch</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-brand mt-4">We're Here for You</h2>
        </div>

        <div class="flex flex-col lg:flex-row gap-12 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
            <!-- Contact Info -->
            <div class="flex-1 p-10 lg:p-12 h-[500px] overflow-y-auto custom-scrollbar">
                <div class="space-y-8">
                    @foreach($branches as $b)
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        <h3 class="font-bold text-lg text-brand mb-4 border-b border-slate-200 pb-2"><i class="fa-solid fa-store text-accent mr-2"></i>{{ $b->name }}</h3>
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800 uppercase">Phone</h4>
                                    <p class="text-slate-600 text-sm">{{ $b->phone ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800 uppercase">WhatsApp</h4>
                                    <p class="text-slate-600 text-sm">{{ $b->whatsapp_number ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-xs text-slate-800 uppercase">Address</h4>
                                    <p class="text-slate-600 text-sm">{{ $b->address ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Map Placeholder -->
            <div class="flex-1 bg-slate-100 relative min-h-[400px]">
                <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=800&auto=format&fit=crop" class="w-full h-full object-cover opacity-80 mix-blend-luminosity" alt="Map">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-accent text-5xl drop-shadow-xl animate-bounce">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-[#0f172a] text-slate-300 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 border-b border-slate-700 pb-12 mb-8">
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center mb-6">
                    <span class="text-3xl mr-2 text-white"><i class="fa-solid fa-droplet"></i></span>
                    <span class="font-bold text-2xl text-white">Laundry Zone</span>
                </div>
                <p class="text-sm text-slate-400 mb-6">Professional laundry and dry cleaning services with care and convenience.</p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Quick Links</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Home</a></li>
                    <li><a href="#services" class="hover:text-white transition">Services</a></li>
                    <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
                    <li><a href="#how-it-works" class="hover:text-white transition">How it Works</a></li>
                    <li><a href="#testimonials" class="hover:text-white transition">Testimonials</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Our Services</h4>
                <ul class="space-y-3 text-sm">
                    @foreach($categories->take(5) as $cat)
                    <li><a href="#services" class="hover:text-white transition">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Newsletter</h4>
                <p class="text-sm text-slate-400 mb-4">Subscribe to get special offers and updates.</p>
                <form class="flex">
                    <input type="email" placeholder="Enter your email" class="bg-slate-800 border-none rounded-l-lg px-4 py-2 w-full text-sm focus:ring-1 focus:ring-accent outline-none text-white">
                    <button type="submit" class="bg-accent text-white px-4 py-2 rounded-r-lg hover:bg-blue-500 transition"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
        <div class="text-center text-sm text-slate-500 flex flex-col md:flex-row justify-between items-center gap-4">
            <p>&copy; {{ date('Y') }} Laundry Zone. All Rights Reserved.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-white transition">Privacy Policy</a>
                <a href="#" class="hover:text-white transition">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Smooth Scroll Script -->
<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>
</body>
</html>
