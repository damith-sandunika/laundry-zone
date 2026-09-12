<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Login - 365laundry.lk</title>
    <link rel="icon" type="image/png" href="{{ asset('images/360_laundry_logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-['Outfit'] min-h-screen flex flex-col justify-center items-center p-4 antialiased">
    
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden p-8 sm:p-10">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block">
                <img src="{{ asset('images/360_laundry_logo_site.png') }}" alt="Logo" class="h-12 w-auto">
            </a>
            <p class="text-slate-500 mt-2 text-sm">Welcome back! Please login to your account.</p>
        </div>

        <!-- Form Card -->
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
                            <a href="{{ route('customer.password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-500">Forgot Password?</a>
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

    </div>

</body>
</html>


