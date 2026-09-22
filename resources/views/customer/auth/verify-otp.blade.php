<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify OTP - {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/laundry_main_logo.png') }}">
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
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <img src="{{ asset('images/laundry_site_logo.png') }}" alt="Logo" class="h-12 w-auto">
            </a>
            <p class="text-slate-500 mt-2 text-sm">Please enter the 4-digit code sent to your phone.</p>
        </div>

        <!-- Form Card -->
        <div class="glass-card rounded-xl shadow-xl overflow-hidden">
            <div class="p-8">
                <form method="POST" action="{{ route('customer.password.verifyOtp') }}" class="space-y-5">
                    @csrf

                    @if (session('success'))
                        <div class="bg-green-50 text-green-600 p-4 rounded-xl text-sm font-medium border border-green-100 mb-6 text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-medium border border-red-100 mb-6">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- OTP Code -->
                    <div>
                        <label for="otp" class="block text-sm font-bold text-slate-700 mb-1.5 text-center">4-Digit Code</label>
                        <input id="otp" type="text" name="otp" required autofocus maxlength="4" placeholder="••••"
                            class="block w-full text-center text-3xl tracking-[1em] rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all py-3 font-mono">
                    </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        Verify Code
                    </button>
                </form>
            </div>
            
            <div class="px-8 py-5 bg-slate-50 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-600 font-medium">
                    Didn't receive the code? 
                    <a href="{{ route('customer.password.request') }}" class="font-bold text-blue-600 hover:text-blue-500">Resend Code</a>
                </p>
            </div>
        </div>

    </div>

</body>
</html>
