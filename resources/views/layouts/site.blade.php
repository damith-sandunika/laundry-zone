<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>365laundry.lk - Premium Laundry & Dry Cleaning Service</title>
    <link rel="icon" type="image/png" href="{{ asset('images/360_laundry_logo.png') }}">
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

        /* Liquid Gradient Background */
        .liquid-bg {
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #2563eb, #0284c7);
            background-size: 400% 400%;
            animation: liquid-gradient 12s ease infinite;
        }
        @keyframes liquid-gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Bubble Animations for Loader */
        .bubbles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 5;
            overflow: hidden;
            top: 0;
            left: 0;
        }
        .bubble {
            position: absolute;
            bottom: -50px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            box-shadow: inset 0 0 15px rgba(255,255,255,0.2);
            animation: rise infinite ease-in;
        }
        @keyframes rise {
            0% {
                bottom: -50px;
                transform: translateX(0);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 0.6; }
            100% {
                bottom: 110vh;
                transform: translateX(calc(-100px + 20vw));
                opacity: 0;
            }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.95; transform: scale(0.98); }
        }
    </style>
</head>

<body class="antialiased text-gray-800 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden">

    <!-- Welcome Loader -->
    <div id="welcome-loader" class="fixed inset-0 z-[9999] liquid-bg flex flex-col items-center justify-center overflow-hidden transition-opacity duration-700">
        
        <!-- Waves at the top -->
        <div class="absolute top-0 left-0 w-[200%] h-[150px] overflow-hidden opacity-40 pointer-events-none" style="-webkit-mask-image: linear-gradient(to bottom, black 30%, transparent 95%); mask-image: linear-gradient(to bottom, black 30%, transparent 95%);">
            <div class="wave-layer wave-1"></div>
            <div class="wave-layer wave-2"></div>
        </div>

        <!-- Bubbles Background -->
        <div class="bubbles-container absolute inset-0 pointer-events-none"></div>
        
        <!-- Logo & Text -->
        <div class="relative z-10 flex flex-col items-center animate-pulse-slow">
            <img src="{{ asset('images/360_laundry_logo.png') }}" alt="365laundry.lk" class="h-24 w-auto mb-6 bg-white/60 p-2 rounded-3xl border border-white/20 backdrop-blur-md shadow-2xl">
            <h1 class="text-4xl font-black text-white tracking-wider drop-shadow-lg">365laundry<span class="text-blue-300">.lk</span></h1>
            <p class="text-blue-100 mt-2 font-bold tracking-[0.2em] text-sm uppercase">Premium Care</p>
        </div>

        <!-- Progress Bar -->
        <div class="relative z-10 mt-16 w-72">
            <div class="flex justify-between text-xs font-bold text-white mb-3 uppercase tracking-wider">
                <span>Loading Experience</span>
                <span id="loader-percentage" class="text-white">0%</span>
            </div>
            <div class="w-full h-2 bg-blue-950/50 rounded-full overflow-hidden shadow-inner">
                <div id="loader-progress" class="h-full bg-white rounded-full w-0 transition-all duration-300 ease-out relative shadow-[0_0_15px_rgba(255,255,255,0.8)]">
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand -->
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/360_laundry_logo_site.png') }}" alt="365laundry.lk Logo" class="h-10 w-auto">
                </a>

                <!-- Right: Sign In + Book Now -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.login') }}"
                        class="text-slate-600 font-semibold hover:text-blue-600 transition text-sm hidden sm:block">My Account</a>
                    <a href="{{ route('customer.login') }}"
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
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 border-b border-slate-700 pb-12 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('images/360_laundry_logo_site.png') }}" alt="365laundry.lk Logo" class="h-10 w-auto bg-white/10 p-1.5 rounded-xl border border-white/20">
                    </div>
                    <p class="text-sm text-slate-400 mb-6 max-w-sm">Professional laundry and dry cleaning services with care and convenience delivered right to your doorstep.</p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full border border-slate-700 flex items-center justify-center hover:bg-accent hover:border-accent hover:text-white transition"><i class="fa-brands fa-whatsapp"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-5 uppercase tracking-wider text-xs">Quick Links</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('customer.login') }}" class="hover:text-white transition">Book Pickup</a></li>
                        <li><a href="{{ route('customer.login') }}" class="hover:text-white transition">My Account</a></li>
                        <li><a href="{{ route('customer.orders') }}" class="hover:text-white transition">Track Order</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-5 uppercase tracking-wider text-xs">Company & Legal</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('site.about') }}" class="hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('site.how-it-works') }}" class="hover:text-white transition">How It Works</a></li>
                        <li><a href="{{ route('site.quality') }}" class="hover:text-white transition">Quality Guarantee</a></li>
                        <li><a href="{{ route('site.faq') }}" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="{{ route('site.terms') }}" class="hover:text-white transition">Terms & Conditions</a></li>
                        <li><a href="{{ route('site.privacy') }}" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>

            </div>
            <div class="text-center text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} 365laundry.lk. All Rights Reserved.</p>
                <p class="mt-1 text-slate-600 text-xs font-medium">Powered by <a href="https://dsrzone.com"
                        target="_blank"
                        class="text-blue-500 hover:text-blue-400 font-bold transition-colors">DSRZone</a></p>
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

        // Loader Script
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('welcome-loader');
            const progress = document.getElementById('loader-progress');
            const percentage = document.getElementById('loader-percentage');
            const bubbleContainer = document.querySelector('.bubbles-container');
            
            // Generate Bubbles
            if (bubbleContainer) {
                for(let i=0; i<20; i++) {
                    let bubble = document.createElement('div');
                    bubble.className = 'bubble';
                    let size = Math.random() * 40 + 10;
                    bubble.style.width = size + 'px';
                    bubble.style.height = size + 'px';
                    bubble.style.left = Math.random() * 100 + '%';
                    bubble.style.animationDuration = (Math.random() * 5 + 4) + 's';
                    bubble.style.animationDelay = Math.random() * 3 + 's';
                    bubbleContainer.appendChild(bubble);
                }
            }

            // Image loading progress
            const images = document.querySelectorAll('img');
            const totalImages = images.length;
            let imagesLoaded = 0;
            let currentProgress = 0;
            let targetProgress = totalImages === 0 ? 100 : 0;
            
            // Simulate minimal loading even if fast
            let simulateInterval = setInterval(() => {
                if (targetProgress < 90) targetProgress += Math.random() * 10;
            }, 100);

            function updateUI() {
                if (currentProgress < targetProgress) {
                    currentProgress += (targetProgress - currentProgress) * 0.1;
                    if (targetProgress - currentProgress < 0.5) currentProgress = targetProgress;
                    
                    let p = Math.min(Math.round(currentProgress), 100);
                    if (progress) progress.style.width = p + '%';
                    if (percentage) percentage.innerText = p + '%';
                }
                
                if (currentProgress < 100) {
                    requestAnimationFrame(updateUI);
                } else {
                    if (loader) {
                        setTimeout(() => {
                            loader.style.opacity = '0';
                            setTimeout(() => loader.remove(), 700);
                        }, 400); // Small pause at 100%
                    }
                }
            }
            
            requestAnimationFrame(updateUI);

            if (totalImages > 0) {
                images.forEach(img => {
                    if (img.complete) {
                        imagesLoaded++;
                        targetProgress = (imagesLoaded / totalImages) * 100;
                    } else {
                        img.addEventListener('load', () => {
                            imagesLoaded++;
                            targetProgress = (imagesLoaded / totalImages) * 100;
                        });
                        img.addEventListener('error', () => {
                            imagesLoaded++;
                            targetProgress = (imagesLoaded / totalImages) * 100;
                        });
                    }
                });
            }

            // Fallback: forcefully finish loader on window.load
            window.addEventListener('load', () => {
                clearInterval(simulateInterval);
                targetProgress = 100;
            });
            
            // Absolute fallback in case of hanging
            setTimeout(() => {
                clearInterval(simulateInterval);
                targetProgress = 100;
            }, 5000);
        });
    </script>
</body>

</html>

