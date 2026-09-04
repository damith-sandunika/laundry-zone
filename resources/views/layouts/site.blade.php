<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>365laundry.lk - Premium Laundry Delivery</title>
    <link rel="icon" type="image/png" href="{{ asset('images/365_laundry_logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #fcfdfd;
        }

        .text-brand {
            color: #1e3a8a;
        }

        /* dark blue */
        .text-accent {
            color: #2563eb;
        }

        /* bright blue */
        .bg-brand {
            background-color: #1e3a8a;
        }

        .bg-accent {
            background-color: #2563eb;
        }

        @keyframes wave {
            0% {
                transform: translateX(0);
            }

            50% {
                transform: translateX(-25%);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .wave-container {
            position: absolute;
            bottom: -5px;
            /* overlap slightly to hide gaps */
            left: 0;
            width: 200%;
            height: 120px;
            overflow: hidden;
            z-index: 15;
            pointer-events: none;
        }

        .wave-layer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-repeat: repeat-x;
            background-position: 0 bottom;
            background-size: 50% 100%;
            animation: wave 20s linear infinite;
        }

        .wave-1 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%23ffffff'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05c50.7,38.35,113.88,43.23,172.9,42.54,63.1-1.07,122.9-20.93,184-39.69,57.17-17.65,115.5-35.16,177.3-39.69,63.1-4.6,125.8,11.23,186.7,28.05,58,16,116.5,31.7,179.3,31.7,60.2,0,119.5-12.79,178.5-27.1,51.8-12.59,101.4-28.75,153.7-31.95V0Z' opacity='.5' fill='%23ffffff'/%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' fill='%23ffffff'/%3E%3C/svg%3E");
            animation-duration: 25s;
        }

        .wave-2 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%23ffffff'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05c50.7,38.35,113.88,43.23,172.9,42.54,63.1-1.07,122.9-20.93,184-39.69,57.17-17.65,115.5-35.16,177.3-39.69,63.1-4.6,125.8,11.23,186.7,28.05,58,16,116.5,31.7,179.3,31.7,60.2,0,119.5-12.79,178.5-27.1,51.8-12.59,101.4-28.75,153.7-31.95V0Z' opacity='.5' fill='%23ffffff'/%3E%3Cpath d='M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z' fill='%23ffffff'/%3E%3C/svg%3E");
            animation-duration: 20s;
            animation-direction: reverse;
            bottom: -10px;
        }

        .hero-img-container {
            position: relative;
            z-index: 10;
        }

        .fade-up-enter {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .fade-up-enter-active {
            opacity: 1;
            transform: translateY(0);
        }

        .slide-left-enter {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s ease-out;
        }

        .slide-right-enter {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s ease-out;
        }

        .slide-active {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>

<body class="antialiased text-gray-800 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden">

    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand -->
                <a href="{{ route('customer.booking') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/365_laundry_logo_site.png') }}" alt="365 Laundry Logo" class="h-10 w-auto">
                </a>

                <!-- Right: Sign In + Book Now -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.login') }}"
                        class="text-slate-600 font-semibold hover:text-blue-600 transition text-sm hidden sm:block">My Account</a>
                    <a href="{{ route('customer.booking') }}"
                        class="bg-blue-600 text-white px-5 py-2 rounded-full font-bold hover:bg-blue-700 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                        Book Now <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
    <footer class="bg-[#0f172a] text-slate-300 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 border-b border-slate-700 pb-12 mb-8">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center mb-6">
                        <span class="text-3xl mr-2 text-white"><i class="fa-solid fa-bolt"></i></span>
                        <span class="font-bold text-2xl text-white">365laundry.lk</span>
                    </div>
                    <p class="text-sm text-slate-400 mb-6">Professional laundry and dry cleaning services with care and
                        convenience.</p>
                    <div class="flex gap-4">
                        <a href="#"
                            class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i
                                class="fa-brands fa-facebook-f"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i
                                class="fa-brands fa-instagram"></i></a>
                        <a href="#"
                            class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i
                                class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Quick Links</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('customer.booking') }}" class="hover:text-white transition">Book
                                Pickup</a></li>
                        <li><a href="{{ route('customer.login') }}" class="hover:text-white transition">My Account</a>
                        </li>
                        <li><a href="{{ route('customer.orders') }}" class="hover:text-white transition">Track Order</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Our Services</h4>
                    <ul class="space-y-3 text-sm">
                        @foreach(\App\Models\ServiceCategory::where('is_active', 1)->take(5)->get() as $cat)
                            <li><a href="{{ url('/#services') }}" class="hover:text-white transition">{{ $cat->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-6 uppercase tracking-wider text-sm">Newsletter</h4>
                    <p class="text-sm text-slate-400 mb-4">Subscribe to get special offers and updates.</p>
                    <form class="flex">
                        <input type="email" placeholder="Enter your email"
                            class="bg-slate-800 border-none rounded-l-lg px-4 py-2 w-full text-sm focus:ring-1 focus:ring-accent outline-none text-white">
                        <button type="submit"
                            class="bg-accent text-white px-4 py-2 rounded-r-lg hover:bg-blue-500 transition"><i
                                class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div
                class="text-center text-sm text-slate-500 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <p>&copy; {{ date('Y') }} 365laundry.lk. All Rights Reserved.</p>
                    <p class="mt-1 text-slate-600 text-xs font-medium">Powered by <a href="https://dsrzone.com"
                            target="_blank"
                            class="text-blue-500 hover:text-blue-400 font-bold transition-colors">DSRZone</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Smooth Scroll & Intersection Observer Script -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add scroll animations
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-up-enter-active');
                        // Stop observing once animated
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1, // Trigger when 10% visible
                rootMargin: '0px 0px -50px 0px' // Trigger slightly before it comes into view
            });

            // Select all major sections to animate (excluding hero which is first)
            const sections = document.querySelectorAll('section:not(:first-of-type)');
            sections.forEach(section => {
                section.classList.add('fade-up-enter');
                observer.observe(section);
            });
        });
    </script>
</body>

</html>

