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
            <img src="{{ asset('images/laundry_slider_desktop_1.png') }}" alt="Laundry Slide 1"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[80%_top]">
            <img src="{{ asset('images/laundry_slider_mobile_1.png') }}" alt="Laundry Slide 1 Mobile"
                class="block lg:hidden w-full h-full object-cover object-top">
        </div>
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out"
            :class="slide === 2 ? 'opacity-100' : 'opacity-0'">
            <img src="{{ asset('images/laundry_slider_desktop_2.png') }}" alt="Laundry Slide 2"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[20%_top]">
            <img src="{{ asset('images/laundry_slider_mobile_2.png') }}" alt="Laundry Slide 2 Mobile"
                class="block lg:hidden w-full h-full object-cover object-top">
        </div>
        <div class="absolute inset-0 z-0 transition-opacity duration-1000 ease-in-out"
            :class="slide === 3 ? 'opacity-100' : 'opacity-0'">
            <img src="{{ asset('images/laundry_slider_desktop_3.png') }}" alt="Laundry Slide 3"
                class="hidden lg:block w-full h-full object-cover object-top lg:object-[80%_top]">
            <img src="{{ asset('images/laundry_slider_mobile_3.png') }}" alt="Laundry Slide 3 Mobile"
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