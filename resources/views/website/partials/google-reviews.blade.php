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
                    ['name' => 'Nadeesha Perera', 'text' => 'Excellent service! My clothes were picked up on time and delivered perfectly pressed and smelling fresh. Highly recommend {{ \App\Models\Setting::get(\'company_name\', \'Laundry Zone\') }}.', 'color' => 'bg-blue-500', 'initial' => 'N', 'time' => 'a week ago'],
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
                        <img src="{{ asset('images/laundry_app_mockup.png') }}" alt="Laundry App Mockup" class="relative z-30 w-full h-auto drop-shadow-2xl hover:-translate-y-2 transition-transform duration-500" style="mix-blend-mode: multiply;">
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