<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — {{ $branch->name ?? '360 Laundry' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Background Details -->
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.05)_1px,transparent_0)] bg-[size:32px_32px]"></div>
        <div class="absolute -top-[10%] -right-[5%] w-[400px] h-[400px] rounded-full bg-[radial-gradient(circle,rgba(59,130,246,0.35)_0%,transparent_70%)] animate-pulse"></div>
    </div>

    <div class="w-full max-w-[480px] bg-white rounded-3xl shadow-2xl p-10 text-center relative z-10 animate-slide-up">
        
        <div class="w-24 h-24 rounded-full bg-green-50 text-green-500 mx-auto mb-6 flex items-center justify-center shadow-[0_0_0_12px_rgba(22,163,74,0.05)] border-2 border-green-100 animate-bounce">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>

        <h1 class="text-3xl font-black text-slate-900 tracking-tight mb-3">Booking Received!</h1>
        <p class="text-slate-500 font-semibold mb-8 leading-relaxed">Thank you, <strong class="text-slate-800">{{ auth('customer')->user()->name ?? 'Valued Customer' }}</strong>. Your laundry pickup has been scheduled successfully.</p>
        
        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 mb-8 text-left space-y-4 shadow-inner">
            <div class="flex justify-between items-center pb-4 border-b border-slate-200">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Order Number</span>
                <span class="text-sm font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">#{{ $appointment->order->order_number ?? $appointment->id }}</span>
            </div>
            <div class="flex justify-between items-center pb-4 border-b border-slate-200">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Date</span>
                <span class="text-sm font-black text-slate-700">{{ \Carbon\Carbon::parse($appointment->pickup_scheduled_at)->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Status</span>
                <span class="text-xs font-black text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    PENDING CONFIRMATION
                </span>
            </div>
        </div>

        <div class="flex flex-col gap-3 w-full">
            <a href="{{ route('customer.booking') }}" class="inline-flex w-full justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300">
                + Book Another Pickup
            </a>
            <a href="{{ route('customer.dashboard') }}" class="inline-flex w-full justify-center items-center gap-2 px-8 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Back to Dashboard
            </a>
        </div>
        
        <p class="mt-6 text-xs font-bold text-slate-400 flex items-center justify-center gap-2">
            <span>📞</span> You will receive a WhatsApp message shortly.
        </p>
    </div>
</body>
</html>

