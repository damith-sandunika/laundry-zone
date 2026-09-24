    <!-- Promotions Section -->
    <section id="promotions" class="pt-4 pb-16 lg:pt-6 lg:pb-24 bg-slate-50 relative overflow-hidden">
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
                        @if($promo->category_id)
                            <a href="{{ url('/?category=' . $promo->category_id . '#services') }}" class="block outline-none">
                        @else
                            <a href="{{ route('customer.login') }}" class="block outline-none">
                        @endif
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
                        effect: "coverflow",
                        grabCursor: true,
                        centeredSlides: true,
                        centerInsufficientSlides: true,
                        slidesPerView: "auto",
                        coverflowEffect: {
                            rotate: 0,
                            stretch: 0,
                            depth: 150,
                            modifier: 1.5,
                            slideShadows: false,
                        },
                        spaceBetween: 30,
                        loop: {{ $promotions->count() > 1 ? 'true' : 'false' }},
                        speed: 1000,
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
