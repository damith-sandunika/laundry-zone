<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - 365 Laundry</title>
    <link rel="icon" type="image/png" href="{{ asset('images/365_laundry_logo.png') }}">
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
                <img src="{{ asset('images/365_laundry_logo_site.png') }}" alt="Logo" class="h-12 w-auto">
            </a>
            <p class="text-slate-500 mt-2 text-sm">Forgot your password? No problem. Just let us know your phone number and we will send you a reset code via WhatsApp/SMS.</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-xl shadow-xl overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('customer.password.email') }}" class="space-y-5">
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

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        Send Reset Code
                    </button>
                </form>
            </div>
            
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-600 font-medium">
                    Remembered your password? 
                    <a href="{{ route('customer.login') }}" class="font-bold text-blue-600 hover:text-blue-500">Sign in</a>
                </p>
            </div>
        </div>

    </div>

</body>
</html>
