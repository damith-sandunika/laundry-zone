<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - 360 Laundry</title>
    <link rel="icon" type="image/png" href="{{ asset('images/360_laundry_logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .text-brand { color: #1e3a8a; }
        .text-accent { color: #2563eb; }
        .bg-brand { background-color: #1e3a8a; }
        .bg-accent { background-color: #2563eb; }
    </style>
</head>
<body class="antialiased text-slate-800 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden min-h-screen flex flex-col">

<!-- Navbar -->
<nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <!-- Logo + Brand Name -->
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <img src="{{ asset('images/360_laundry_logo_site.png') }}" alt="360 Laundry" class="h-10">
            </a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-6 lg:space-x-8 items-center font-medium text-sm">
                <a href="{{ route('customer.dashboard') }}" class="{{ request()->routeIs('customer.dashboard') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'text-slate-600 hover:text-blue-600 transition' }}">Dashboard</a>
                <a href="{{ route('customer.orders') }}" class="{{ request()->routeIs('customer.orders') || request()->routeIs('customer.orders.show') ? 'text-blue-600 border-b-2 border-blue-600 pb-1' : 'text-slate-600 hover:text-blue-600 transition' }}">My Orders</a>
                <a href="{{ route('customer.booking') }}" class="text-slate-600 hover:text-blue-600 transition">Book Pickup</a>
                <a href="#" class="text-slate-600 hover:text-blue-600 transition">Ratings</a>
                
                <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 font-bold hover:text-red-700 transition flex items-center gap-1">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 hover:text-blue-600 focus:outline-none p-2">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" style="display:none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" style="display:none" class="md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg z-50">
        <div class="px-4 pt-2 pb-6 space-y-1">
            <a href="{{ route('customer.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('customer.dashboard') ? 'text-blue-600 bg-blue-50' : 'text-slate-700 hover:bg-slate-50' }}">Dashboard</a>
            <a href="{{ route('customer.orders') }}" class="block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('customer.orders') || request()->routeIs('customer.orders.show') ? 'text-blue-600 bg-blue-50' : 'text-slate-700 hover:bg-slate-50' }}">My Orders</a>
            <a href="{{ route('customer.booking') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-50">Book Pickup</a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-50">Ratings</a>
            
            <div class="mt-4 pt-4 border-t border-slate-100">
                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left block px-3 py-2 rounded-md text-base font-bold text-red-600 hover:bg-red-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
@if (isset($header))
    <header class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        {{ $header }}
    </header>
@endif

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl font-bold flex items-center gap-2 mb-6">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl font-bold flex items-center gap-2 mb-6">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl font-bold flex flex-col gap-1 mb-6">
            @foreach($errors->all() as $error)
                <div class="flex items-center gap-2"><i class="fa-solid fa-xmark"></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{ $slot }}
</main>

<!-- Footer -->
<footer class="bg-white border-t border-slate-200 mt-auto py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-bold text-brand mb-2">360 Laundry</p>
            <p class="text-xs text-slate-500 mb-4">&copy; {{ date('Y') }} All Rights Reserved.</p>
            <p class="text-[10px] font-extrabold text-slate-400 tracking-widest uppercase">Powered by <a href="https://dsrzone.com" target="_blank" class="text-blue-500 hover:text-blue-400 font-black transition-colors">DSRZone</a></p>
        </div>
    </div>
</footer>

@if (isset($scripts))
    {{ $scripts }}
@endif

</body>
</html>


