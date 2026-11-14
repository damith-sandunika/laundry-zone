<footer class="bg-[#0f172a] text-slate-300 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 border-b border-slate-700 pb-12 mb-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-4">
                    <img src="{{ asset('images/laundry_site_logo.png') }}" alt="{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} Logo" class="h-10 w-auto bg-white/10 p-1.5 rounded-xl border border-white/20">
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
            <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}. All Rights Reserved.</p>
            <p class="mt-1 text-slate-600 text-xs font-medium">Powered by <a href="https://dsrzone.com" target="_blank" class="text-blue-500 hover:text-blue-400 font-bold transition-colors">DSRZone</a></p>
        </div>
    </div>
</footer>

<script>
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-up-enter-active');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

        document.querySelectorAll('section:not(:first-of-type)').forEach(section => {
            section.classList.add('fade-up-enter');
            observer.observe(section);
        });

        const loader = document.getElementById('welcome-loader');
        const progress = document.getElementById('loader-progress');
        const percentage = document.getElementById('loader-percentage');
        const bubbleContainer = document.querySelector('.bubbles-container');
        
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

        const images = document.querySelectorAll('img');
        const totalImages = images.length;
        let imagesLoaded = 0;
        let currentProgress = 0;
        let targetProgress = totalImages === 0 ? 100 : 0;
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
                    }, 400);
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
                    img.addEventListener('load', () => { imagesLoaded++; targetProgress = (imagesLoaded / totalImages) * 100; });
                    img.addEventListener('error', () => { imagesLoaded++; targetProgress = (imagesLoaded / totalImages) * 100; });
                }
            });
        }
        window.addEventListener('load', () => { clearInterval(simulateInterval); targetProgress = 100; });
        setTimeout(() => { clearInterval(simulateInterval); targetProgress = 100; }, 5000);
    });
</script>

