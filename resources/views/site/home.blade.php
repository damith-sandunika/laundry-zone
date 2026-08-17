<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Zone - Premium Laundry Delivery</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .hero-gradient { background: linear-gradient(135deg, #0ea5e9, #3b82f6); }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex-shrink-0 flex items-center">
                <span class="text-2xl mr-2">👕</span>
                <span class="font-bold text-xl text-slate-800">Laundry Zone</span>
            </div>
            <div>
                <a href="{{ route('login') }}" class="text-slate-600 font-medium hover:text-blue-600 mr-4">Sign In</a>
                <a href="{{ route('booking.page') }}" class="bg-blue-600 text-white px-5 py-2 rounded-full font-semibold hover:bg-blue-700 transition">Book Now</a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-gradient text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight">Premium Laundry & Dry Cleaning <br> Delivered to Your Door.</h1>
        <p class="text-lg md:text-xl text-blue-100 mb-10 max-w-2xl mx-auto">We pick up, clean, and deliver your clothes so you can focus on what matters most.</p>
        <a href="{{ route('booking.page') }}" class="inline-block bg-white text-blue-600 px-8 py-4 rounded-full font-bold text-lg shadow-lg hover:shadow-xl hover:bg-slate-50 transition">Schedule a Pickup</a>
    </div>
</section>

<!-- Services Section -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-slate-800 mb-12">Our Services</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($services as $service)
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition text-center">
                <div class="text-4xl mb-4">{{ $service->icon }}</div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $service->name }}</h3>
                <p class="text-slate-500 text-sm mb-4">Professional {{ strtolower($service->name) }} service.</p>
                <div class="font-semibold text-blue-600">From Rs.{{ number_format($service->base_price, 2) }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How it works -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-slate-800 mb-12">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mb-4">1</div>
                <h3 class="font-bold text-slate-800 mb-2">Book Online</h3>
                <p class="text-slate-500 text-sm">Schedule a pickup date and location via our simple form.</p>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mb-4">2</div>
                <h3 class="font-bold text-slate-800 mb-2">We Pick Up</h3>
                <p class="text-slate-500 text-sm">Our rider will collect your laundry at the scheduled time.</p>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mb-4">3</div>
                <h3 class="font-bold text-slate-800 mb-2">We Clean</h3>
                <p class="text-slate-500 text-sm">Expert washing, drying, folding or dry cleaning.</p>
            </div>
            <div>
                <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mb-4">4</div>
                <h3 class="font-bold text-slate-800 mb-2">We Deliver</h3>
                <p class="text-slate-500 text-sm">Fresh, clean clothes delivered back to your door.</p>
            </div>
        </div>
    </div>
</section>

<!-- Branches -->
<section class="py-16 bg-slate-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-12">Our Branches</h2>
        <div class="flex flex-wrap justify-center gap-6">
            @foreach($branches as $branch)
            <div class="bg-slate-700 px-6 py-4 rounded-xl">
                <div class="font-bold text-lg">{{ $branch->name }}</div>
                <div class="text-slate-400 text-sm">{{ $branch->address }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-slate-900 text-slate-400 py-8 text-center text-sm">
    <p>&copy; {{ date('Y') }} Laundry Zone. All rights reserved.</p>
</footer>

</body>
</html>
