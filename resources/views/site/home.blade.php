@extends('layouts.site')

@section('content')
    <!-- Hero Section with Slides -->
    <section x-data="{ slide: 1, timer: null, direction: 1 }" x-init="timer = setInterval(() => { 
            if (slide === 3) direction = -1;
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
                        class="text-5xl sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
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
                        <a href="{{ route('customer.booking') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
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
                        class="text-5xl sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
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
                        <a href="{{ route('customer.booking') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
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
                        class="text-5xl sm:text-6xl lg:text-6xl font-extrabold font-['Poppins'] text-brand leading-[1.1] mb-0 lg:mb-2 tracking-tight drop-shadow-[0_4px_4px_rgba(255,255,255,0.8)] lg:drop-shadow-[0_4px_25px_rgba(255,255,255,1)] [-webkit-text-stroke:1.2px_white] lg:[-webkit-text-stroke:0.5px_white]"
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
                        <a href="{{ route('customer.booking') }}"
                            class="w-full bg-accent text-white px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base shadow-xl shadow-blue-200/50 hover:bg-blue-700 transition flex items-center justify-center gap-2">
                            <i class="fa-regular fa-calendar-check"></i> Book <span
                                class="hidden sm:inline">Pickup</span>
                        </a>
                        <a href="#services"
                            class="w-full bg-white text-accent border border-slate-200 px-2 sm:px-6 py-2.5 sm:py-3 rounded-full font-bold text-sm lg:text-base hover:bg-slate-50 transition flex items-center justify-center gap-2 shadow-sm">
                            <i class="fa-regular fa-circle-play text-base"></i> Services
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- White Pill Banner (Always visible on all slides) -->
        <div class="absolute bottom-2 lg:bottom-12 left-0 right-0 z-20 max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 w-full">
            <!-- 2x2 Grid on mobile to save space, inline on desktop -->
            <div
                class="bg-white/95 backdrop-blur-xl rounded-3xl lg:rounded-[2.5rem] p-3 lg:p-6 shadow-2xl border border-white grid grid-cols-2 lg:flex lg:flex-row lg:flex-wrap justify-start lg:justify-between items-center gap-3 lg:gap-4 w-full">

                <div class="flex items-center gap-3 lg:gap-4 lg:pl-4">
                    <div class="text-2xl lg:text-3xl text-brand"><i class="fa-solid fa-truck-fast"></i></div>
                    <div>
                        <p class="font-extrabold text-[12px] sm:text-sm lg:text-base text-brand leading-tight">Pickup &
                            Delivery</p>
                        <p class="text-[10px] lg:text-xs text-slate-500 font-medium">Right at your doorstep.</p>
                    </div>
                </div>
                <div class="hidden lg:block w-px h-10 bg-slate-200"></div>

                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="text-2xl lg:text-3xl text-brand"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    <div>
                        <p class="font-extrabold text-[12px] sm:text-sm lg:text-base text-brand leading-tight">48-Hour
                            Service</p>
                        <p class="text-[10px] lg:text-xs text-slate-500 font-medium">Fast & reliable delivery.</p>
                    </div>
                </div>
                <div class="hidden lg:block w-px h-10 bg-slate-200"></div>

                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="text-2xl lg:text-3xl text-brand"><i class="fa-solid fa-moon"></i></div>
                    <div>
                        <p class="font-extrabold text-[12px] sm:text-sm lg:text-base text-brand leading-tight">Open Till
                            10 PM</p>
                        <p class="text-[10px] lg:text-xs text-slate-500 font-medium">Pickups available late.</p>
                    </div>
                </div>
                <div class="hidden lg:block w-px h-10 bg-slate-200"></div>

                <div class="flex items-center gap-3 lg:gap-4 lg:pr-4">
                    <div class="text-2xl lg:text-3xl text-green-500"><i class="fa-solid fa-shield-virus"></i></div>
                    <div>
                        <p class="font-extrabold text-[12px] sm:text-sm lg:text-base text-brand leading-tight">Safe &
                            Hygienic</p>
                        <p class="text-[10px] lg:text-xs text-slate-500 font-medium">Clean you can trust.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider Indicators -->
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-3 z-30">
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
    <section id="services" class="py-10 lg:py-20 bg-white" x-data="{ openCategory: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">What
                    We Offer</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Our Services</h2>
                <div class="w-16 h-1 bg-accent mx-auto rounded-full"></div>
            </div>

            <!-- Category Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($categories as $category)
                    <div class="rounded-3xl border border-slate-100 shadow-sm overflow-hidden transition-all duration-300"
                        :class="openCategory === {{ $category->id }} ? 'ring-2 ring-blue-200 shadow-xl' : 'hover:shadow-lg'">

                        <!-- Category Header Card -->
                        <div @click="openCategory = openCategory === {{ $category->id }} ? null : {{ $category->id }}"
                            class="p-6 cursor-pointer flex items-center justify-between transition-colors duration-200"
                            :class="openCategory === {{ $category->id }} ? 'bg-accent text-white' : 'bg-white text-slate-800'">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl transition-colors"
                                    :class="openCategory === {{ $category->id }} ? 'bg-white/20 text-white' : 'bg-blue-50 text-accent'">
                                    @if(str_contains(strtolower($category->name), 'wash'))
                                        <i class="fa-solid fa-soap"></i>
                                    @elseif(str_contains(strtolower($category->name), 'dry'))
                                        <i class="fa-solid fa-shirt"></i>
                                    @elseif(str_contains(strtolower($category->name), 'iron'))
                                        <i class="fa-solid fa-fire-burner"></i>
                                    @else
                                        <i class="fa-solid fa-layer-group"></i>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold">{{ $category->name }}</h3>
                                    <p class="text-sm mt-0.5"
                                        :class="openCategory === {{ $category->id }} ? 'text-blue-100' : 'text-slate-500'">
                                        {{ $category->services->count() }} service(s) available</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-full flex items-center justify-center transition-all"
                                :class="openCategory === {{ $category->id }} ? 'bg-white/20 rotate-180' : 'bg-slate-100'">
                                <i class="fa-solid fa-chevron-down text-sm"
                                    :class="openCategory === {{ $category->id }} ? 'text-white' : 'text-slate-500'"></i>
                            </div>
                        </div>

                        <!-- Services List (Expandable) -->
                        <div x-show="openCategory === {{ $category->id }}"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0"
                            style="display:none;" class="bg-slate-50 border-t border-slate-100">
                            @foreach($category->services as $service)
                                <div
                                    class="flex items-center justify-between px-6 py-4 border-b border-slate-100 last:border-0 hover:bg-white transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-xl bg-blue-50 text-accent flex items-center justify-center">
                                            @if(str_contains(strtolower($service->name), 'wash'))
                                                <i class="fa-solid fa-jug-detergent"></i>
                                            @elseif(str_contains(strtolower($service->name), 'iron'))
                                                <i class="fa-solid fa-fire-burner"></i>
                                            @elseif(str_contains(strtolower($service->name), 'dry'))
                                                <i class="fa-solid fa-shirt"></i>
                                            @else
                                                <i class="fa-solid fa-hands-bubbles"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800">{{ $service->name }}</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                {{ $service->description ?? 'Professional care for your clothes.' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-4">
                                        <p class="text-accent font-extrabold text-lg">Rs.
                                            {{ number_format($service->base_price, 0) }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ $service->price_type == 'per_kg' ? 'per kg' : 'per item' }}</p>
                                    </div>
                                </div>
                            @endforeach
                            @if($category->services->isEmpty())
                                <div class="p-6 text-center text-slate-400 text-sm">No services in this category yet.</div>
                            @endif
                            <div class="p-4 border-t border-slate-100">
                                <a href="{{ route('customer.booking') }}"
                                    class="block w-full text-center bg-accent text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition-all shadow-sm">
                                    Book {{ $category->name }} <i class="fa-solid fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-10 lg:py-20 bg-slate-50 relative overflow-hidden">
        <div
            class="absolute top-0 right-0 w-64 h-64 bg-blue-100 rounded-full blur-3xl opacity-50 -translate-y-1/2 translate-x-1/2">
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <span
                    class="bg-blue-100 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Affordable
                    & Transparent</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Simple & Transparent Pricing</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Choose a package that best fits your laundry needs. Save
                    more with our bundle deals.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center max-w-5xl mx-auto">
                @foreach($packages->take(3) as $index => $package)
                    <div
                        class="bg-white rounded-2xl p-6 border border-slate-100 {{ $index === 1 ? 'shadow-2xl ring-2 ring-blue-500/20 md:-translate-y-2 scale-105 relative' : 'shadow hover:shadow-lg' }} transition-all">
                        @if($index === 1)
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2">
                                <span
                                    class="bg-accent text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest shadow-sm">Most
                                    Popular</span>
                            </div>
                        @endif
                        <h3 class="text-xl font-bold text-center text-brand mb-1">{{ $package->name }}</h3>
                        <div class="text-center mb-4">
                            <span class="text-xs text-slate-500 font-medium">From</span><br>
                            <span class="text-3xl font-extrabold text-slate-800">Rs.
                                {{ number_format($package->price, 0) }}</span>
                        </div>

                        <ul class="space-y-2.5 mb-6">
                            @foreach($package->services as $ps)
                                <li class="flex items-center gap-2 text-xs text-slate-600">
                                    <i
                                        class="fa-solid fa-check text-accent bg-blue-50 w-5 h-5 rounded-full flex items-center justify-center text-[10px]"></i>
                                    {{ $ps->serviceType->name ?? 'Service' }} <span
                                        class="text-slate-400 text-[10px] ml-auto">x{{ $ps->total_quantity }}</span>
                                </li>
                            @endforeach
                            <li class="flex items-center gap-2 text-xs text-slate-600">
                                <i
                                    class="fa-solid fa-check text-accent bg-blue-50 w-5 h-5 rounded-full flex items-center justify-center text-[10px]"></i>
                                Free Delivery
                            </li>
                        </ul>

                        <a href="{{ route('customer.booking') }}"
                            class="block w-full text-center py-2.5 rounded-lg font-bold text-sm transition-all {{ $index === 1 ? 'bg-accent text-white hover:bg-blue-700 shadow-md' : 'bg-blue-50 text-blue-600 hover:bg-blue-100' }}">
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
                <span
                    class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Easy
                    Steps</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">How It Works</h2>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center relative">
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-blue-100 -translate-y-1/2 z-0">
                </div>

                <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                        <i class="fa-regular fa-calendar-check text-4xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">1. Book</h3>
                    <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">Schedule a pickup at your convenience.
                    </p>
                </div>

                <div class="hidden md:block relative z-10 text-blue-300"><i
                        class="fa-solid fa-arrow-right text-2xl"></i></div>

                <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                        <i class="fa-solid fa-truck-pickup text-4xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">2. Pickup</h3>
                    <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">We collect laundry from your doorstep.
                    </p>
                </div>

                <div class="hidden md:block relative z-10 text-blue-300"><i
                        class="fa-solid fa-arrow-right text-2xl"></i></div>

                <div class="relative z-10 bg-white p-4 text-center group mb-8 md:mb-0">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                        <i class="fa-solid fa-hands-bubbles text-4xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">3. Clean</h3>
                    <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">Clothes are cleaned with premium
                        products.</p>
                </div>

                <div class="hidden md:block relative z-10 text-blue-300"><i
                        class="fa-solid fa-arrow-right text-2xl"></i></div>

                <div class="relative z-10 bg-white p-4 text-center group">
                    <div
                        class="w-24 h-24 mx-auto bg-white border-4 border-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:border-blue-200 transition-colors shadow-lg">
                        <i class="fa-solid fa-box-open text-4xl text-accent"></i>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800">4. Deliver</h3>
                    <p class="text-sm text-slate-500 mt-2 max-w-[150px] mx-auto">We deliver fresh, clean clothes back to
                        you.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us & Features -->
    <section class="py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-12 items-center">
                <div class="flex-1">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-brand mb-8">Why Choose 360 Laundry?</h2>
                    <div class="grid grid-cols-2 gap-8">
                        <div class="text-center lg:text-left">
                            <div
                                class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-2">Experienced Team</h4>
                            <p class="text-sm text-slate-500">Trained professionals with years of experience.</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <div
                                class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                                <i class="fa-solid fa-cogs"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-2">Advanced Equipment</h4>
                            <p class="text-sm text-slate-500">Modern machines & safe cleaning methods.</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <div
                                class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                                <i class="fa-solid fa-shield-virus"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 mb-2">Hygienic Cleaning</h4>
                            <p class="text-sm text-slate-500">Deep cleaning for a healthier life.</p>
                        </div>
                        <div class="text-center lg:text-left">
                            <div
                                class="w-16 h-16 mx-auto lg:mx-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
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
                        <img src="{{ asset('images/choose_laundry.png') }}" alt="Clean Towels"
                            class="relative z-10 rounded-[2rem] shadow-2xl object-cover h-[400px] w-[500px]">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">What
                    Our Customers Say</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mt-4 mb-4">Testimonials</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                    <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <img src="https://i.pravatar.cc/150?img=5" alt="User"
                            class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                        <div>
                            <h4 class="font-bold text-slate-800">Nirosha W.</h4>
                            <div class="flex text-yellow-400 text-xs">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-600 relative z-10">"Amazing service! My clothes came back so fresh and
                        perfectly folded. Saves me so much time every week."</p>
                </div>

                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                    <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <img src="https://i.pravatar.cc/150?img=11" alt="User"
                            class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                        <div>
                            <h4 class="font-bold text-slate-800">Gayan P.</h4>
                            <div class="flex text-yellow-400 text-xs">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star-half-stroke"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-600 relative z-10">"Very reliable pickup and delivery. Their dry cleaning
                        removed a tough stain from my suit. Highly recommended!"</p>
                </div>

                <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100 relative">
                    <i class="fa-solid fa-quote-left absolute top-6 right-8 text-4xl text-blue-100"></i>
                    <div class="flex items-center gap-4 mb-6 relative z-10">
                        <img src="https://i.pravatar.cc/150?img=9" alt="User"
                            class="w-16 h-16 rounded-full object-cover shadow-sm border-2 border-white">
                        <div>
                            <h4 class="font-bold text-slate-800">Tharindi S.</h4>
                            <div class="flex text-yellow-400 text-xs">
                                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                                    class="fa-solid fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="text-slate-600 relative z-10">"Great quality ironing. My clothes look brand new every
                        time. The booking process is extremely easy."</p>
                </div>
            </div>
        </div>
    </section>

    <!-- App Promo -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
        <div
            class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-[3rem] p-10 lg:p-16 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center gap-10">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-black/10 rounded-full blur-xl"></div>

            <div class="flex-1 relative z-10 text-center md:text-left">
                <h2 class="text-3xl md:text-5xl font-extrabold mb-4">Laundry at Your Fingertips</h2>
                <p class="text-blue-100 text-lg mb-8 max-w-lg">Book, track & manage your laundry easily from your phone.
                    Download our app today!</p>
                <div class="flex flex-wrap justify-center md:justify-start gap-4">
                    <a href="#"
                        class="bg-black hover:bg-gray-800 transition rounded-xl px-5 py-2.5 flex items-center gap-3">
                        <i class="fa-brands fa-google-play text-2xl"></i>
                        <div class="text-left">
                            <p class="text-[10px] uppercase tracking-wider leading-none">GET IT ON</p>
                            <p class="font-bold leading-none mt-1">Google Play</p>
                        </div>
                    </a>
                    <a href="#"
                        class="bg-black hover:bg-gray-800 transition rounded-xl px-5 py-2.5 flex items-center gap-3">
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
                <img src="{{ asset('images/app_mockup.png') }}" alt="App Mockup"
                    class="w-full max-w-[300px] h-auto drop-shadow-2xl rounded-[3rem] transform lg:-translate-y-4 hover:-translate-y-6 transition-transform duration-500">
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span
                    class="bg-blue-50 text-blue-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Get
                    In Touch</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-brand mt-4">We're Here for You</h2>
            </div>

            <div
                class="flex flex-col lg:flex-row gap-12 bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
                <!-- Contact Info -->
                <div class="flex-1 p-10 lg:p-12 h-[500px] overflow-y-auto custom-scrollbar">
                    <div class="space-y-8">
                        @foreach($branches as $b)
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <h3 class="font-bold text-lg text-brand mb-4 border-b border-slate-200 pb-2"><i
                                        class="fa-solid fa-store text-accent mr-2"></i>{{ $b->name }}</h3>
                                <div class="space-y-4">
                                    <div class="flex gap-4">
                                        <div
                                            class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
                                            <i class="fa-solid fa-phone"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs text-slate-800 uppercase">Phone</h4>
                                            <p class="text-slate-600 text-sm">{{ $b->phone ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-4">
                                        <div
                                            class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-xs text-slate-800 uppercase">WhatsApp</h4>
                                            <p class="text-slate-600 text-sm">{{ $b->whatsapp_number ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-4">
                                        <div
                                            class="w-10 h-10 bg-white text-blue-600 rounded-full flex items-center justify-center text-sm shrink-0 shadow-sm">
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
                    <img src="https://images.unsplash.com/photo-1524661135-423995f22d0b?q=80&w=800&auto=format&fit=crop"
                        class="w-full h-full object-cover opacity-80 mix-blend-luminosity" alt="Map">
                    <div
                        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-accent text-5xl drop-shadow-xl animate-bounce">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
@endsection

