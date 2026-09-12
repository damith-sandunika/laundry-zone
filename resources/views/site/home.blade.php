@extends('layouts.site')

@section('content')
    <!-- Swiper CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <!-- Hero Section with Slides -->
    <section x-data="{ slide: 1, timer: null, direction: 1, maxSlide: 3 }" x-init="timer = setInterval(() => { 
            if (slide === maxSlide) direction = -1;
            else if (slide === 1) direction = 1;
            slide = slide + direction;
         }, 5000)"
        class="relative w-full h-[700px] lg:h-[750px] overflow-hidden bg-slate-900 shadow-inner group">

        <!-- Animated Backgrounds (NO SHADES) -->
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out"
            :class="slide === 1 ? 'opacity-100' : 'opacity-0'">
            <img src="{{ asset('images/1.png') }}" alt="Laundry Slide 1"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[80%_top]">
            <img src="{{ asset('images/m.1.png') }}" alt="Laundry Slide 1 Mobile"
                class="block lg:hidden w-full h-full object-cover object-top">
        </div>
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out"
            :class="slide === 2 ? 'opacity-100' : 'opacity-0'">
            <img src="{{ asset('images/2.png') }}" alt="Laundry Slide 2"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[20%_top]">
            <img src="{{ asset('images/m.2.png') }}" alt="Laundry Slide 2 Mobile"
                class="block lg:hidden w-full h-full object-cover object-top">
        </div>
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out"
            :class="slide === 3 ? 'opacity-100' : 'opacity-0'">
            <img src="{{ asset('images/3.png') }}" alt="Laundry Slide 3"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[80%_top]">
            <img src="{{ asset('images/m.3.png') }}" alt="Laundry Slide 3 Mobile"
                class="block lg:hidden w-full h-full object-cover object-top">
        </div>

        <!-- Texts for Slides -->
        <div class="absolute inset-0 z-10 w-full pt-8 lg:pt-16 pointer-events-none">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full relative pointer-events-none h-full">

                <!-- Slide 1 Text (Extreme Left) -->
                <div
                    class="absolute top-[40%] lg:top-12 inset-x-0 mx-auto lg:mx-0 lg:left-8 px-4 lg:px-0 w-full max-w-full lg:max-w-lg pointer-events-auto flex flex-col items-center lg:items-start text-center lg:text-left">
                    <h1 x-show="slide === 1" x-transition:enter="transition ease-out duration-700 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-[40px] sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
                        style="display: none;">
                        We Clean. <br> <span class="text-accent">You Relax.</span>
                    </h1>

                    <div x-show="slide === 1" x-transition:enter="transition-all ease-out duration-700 delay-300"
                        x-transition:enter-start="opacity-0 w-0" x-transition:enter-end="opacity-100 w-24 lg:w-32"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-1.5 bg-gradient-to-r from-accent to-blue-300 rounded-full mb-1 lg:mb-3"
                        style="display: none;"></div>

                    <p x-show="slide === 1" x-transition:enter="transition ease-out duration-700 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-sm sm:text-base lg:text-lg text-brand mb-1 lg:mb-4 font-bold max-w-md leading-snug drop-shadow-[0_2px_15px_rgba(255,255,255,1)] bg-white/30 lg:bg-transparent backdrop-blur-sm lg:backdrop-blur-none p-2 lg:p-0 rounded-xl"
                        style="display: none;">
                        Professional laundry and dry cleaning with pickup & delivery at your doorstep.
                    </p>

                    <ul x-show="slide === 1" x-transition:enter="transition ease-out duration-700 delay-500"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mb-2 lg:mb-6 grid grid-cols-2 gap-x-2 gap-y-1 lg:flex lg:flex-col lg:gap-0 lg:space-y-4 text-[11px] sm:text-sm lg:text-base font-bold text-slate-800 drop-shadow-md w-full"
                        style="display: none;">
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Eco-friendly Detergents</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Expert Handling</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> 24-Hour Express</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Quality Assured</li>
                    </ul>

                    <div x-show="slide === 1" x-transition:enter="transition ease-out duration-700 delay-700"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-3 lg:flex lg:flex-row lg:gap-5 justify-center lg:justify-start w-full max-w-[280px] lg:max-w-none mx-auto lg:mx-0"
                        style="display: none;">
                        <a href="{{ route('customer.login') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
                    </div>

                    <!-- Mobile Features (Below Buttons) -->
                    <div x-show="slide === 1" x-transition:enter="transition ease-out duration-700 delay-800"
                        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-2 mt-6 w-full max-w-[320px] mx-auto lg:hidden" style="display: none;">
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-truck-fast text-blue-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Pickup & Delivery</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-orange-500"></i><p class="font-bold text-[10px] text-brand leading-tight">48-Hour Service</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-moon text-purple-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Open Till 10 PM</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-shield-virus text-green-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Safe & Hygienic</p></div>
                    </div>
                </div>

                <!-- Slide 2 Text (Extreme Right) -->
                <div
                    class="absolute top-[40%] lg:top-4 inset-x-0 mx-auto lg:mx-0 lg:left-auto lg:right-8 px-4 lg:px-0 w-full max-w-full lg:max-w-lg pointer-events-auto flex flex-col items-center lg:items-end text-center lg:text-right">
                    <h1 x-show="slide === 2" x-transition:enter="transition ease-out duration-700 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-[40px] sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
                        style="display: none;">
                        Premium Fabric <br> <span class="text-accent">Care.</span>
                    </h1>

                    <div x-show="slide === 2" x-transition:enter="transition-all ease-out duration-700 delay-300"
                        x-transition:enter-start="opacity-0 w-0" x-transition:enter-end="opacity-100 w-24 lg:w-32"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-1.5 bg-gradient-to-r lg:bg-gradient-to-l from-accent to-blue-300 rounded-full mb-1 lg:mb-3"
                        style="display: none;"></div>

                    <p x-show="slide === 2" x-transition:enter="transition ease-out duration-700 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-sm sm:text-base lg:text-lg text-brand mb-1 lg:mb-4 font-bold max-w-md leading-snug drop-shadow-[0_2px_15px_rgba(255,255,255,1)] bg-white/30 lg:bg-transparent backdrop-blur-sm lg:backdrop-blur-none p-2 lg:p-0 rounded-xl"
                        style="display: none;">
                        From school uniforms to office wear, shoes to monk robes – we clean it all with care.
                    </p>

                    <ul x-show="slide === 2" x-transition:enter="transition ease-out duration-700 delay-500"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mb-2 lg:mb-6 grid grid-cols-2 gap-x-2 gap-y-1 lg:flex lg:flex-col lg:items-end lg:gap-0 lg:space-y-4 text-[11px] sm:text-sm lg:text-base font-bold text-slate-800 drop-shadow-md w-full lg:text-right"
                        style="display: none;">
                        <li class="flex items-center justify-center lg:justify-end gap-1.5 lg:flex-row-reverse">Tough Stain Removal <i class="fa-solid fa-circle-check text-green-500"></i></li>
                        <li class="flex items-center justify-center lg:justify-end gap-1.5 lg:flex-row-reverse">Fabric Protection <i class="fa-solid fa-circle-check text-green-500"></i></li>
                        <li class="flex items-center justify-center lg:justify-end gap-1.5 lg:flex-row-reverse">Crisp Ironing <i class="fa-solid fa-circle-check text-green-500"></i></li>
                        <li class="flex items-center justify-center lg:justify-end gap-1.5 lg:flex-row-reverse">Color Care <i class="fa-solid fa-circle-check text-green-500"></i></li>
                    </ul>

                    <div x-show="slide === 2" x-transition:enter="transition ease-out duration-700 delay-700"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-3 lg:flex lg:flex-row lg:gap-5 justify-center lg:justify-end w-full max-w-[280px] lg:max-w-none mx-auto lg:mx-0"
                        style="display: none;">
                        <a href="{{ route('customer.login') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
                    </div>

                    <!-- Mobile Features (Below Buttons) -->
                    <div x-show="slide === 2" x-transition:enter="transition ease-out duration-700 delay-800"
                        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-2 mt-6 w-full max-w-[320px] mx-auto lg:hidden" style="display: none;">
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-truck-fast text-blue-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Pickup & Delivery</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-orange-500"></i><p class="font-bold text-[10px] text-brand leading-tight">48-Hour Service</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-moon text-purple-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Open Till 10 PM</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-shield-virus text-green-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Safe & Hygienic</p></div>
                    </div>
                </div>

                <!-- Slide 3 Text (Extreme Left) -->
                <div
                    class="absolute top-[40%] lg:top-12 inset-x-0 mx-auto lg:mx-0 lg:left-8 px-4 lg:px-0 w-full max-w-full lg:max-w-lg pointer-events-auto flex flex-col items-center lg:items-start text-center lg:text-left">
                    <h1 x-show="slide === 3" x-transition:enter="transition ease-out duration-700 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-[40px] sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
                        style="display: none;">
                        Fast & Reliable <br> <span class="text-accent">Delivery.</span>
                    </h1>

                    <div x-show="slide === 3" x-transition:enter="transition-all ease-out duration-700 delay-300"
                        x-transition:enter-start="opacity-0 w-0" x-transition:enter-end="opacity-100 w-24 lg:w-32"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="h-1.5 bg-gradient-to-r from-accent to-blue-300 rounded-full mb-1 lg:mb-3"
                        style="display: none;"></div>

                    <p x-show="slide === 3" x-transition:enter="transition ease-out duration-700 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-sm sm:text-base lg:text-lg text-brand mb-1 lg:mb-4 font-bold max-w-md leading-snug drop-shadow-[0_2px_15px_rgba(255,255,255,1)] bg-white/30 lg:bg-transparent backdrop-blur-sm lg:backdrop-blur-none p-2 lg:p-0 rounded-xl"
                        style="display: none;">
                        We deliver your fresh, clean laundry within 48 hours right to your doorstep.
                    </p>

                    <ul x-show="slide === 3" x-transition:enter="transition ease-out duration-700 delay-500"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="mb-2 lg:mb-6 grid grid-cols-2 gap-x-2 gap-y-1 lg:flex lg:flex-col lg:gap-0 lg:space-y-4 text-[11px] sm:text-sm lg:text-base font-bold text-slate-800 drop-shadow-md w-full"
                        style="display: none;">
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Live Tracking</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Flexible Times</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> Safe Handovers</li>
                        <li class="flex items-center justify-center lg:justify-start gap-1.5"><i class="fa-solid fa-circle-check text-green-500"></i> 24/7 Support</li>
                    </ul>

                    <div x-show="slide === 3" x-transition:enter="transition ease-out duration-700 delay-700"
                        x-transition:enter-start="opacity-0 translate-y-8"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-3 lg:flex lg:flex-row lg:gap-5 justify-center lg:justify-start w-full max-w-[280px] lg:max-w-none mx-auto lg:mx-0"
                        style="display: none;">
                        <a href="{{ route('customer.login') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
                    </div>

                    <!-- Mobile Features (Below Buttons) -->
                    <div x-show="slide === 3" x-transition:enter="transition ease-out duration-700 delay-800"
                        x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                        class="grid grid-cols-2 gap-2 mt-6 w-full max-w-[320px] mx-auto lg:hidden" style="display: none;">
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-truck-fast text-blue-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Pickup & Delivery</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-orange-500"></i><p class="font-bold text-[10px] text-brand leading-tight">48-Hour Service</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-moon text-purple-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Open Till 10 PM</p></div>
                        <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 shadow-sm border border-white flex items-center gap-2"><i class="fa-solid fa-shield-virus text-green-600"></i><p class="font-bold text-[10px] text-brand leading-tight">Safe & Hygienic</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Separate Feature Cards (Always visible on desktop only) -->
        <div class="absolute bottom-2 lg:bottom-10 left-0 right-0 z-20 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 w-full hidden lg:block"
             x-data="{ activeFeature: 1 }" 
             x-init="setInterval(() => { activeFeature = activeFeature === 4 ? 1 : activeFeature + 1 }, 2500)">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-4 w-full">
                <!-- Card 1: Blue & Bounce -->
                <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 sm:p-3 shadow-lg border border-white flex flex-row items-center gap-2 sm:gap-3 transition-all duration-300"
                     :class="activeFeature === 1 ? 'ring-2 ring-blue-400 -translate-y-1' : ''">
                    <div class="text-xl sm:text-2xl text-brand transition-all duration-300"
                         :class="activeFeature === 1 ? 'animate-bounce text-blue-600' : ''">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div class="text-left">
                        <p class="font-extrabold text-[10px] sm:text-[12px] text-brand leading-tight">Pickup & Delivery</p>
                        <p class="text-[8px] sm:text-[9px] text-slate-500 font-medium">Right at your doorstep.</p>
                    </div>
                </div>
                <!-- Card 2: Orange & Spin -->
                <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 sm:p-3 shadow-lg border border-white flex flex-row items-center gap-2 sm:gap-3 transition-all duration-300"
                     :class="activeFeature === 2 ? 'ring-2 ring-orange-400 -translate-y-1' : ''">
                    <div class="text-xl sm:text-2xl text-brand transition-all duration-300 inline-block"
                         :class="activeFeature === 2 ? 'animate-[spin_1s_linear_infinite] text-orange-500' : ''">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div class="text-left">
                        <p class="font-extrabold text-[10px] sm:text-[12px] text-brand leading-tight">48-Hour Service</p>
                        <p class="text-[8px] sm:text-[9px] text-slate-500 font-medium">Fast & reliable delivery.</p>
                    </div>
                </div>
                <!-- Card 3: Purple & Pulse -->
                <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 sm:p-3 shadow-lg border border-white flex flex-row items-center gap-2 sm:gap-3 transition-all duration-300"
                     :class="activeFeature === 3 ? 'ring-2 ring-purple-400 -translate-y-1' : ''">
                    <div class="text-xl sm:text-2xl text-brand transition-all duration-300"
                         :class="activeFeature === 3 ? 'animate-pulse scale-125 text-purple-600' : ''">
                        <i class="fa-solid fa-moon"></i>
                    </div>
                    <div class="text-left">
                        <p class="font-extrabold text-[10px] sm:text-[12px] text-brand leading-tight">Open Till 10 PM</p>
                        <p class="text-[8px] sm:text-[9px] text-slate-500 font-medium">Pickups available late.</p>
                    </div>
                </div>
                <!-- Card 4: Green & Scale/Rotate -->
                <div class="bg-white/95 backdrop-blur-xl rounded-xl p-2 sm:p-3 shadow-lg border border-white flex flex-row items-center gap-2 sm:gap-3 transition-all duration-300"
                     :class="activeFeature === 4 ? 'ring-2 ring-green-400 -translate-y-1' : ''">
                    <div class="text-xl sm:text-2xl text-green-500 transition-all duration-300 inline-block"
                         :class="activeFeature === 4 ? 'scale-125 -rotate-12 text-green-600' : ''">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                    <div class="text-left">
                        <p class="font-extrabold text-[10px] sm:text-[12px] text-brand leading-tight">Safe & Hygienic</p>
                        <p class="text-[8px] sm:text-[9px] text-slate-500 font-medium">Clean you can trust.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider Indicators -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 hidden lg:flex gap-3 z-30">
            <template x-for="i in maxSlide">
                <button @click="slide = i" class="h-2.5 rounded-full transition-all duration-300 shadow-md"
                    :class="slide === i ? 'w-10 bg-accent' : 'w-2.5 bg-white/70 hover:bg-white'"></button>
            </template>
        </div>
    </section>
    <style>
        /* Hide scrollbar for the horizontal scrolling features banner on mobile */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <!-- Services Section -->
    <section id="services" class="pt-12 pb-32 lg:pt-24 lg:pb-44 bg-slate-50/60 relative" x-data="homeServicesHandler({{ Js::from($categories) }})">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <span class="bg-blue-100 text-blue-700 text-xs font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">What We Offer</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-3">Our Services</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base font-medium">Select a category below to explore services, prices, and build your order.</p>
                <div class="w-16 h-1 bg-accent mx-auto rounded-full mt-4"></div>
            </div>

            <!-- Interactive Main Category Tabs / Pills Bar (Fast Switching) -->
            <div class="mb-6 max-w-6xl mx-auto">
                <div class="flex items-center justify-start sm:justify-center gap-2 overflow-x-auto pb-2 scrollbar-hide px-2">
                    <button type="button" @click="goHomeLevel()" class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all cursor-pointer shrink-0 border flex items-center gap-1.5"
                            :class="currentViewLevel === 0 ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                        <i class="fa-solid fa-shapes text-xs"></i> All Categories
                    </button>
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" @click="selectLevel1(cat)" class="px-4 py-2 rounded-full text-xs sm:text-sm font-extrabold transition-all cursor-pointer shrink-0 border flex items-center gap-1.5"
                                :class="selectedMainCategory && selectedMainCategory.id === cat.id ? 'bg-blue-600 text-white border-blue-600 shadow-md scale-105' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'">
                            <span x-text="cat.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Sleek Minimal Navigation Header (when drilled down: Back + 2 Category Tabs) -->
            <div x-show="currentViewLevel > 0" x-transition.opacity class="flex items-center justify-center gap-2 sm:gap-3 mb-8 max-w-4xl mx-auto flex-wrap">
                <!-- Back Button -->
                <button type="button" @click="goBackLevel()" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-extrabold px-3.5 py-2 rounded-xl text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Back
                </button>

                <!-- Separator -->
                <span class="text-slate-300 text-xs font-bold">/</span>

                <!-- Tab 1: Main Category Pill -->
                <button type="button" @click="selectLevel1(selectedMainCategory)" class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-extrabold transition-all cursor-pointer border shadow-sm flex items-center gap-1.5"
                        :class="currentViewLevel === 1 ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'">
                    <i class="fa-solid fa-folder text-[10px]" :class="currentViewLevel === 1 ? 'text-white' : 'text-blue-600'"></i>
                    <span x-text="selectedMainCategory?.name"></span>
                </button>

                <!-- Tab 2: Subcategory Pill (if in level 2 and subcategory selected) -->
                <template x-if="currentViewLevel === 2 && selectedSubcategory">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="text-slate-300 text-xs font-bold">/</span>
                        <div class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-extrabold bg-blue-600 text-white border border-blue-600 shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-tag text-[10px] text-white"></i>
                            <span x-text="selectedSubcategory?.name"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Level 0: Main Categories Grid (Plain clean cards without dark shade, 3 cols on mobile) -->
            <div x-show="currentViewLevel === 0" x-transition:enter="transition ease-out duration-300">
                <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-6 max-w-6xl mx-auto justify-items-center">
                    <template x-for="cat in categories" :key="cat.id">
                        <div @click="selectLevel1(cat)" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-blue-500 hover:-translate-y-1.5 transition-all duration-300 overflow-hidden flex flex-col justify-between cursor-pointer w-full text-center group">
                            <!-- Full Image Container (No dark black shade!) -->
                            <div class="aspect-square w-full relative overflow-hidden bg-slate-100 flex items-center justify-center">
                                <template x-if="cat.image_path">
                                    <img :src="cat.image_path.startsWith('http') ? cat.image_path : '/' + cat.image_path" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :alt="cat.name">
                                </template>
                                <template x-if="!cat.image_path">
                                    <div class="w-full h-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <i class="fa-solid fa-soap text-3xl sm:text-4xl"></i>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Plain Title Bar Underneath -->
                            <div class="p-2 sm:p-3 bg-white border-t border-slate-100 flex items-center justify-center text-center">
                                <h4 class="text-slate-900 font-extrabold text-xs sm:text-base leading-tight group-hover:text-blue-600 transition-colors line-clamp-1 text-center" x-text="cat.name"></h4>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Level 1: Subcategories Grid (Plain clean cards without dark shade, 3 cols on mobile) -->
            <div x-show="currentViewLevel === 1" x-transition:enter="transition ease-out duration-300">
                <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-6 max-w-6xl mx-auto justify-items-center">
                    <template x-for="sub in currentSubcategories" :key="sub.id">
                        <div @click="selectLevel2(sub)" class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-blue-500 hover:-translate-y-1.5 transition-all duration-300 overflow-hidden flex flex-col justify-between cursor-pointer w-full text-center group">
                            <!-- Full Image Container (No dark black shade!) -->
                            <div class="aspect-square w-full relative overflow-hidden bg-slate-100 flex items-center justify-center">
                                <template x-if="sub.image_path">
                                    <img :src="sub.image_path.startsWith('http') ? sub.image_path : '/' + sub.image_path" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :alt="sub.name">
                                </template>
                                <template x-if="!sub.image_path">
                                    <div class="w-full h-full bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                        <i class="fa-solid fa-layer-group text-3xl sm:text-4xl"></i>
                                    </div>
                                </template>
                            </div>

                            <!-- Plain Title Bar Underneath -->
                            <div class="p-2 sm:p-3 bg-white border-t border-slate-100 flex items-center justify-center text-center">
                                <h4 class="text-slate-900 font-extrabold text-xs sm:text-base leading-tight group-hover:text-blue-600 transition-colors line-clamp-1 text-center" x-text="sub.name"></h4>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Level 2: Compact Single-Line Services Bar List -->
            <div x-show="currentViewLevel === 2" x-transition:enter="transition ease-out duration-300">
                <div class="max-w-4xl mx-auto space-y-2.5 sm:space-y-3">
                    <template x-for="service in currentServices" :key="service.id">
                        <div class="bg-white border border-slate-200 hover:border-blue-500 rounded-2xl px-3.5 py-2.5 sm:px-5 sm:py-3.5 shadow-sm hover:shadow-md transition-all flex items-center justify-between gap-3">
                            <!-- Left: Service Name -->
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold text-xs sm:text-base shadow-sm">
                                    <i class="fa-solid fa-jug-detergent"></i>
                                </div>
                                <h5 class="font-extrabold text-slate-900 text-xs sm:text-base truncate" x-text="service.name"></h5>
                            </div>

                            <!-- Center/Right: Unit above Price -->
                            <div class="flex flex-col items-end shrink-0 leading-tight">
                                <span class="text-[10px] sm:text-xs font-extrabold text-slate-400 uppercase tracking-wider" x-text="service.price_type === 'per_kg' ? 'Per KG' : 'Per Item'"></span>
                                <span class="text-xs sm:text-base font-black text-blue-600">LKR <span x-text="parseFloat(service.base_price).toFixed(2)"></span></span>
                            </div>

                            <!-- Right: Add Button -->
                            <button type="button" @click="openQtyModal(service)" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs transition-all flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
                                <span>Add</span>
                                <span class="w-4 h-4 rounded-full bg-white/20 text-white flex items-center justify-center text-[10px] font-black">+</span>
                            </button>
                        </div>
                    </template>
                    <template x-if="currentServices.length === 0">
                        <div class="py-12 text-center text-slate-400 font-bold">
                            No services found in this category.
                        </div>
                    </template>
                </div>
            </div>

            <!-- Quantity Selection Modal -->
            <div x-show="isQtyModalOpen" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center p-4">
                <div x-show="isQtyModalOpen" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="isQtyModalOpen = false"></div>
                <div x-show="isQtyModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden border border-slate-100">
                    <div class="p-6 text-center">
                        <h3 class="text-lg font-extrabold text-slate-800 mb-1" x-text="selectedService?.name"></h3>
                        <p class="text-sm font-bold text-blue-600 mb-6">LKR <span x-text="parseFloat(selectedService?.base_price || 0).toFixed(2)"></span> <span class="text-xs text-slate-400" x-text="selectedService?.price_type === 'per_kg' ? '/ kg' : '/ item'"></span></p>
                        
                        <div class="flex items-center justify-center gap-6">
                            <button type="button" @click="if(serviceQty > 1) serviceQty--" class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-bold transition-colors"><i class="fa-solid fa-minus"></i></button>
                            <div class="text-2xl font-black text-slate-800 w-12 text-center" x-text="serviceQty"></div>
                            <button type="button" @click="serviceQty++" class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 font-bold transition-colors"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-3">
                        <button type="button" @click="isQtyModalOpen = false" class="flex-1 py-2.5 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors text-xs">Cancel</button>
                        <button type="button" @click="confirmAddToCart()" class="flex-1 py-2.5 rounded-xl font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition-colors text-xs">Add to Cart</button>
                    </div>
                </div>
            </div>

            <!-- Fixed Bottom Cart Bar -->
            <div x-show="cart.length > 0" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100" class="fixed bottom-0 left-0 w-full z-40 bg-white border-t border-slate-200 shadow-[0_-8px_30px_rgba(0,0,0,0.12)]">
                <!-- Expandable Cart List -->
                <div x-show="isCartExpanded" class="bg-slate-50 border-b border-slate-200 max-h-[40vh] overflow-y-auto custom-scrollbar">
                    <div class="max-w-4xl mx-auto p-4 space-y-2">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 text-center">Your Cart Items</h4>
                        <template x-for="(item, index) in cart" :key="item.cart_key">
                            <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-sm">
                                <div class="flex-1 min-w-0 pr-4">
                                    <div class="font-bold text-sm text-slate-800 truncate" x-text="item.name"></div>
                                    <div class="text-xs font-semibold text-blue-600 mt-0.5">LKR <span x-text="item.base_price.toFixed(2)"></span> x <span x-text="item.qty"></span> = LKR <span x-text="(item.base_price * item.qty).toFixed(2)"></span></div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="flex items-center bg-slate-50 rounded-lg p-1 border border-slate-200">
                                        <button type="button" @click="updateCartQty(index, -1)" class="w-7 h-7 flex items-center justify-center rounded-md bg-white text-slate-600 shadow-sm hover:text-red-500 border border-slate-200"><i class="fa-solid fa-minus text-[10px]"></i></button>
                                        <span class="w-8 text-center text-xs font-bold text-slate-800" x-text="item.qty"></span>
                                        <button type="button" @click="updateCartQty(index, 1)" class="w-7 h-7 flex items-center justify-center rounded-md bg-white text-slate-600 shadow-sm hover:text-blue-600 border border-slate-200"><i class="fa-solid fa-plus text-[10px]"></i></button>
                                    </div>
                                    <button type="button" @click="removeFromCartByIndex(index)" class="text-red-400 hover:text-red-600 p-2 transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Main Cart Bar -->
                <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5 cursor-pointer" @click="isCartExpanded = !isCartExpanded">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center relative shadow-sm font-bold">
                            <i class="fa-solid fa-basket-shopping text-lg"></i>
                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-blue-600 text-white text-[10px] font-black rounded-full flex items-center justify-center shadow-md" x-text="cart.reduce((sum, i) => sum + i.qty, 0)"></span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-500 flex items-center gap-1">Cart Total <i class="fa-solid text-[10px] ml-1 transition-transform" :class="isCartExpanded ? 'fa-chevron-down' : 'fa-chevron-up'"></i></div>
                            <div class="text-lg font-black text-slate-900">LKR <span x-text="baseCartTotal.toFixed(2)"></span></div>
                        </div>
                    </div>
                    
                    <button type="button" @click="checkoutFromHomepage()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 sm:px-8 py-3 rounded-xl font-extrabold transition-all shadow-lg shadow-blue-600/30 flex items-center gap-2 text-sm sm:text-base cursor-pointer">
                        <span>Checkout Now</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('homeServicesHandler', (categoriesData) => ({
                categories: categoriesData || [],
                selectedMainCategory: null,
                selectedSubcategory: null,
                currentSubcategories: [],
                currentServices: [],
                currentViewLevel: 0, // 0: Categories, 1: Subcategories, 2: Services
                
                cart: [],
                isCartExpanded: false,
                isQtyModalOpen: false,
                selectedService: null,
                serviceQty: 1,

                getServiceCount(cat) {
                    let count = cat.services ? Object.keys(cat.services).length : 0;
                    if (cat.children) {
                        let children = Array.isArray(cat.children) ? cat.children : Object.values(cat.children);
                        children.forEach(child => {
                            if (child.services) {
                                count += Object.keys(child.services).length;
                            }
                        });
                    }
                    return count;
                },

                scrollToServices() {
                    this.$nextTick(() => {
                        const sec = document.getElementById('services');
                        if (sec) {
                            sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                },

                selectLevel1(cat) {
                    this.selectedMainCategory = cat;
                    let childrenArray = cat.children ? (Array.isArray(cat.children) ? cat.children : Object.values(cat.children)) : [];
                    
                    if (childrenArray.length > 0) {
                        this.currentSubcategories = childrenArray;
                        this.currentViewLevel = 1;
                    } else {
                        this.currentSubcategories = [];
                        this.selectedSubcategory = null;
                        this.currentServices = cat.services ? (Array.isArray(cat.services) ? cat.services : Object.values(cat.services)) : [];
                        this.currentViewLevel = 2;
                    }
                    this.scrollToServices();
                },

                selectLevel2(sub) {
                    this.selectedSubcategory = sub;
                    this.currentServices = sub.services ? (Array.isArray(sub.services) ? sub.services : Object.values(sub.services)) : [];
                    this.currentViewLevel = 2;
                    this.scrollToServices();
                },

                goHomeLevel() {
                    this.currentViewLevel = 0;
                    this.selectedMainCategory = null;
                    this.selectedSubcategory = null;
                    this.currentSubcategories = [];
                    this.currentServices = [];
                    this.scrollToServices();
                },

                goBackLevel() {
                    if (this.currentViewLevel === 2) {
                        if (this.currentSubcategories.length > 0) {
                            this.currentViewLevel = 1;
                            this.selectedSubcategory = null;
                        } else {
                            this.currentViewLevel = 0;
                            this.selectedMainCategory = null;
                        }
                    } else if (this.currentViewLevel === 1) {
                        this.currentViewLevel = 0;
                        this.selectedMainCategory = null;
                    }
                    this.scrollToServices();
                },

                openQtyModal(service) {
                    this.selectedService = service;
                    this.serviceQty = 1;
                    this.isQtyModalOpen = true;
                },

                confirmAddToCart() {
                    if (!this.selectedService) return;
                    
                    let cartItem = {
                        id: this.selectedService.id,
                        service_type_id: this.selectedService.id,
                        name: this.selectedService.name,
                        base_price: parseFloat(this.selectedService.base_price),
                        price_type: this.selectedService.price_type,
                        qty: this.serviceQty,
                        turnaround_type_id: null,
                        selected_extras: [],
                        cart_key: Date.now() + Math.random()
                    };
                    
                    let existing = this.cart.find(i => i.id === cartItem.id);
                    if (existing) {
                        existing.qty += cartItem.qty;
                    } else {
                        this.cart.push(cartItem);
                    }
                    
                    this.isQtyModalOpen = false;
                },

                updateCartQty(index, amount) {
                    if (this.cart[index].qty + amount > 0) {
                        this.cart[index].qty += amount;
                    }
                },

                removeFromCartByIndex(index) {
                    this.cart.splice(index, 1);
                    if (this.cart.length === 0) {
                        this.isCartExpanded = false;
                    }
                },

                get baseCartTotal() {
                    return this.cart.reduce((total, item) => total + (parseFloat(item.base_price) * item.qty), 0);
                },

                checkoutFromHomepage() {
                    if (this.cart.length === 0) return;
                    localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    window.location.href = "{{ route('customer.booking') }}";
                }
            }));
        });
    </script>

    <!-- Promotions Section -->
    <section id="promotions" class="py-16 lg:py-24 bg-slate-50 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-200 rounded-full blur-3xl opacity-30 -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-200 rounded-full blur-3xl opacity-30 translate-y-1/2 -translate-x-1/2"></div>
        
        @php
            $promotions = \App\Models\Promotion::where('is_active', true)->orderBy('order_sequence')->get();
        @endphp
        
        @if($promotions->count() > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-30">
            
            <div class="text-center mb-10">
                <span class="bg-blue-100 text-blue-600 text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest shadow-sm">Special Offers</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Our Promotions</h2>
                <p class="text-slate-500 max-w-2xl mx-auto text-sm md:text-base font-medium">Grab these exclusive deals while they last. Enjoy premium laundry services at unbeatable prices.</p>
            </div>
            <div class="swiper promotionsSwiper py-12 px-4" style="overflow: hidden; padding-bottom: 60px;">
                <div class="swiper-wrapper">
                    @foreach($promotions as $promo)
                    <div class="swiper-slide">
                        <a href="{{ route('customer.login') ?? '/login' }}" class="block outline-none">
                            <div class="promo-card">
                                <img src="{{ Str::startsWith($promo->image_path, 'http') ? $promo->image_path : asset('storage/' . $promo->image_path) }}" class="w-full h-auto object-cover rounded-[1.5rem]" alt="Promotion">
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
                <!-- Pagination -->
                <div class="swiper-pagination z-50"></div>
            </div>

            <style>
                .promotionsSwiper {
                    padding-top: 2rem;
                    padding-bottom: 4rem;
                }
                .promotionsSwiper .swiper-slide {
                    width: 260px; /* Base size */
                    opacity: 0.5;
                    transform: scale(0.85);
                    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                    filter: blur(1.5px);
                }
                @media (min-width: 768px) {
                    .promotionsSwiper .swiper-slide {
                        width: 320px;
                    }
                }
                .promotionsSwiper .swiper-slide-active {
                    opacity: 1;
                    transform: scale(1.15); /* Pop out the active slide */
                    z-index: 20;
                    filter: blur(0px);
                }
                
                /* Premium Card Wrapper */
                .promo-card {
                    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8));
                    backdrop-filter: blur(10px);
                    padding: 0.5rem;
                    border-radius: 1.8rem;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.8);
                    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
                    position: relative;
                }
                /* Active Card Styling */
                .promotionsSwiper .swiper-slide-active .promo-card {
                    background: #ffffff;
                    /* Elegant deep glow */
                    box-shadow: 0 30px 60px -15px rgba(37, 99, 235, 0.25), 
                                0 0 40px rgba(59, 130, 246, 0.15),
                                inset 0 0 0 2px rgba(59, 130, 246, 0.1);
                    border-color: transparent;
                }

                /* Pagination Dots */
                .promotionsSwiper .swiper-pagination-bullet {
                    background-color: #cbd5e1;
                    opacity: 0.6;
                    width: 8px;
                    height: 8px;
                    transition: all 0.4s ease;
                }
                .promotionsSwiper .swiper-pagination-bullet-active {
                    background-color: #3b82f6; /* Tailwind blue-500 */
                    opacity: 1;
                    width: 32px;
                    border-radius: 4px;
                    box-shadow: 0 0 10px rgba(59,130,246,0.5);
                }
            </style>
            
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new Swiper(".promotionsSwiper", {
                        effect: "slide", /* Simple clean slide effect */
                        grabCursor: true,
                        centeredSlides: true,
                        slidesPerView: "auto",
                        spaceBetween: 30, /* Space between cards */
                        loop: {{ $promotions->count() > 3 ? 'true' : 'false' }},
                        speed: 1000, /* Smooth transition speed */
                        autoplay: {
                            delay: 2500,
                            disableOnInteraction: false /* Keeps looping */
                        },
                        pagination: {
                            el: ".swiper-pagination",
                            clickable: true,
                        },
                        breakpoints: {
                            768: {
                                spaceBetween: 50,
                            }
                        }
                    });
                });
            </script>
        </div>
        @endif
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-12 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Easy Steps</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">How It Works</h2>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center relative max-w-5xl mx-auto">
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-blue-100 -translate-y-1/2 z-0"></div>

                <div class="relative z-10 bg-white p-2 sm:p-4 text-center group">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:border-blue-200 transition-colors shadow-md">
                        <i class="fa-regular fa-calendar-check text-2xl sm:text-3xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-800">1. Book</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-2 max-w-[150px] mx-auto">Schedule a pickup.</p>
                </div>

                <div class="block md:hidden text-blue-200 my-2"><i class="fa-solid fa-arrow-down text-xl"></i></div>
                <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-xl"></i></div>

                <div class="relative z-10 bg-white p-2 sm:p-4 text-center group">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:border-blue-200 transition-colors shadow-md">
                        <i class="fa-solid fa-truck-pickup text-2xl sm:text-3xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-800">2. Pickup</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-2 max-w-[150px] mx-auto">We collect laundry.</p>
                </div>

                <div class="block md:hidden text-blue-200 my-2"><i class="fa-solid fa-arrow-down text-xl"></i></div>
                <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-xl"></i></div>

                <div class="relative z-10 bg-white p-2 sm:p-4 text-center group">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:border-blue-200 transition-colors shadow-md">
                        <i class="fa-solid fa-hands-bubbles text-2xl sm:text-3xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-800">3. Clean</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-2 max-w-[150px] mx-auto">Premium products.</p>
                </div>

                <div class="block md:hidden text-blue-200 my-2"><i class="fa-solid fa-arrow-down text-xl"></i></div>
                <div class="hidden md:block relative z-10 text-blue-300"><i class="fa-solid fa-arrow-right text-xl"></i></div>

                <div class="relative z-10 bg-white p-2 sm:p-4 text-center group">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:border-blue-200 transition-colors shadow-md">
                        <i class="fa-solid fa-box-open text-2xl sm:text-3xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg text-slate-800">4. Deliver</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 sm:mt-2 max-w-[150px] mx-auto">Fresh clothes back.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us & Features -->
    <section class="py-16 lg:py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mb-4">Why Choose 365laundry.lk?</h2>
                <p class="text-slate-500 max-w-2xl mx-auto font-medium">We combine state-of-the-art technology with expert care to give your clothes the premium treatment they deserve.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Experienced Team</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Trained professionals.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-cogs"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Advanced Equipment</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Modern machines.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Hygienic Cleaning</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Deep cleaning care.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-thumbs-up"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">100% Satisfaction</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Best service assured.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- Google Reviews -->
    <section id="testimonials" class="py-16 lg:py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-6">
                <div class="text-center md:text-left flex items-center gap-4">
                    <i class="fa-brands fa-google text-4xl text-blue-500 hidden sm:block"></i>
                    <div>
                        <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">What Our Customers Say</span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-brand mt-4">Google Reviews</h2>
                    </div>
                </div>
                <a href="{{ env('GOOGLE_REVIEW_LINK', '#') }}" target="_blank" class="bg-white border-2 border-brand text-brand hover:bg-brand hover:text-white font-bold py-2.5 px-6 rounded-xl shadow-sm transition-colors flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-star text-yellow-400"></i> Write a Review
                </a>
            </div>

            @php
                $reviews = [
                    ['name' => 'Nadeesha Perera', 'text' => 'Excellent service! My clothes were picked up on time and delivered perfectly pressed and smelling fresh. Highly recommend 365 Laundry.', 'color' => 'bg-blue-500', 'initial' => 'N', 'time' => 'a week ago'],
                    ['name' => 'Gayan Fernando', 'text' => 'Very reliable and fast. Had a tough stain on my favorite shirt and they managed to get it out completely. The staff is also very polite.', 'color' => 'bg-green-500', 'initial' => 'G', 'time' => '2 weeks ago'],
                    ['name' => 'Tharindi Silva', 'text' => 'Best laundry service in Kotikawatta. The 48-hour delivery is exactly as promised. Packaging is also very neat and clean.', 'color' => 'bg-purple-500', 'initial' => 'T', 'time' => 'a month ago'],
                    ['name' => 'Kasun Weerasinghe', 'text' => 'Great value for money. They handle my office wear perfectly. The convenience of them picking it up from home is unmatched.', 'color' => 'bg-orange-500', 'initial' => 'K', 'time' => '2 months ago'],
                    ['name' => 'Chamodi Rathnayake', 'text' => 'Safe and hygienic. I gave my heavy curtains for washing and they did a brilliant job. Will definitely use their service again.', 'color' => 'bg-pink-500', 'initial' => 'C', 'time' => '3 months ago'],
                    ['name' => 'Dinuka Bandara', 'text' => 'Super fast service. I needed a suit dry cleaned urgently and they managed to accommodate my request. Very professional.', 'color' => 'bg-red-500', 'initial' => 'D', 'time' => '3 months ago'],
                    ['name' => 'Hasini Wijesinghe', 'text' => 'Been using them for 3 months now for all my household laundry. Very consistent quality. The booking process is also very easy.', 'color' => 'bg-teal-500', 'initial' => 'H', 'time' => '4 months ago'],
                    ['name' => 'Amila Jayasuriya', 'text' => 'Very friendly team and excellent fabric care. They pay attention to the care labels which most other places ignore. 5 stars!', 'color' => 'bg-indigo-500', 'initial' => 'A', 'time' => '5 months ago'],
                ];
            @endphp

            <div class="swiper reviewsSwiper py-4" style="overflow: hidden; padding-bottom: 50px; padding-left: 5px; padding-right: 5px;">
                <div class="swiper-wrapper">
                    @foreach($reviews as $review)
                    <div class="swiper-slide">
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full {{ $review['color'] }} flex items-center justify-center text-white font-bold text-lg">
                                        {{ $review['initial'] }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $review['name'] }}</h4>
                                        <p class="text-[11px] text-slate-500">{{ $review['time'] }}</p>
                                    </div>
                                </div>
                                <i class="fa-brands fa-google text-xl" style="color: #4285F4;"></i>
                            </div>
                            <!-- Stars -->
                            <div class="flex text-yellow-400 text-xs mb-3 gap-0.5">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                            </div>
                            <!-- Text -->
                            <p class="text-slate-600 text-sm flex-grow">"{{ $review['text'] }}"</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <!-- Pagination -->
                <div class="swiper-pagination z-50"></div>
            </div>

            <style>
                /* Reviews Slider Custom Pagination */
                .reviewsSwiper .swiper-pagination-bullet {
                    background-color: #cbd5e1;
                    opacity: 0.6;
                    transition: all 0.3s;
                }
                .reviewsSwiper .swiper-pagination-bullet-active {
                    background-color: #3b82f6;
                    opacity: 1;
                    width: 24px;
                    border-radius: 4px;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Initialize Reviews Slider
                    new Swiper(".reviewsSwiper", {
                        slidesPerView: 1,
                        spaceBetween: 20,
                        loop: true,
                        grabCursor: true,
                        speed: 800,
                        autoplay: {
                            delay: 3000,
                            disableOnInteraction: false
                        },
                        pagination: {
                            el: ".swiper-pagination",
                            clickable: true,
                        },
                        breakpoints: {
                            768: {
                                slidesPerView: 2,
                                spaceBetween: 24,
                            },
                            1024: {
                                slidesPerView: 3,
                                spaceBetween: 30,
                            }
                        }
                    });
                });
            </script>
        </div>
    </section>

    
    <!-- App Download Section -->
    <section class="py-16 lg:py-20 bg-brand text-white relative">
        <!-- Background Elements -->
        <div class="absolute inset-0 opacity-10 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-64 h-64 bg-white rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 -translate-y-1/2"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-400 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/3 translate-y-1/3"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
                <!-- Text Content (Left) -->
                <div class="flex-1 text-center lg:text-left z-20">
                    <h2 class="text-3xl md:text-5xl font-extrabold mb-4 leading-tight">Laundry at Your Fingertips</h2>
                    <p class="text-blue-100 text-base md:text-lg mb-8 max-w-xl mx-auto lg:mx-0">
                        Book, track & manage your laundry easily from your phone. Download our app today for exclusive discounts and seamless scheduling.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <!-- App Store Button -->
                        <a href="#" class="flex items-center gap-3 bg-white text-brand px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors shadow-lg hover:shadow-xl w-full sm:w-auto justify-center group">
                            <i class="fa-brands fa-apple text-3xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-left">
                                <p class="text-[10px] uppercase font-bold leading-none text-slate-500 mb-1">Download on the</p>
                                <p class="text-lg font-bold leading-none">App Store</p>
                            </div>
                        </a>
                        
                        <!-- Google Play Button -->
                        <a href="#" class="flex items-center gap-3 bg-white text-brand px-6 py-3 rounded-xl hover:bg-blue-50 transition-colors shadow-lg hover:shadow-xl w-full sm:w-auto justify-center group">
                            <i class="fa-brands fa-google-play text-2xl group-hover:scale-110 transition-transform"></i>
                            <div class="text-left">
                                <p class="text-[10px] uppercase font-bold leading-none text-slate-500 mb-1">GET IT ON</p>
                                <p class="text-lg font-bold leading-none">Google Play</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- App Image (Right) -->
                <div class="flex-1 relative flex justify-center lg:justify-end z-30">
                    <!-- Negative margins make the image "pop out" of the section boundaries -->
                    <div class="relative w-full max-w-[280px] sm:max-w-sm -mb-28 lg:-my-32">
                        <!-- Decorative glow behind image -->
                        <div class="absolute inset-0 bg-blue-400 rounded-full filter blur-3xl opacity-30 transform scale-90 pointer-events-none"></div>
                        <img src="{{ asset('images/laundry_app_mockup_new.png') }}" alt="Laundry App Mockup" class="relative z-30 w-full h-auto drop-shadow-2xl hover:-translate-y-2 transition-transform duration-500" style="mix-blend-mode: multiply;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 lg:py-20 bg-slate-50 border-t border-slate-100 mt-12 lg:mt-0 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Get In Touch</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-brand mt-4">We're Here for You</h2>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 bg-white rounded-3xl shadow-lg overflow-hidden border border-slate-200">
                <!-- Contact Info (No Scroll) -->
                <div class="flex-1 p-6 lg:p-10 flex flex-col justify-center">
                    <div class="grid gap-6">
                        @foreach($branches as $branch)
                        <!-- Branch -->
                        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100">
                            <h3 class="font-bold text-base text-brand mb-3 pb-2 border-b border-slate-200">
                                @if($branch->type === 'workshop')
                                    <i class="fa-solid fa-cogs text-accent mr-2"></i>
                                @else
                                    <i class="fa-solid fa-store text-accent mr-2"></i>
                                @endif
                                {{ $branch->name }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @if($branch->phone)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center text-xs shrink-0 shadow-sm"><i class="fa-solid fa-phone"></i></div>
                                    <div><p class="text-[10px] font-bold text-slate-400 uppercase">Phone</p><p class="text-sm font-semibold text-slate-700">{{ $branch->phone }}</p></div>
                                </div>
                                @endif
                                @if($branch->whatsapp_number)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-white text-green-500 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm"><i class="fa-brands fa-whatsapp"></i></div>
                                    <div><p class="text-[10px] font-bold text-slate-400 uppercase">WhatsApp</p><p class="text-sm font-semibold text-slate-700">{{ $branch->whatsapp_number }}</p></div>
                                </div>
                                @endif
                                @if($branch->address)
                                <div class="flex items-center gap-3 sm:col-span-2 mt-1">
                                    <div class="w-8 h-8 bg-white text-blue-600 rounded-full flex items-center justify-center text-xs shrink-0 shadow-sm"><i class="fa-solid fa-location-dot"></i></div>
                                    <div><p class="text-[10px] font-bold text-slate-400 uppercase">Address</p><p class="text-sm font-semibold text-slate-700">{{ $branch->address }}</p></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Authentic OpenStreetMap -->
                <div class="flex-1 bg-slate-200 relative min-h-[300px] lg:min-h-full">
                    <iframe 
                        src="https://www.openstreetmap.org/export/embed.html?bbox=79.8350,6.9120,79.8750,6.9520&amp;layer=mapnik&amp;marker=6.9271,79.8612" 
                        width="100%" 
                        height="100%" 
                        style="border:0; position:absolute; top:0; left:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                    <!-- Small attribution for OSM -->
                    <div class="absolute bottom-1 right-1 bg-white/80 px-2 py-1 text-[10px] text-slate-500 rounded">
                        <a href="https://www.openstreetmap.org/?mlat=6.9271&amp;mlon=79.8612#map=14/6.9271/79.8612" target="_blank" class="hover:text-blue-600">View Larger Map</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
@endsection



