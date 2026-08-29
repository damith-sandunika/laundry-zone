<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Login - RAPID Laundromat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2">
                <img src="{{ asset('images/rapid_laundromat_logo_site.png') }}" alt="Logo" class="h-12 w-auto">
                <span class="font-black text-2xl tracking-tight text-blue-600">RAPID Laundromat</span>
            </a>
            <p class="text-slate-500 mt-2 text-sm">Welcome back! Please login to your account.</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-2xl shadow-xl overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('customer.login.submit') }}" class="space-y-5">
                    @csrf

                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-medium border border-red-100 mb-6">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-bold text-slate-700 mb-1.5">Phone Number</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-phone"></i>
                            </div>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required autofocus
                                class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all text-sm py-2.5">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-bold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-semibold text-blue-600 hover:text-blue-500">Forgot password?</a>
                        </div>
                        <div class="relative" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required
                                class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all text-sm py-2.5 pr-10">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input id="remember" type="checkbox" name="remember" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <label for="remember" class="ml-2 block text-sm font-medium text-slate-600">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        Sign In
                    </button>
                </form>
            </div>
            
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-600 font-medium">
                    Don't have an account? 
                    <a href="{{ route('customer.register') }}" class="font-bold text-blue-600 hover:text-blue-500">Sign up</a>
                </p>
        </div>

        <!-- App Download Banner -->
        <div class="mt-8 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white text-center shadow-xl relative overflow-hidden animate-slide-up" style="animation-delay: 200ms;">
            <div class="absolute -right-6 -bottom-6 opacity-20 transform rotate-12">
                <i class="fa-solid fa-mobile-screen-button text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-lg font-black mb-1">Get the RAPID Laundromat App!</h3>
                <p class="text-blue-100 text-sm mb-5 font-medium">Book pickups, track orders, and pay online effortlessly.</p>
                <div class="flex justify-center gap-3">
                    <a href="#" class="bg-black/80 hover:bg-black text-white px-4 py-2.5 rounded-xl flex items-center gap-2.5 transition-all shadow-lg shadow-black/20 hover:-translate-y-1">
                        <i class="fa-brands fa-google-play text-2xl"></i>
                        <div class="text-left leading-none">
                            <div class="text-[8px] uppercase tracking-wider text-slate-300 mb-0.5">Get it on</div>
                            <div class="text-[13px] font-bold">Google Play</div>
                        </div>
                    </a>
                    <a href="#" class="bg-black/80 hover:bg-black text-white px-4 py-2.5 rounded-xl flex items-center gap-2.5 transition-all shadow-lg shadow-black/20 hover:-translate-y-1">
                        <i class="fa-brands fa-apple text-2xl mb-1"></i>
                        <div class="text-left leading-none">
                            <div class="text-[8px] uppercase tracking-wider text-slate-300 mb-0.5">Download on the</div>
                            <div class="text-[13px] font-bold">App Store</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
