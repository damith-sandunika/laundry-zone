<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — {{ $branch->name ?? 'Laundry Zone' }}</title>
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
        <p class="text-slate-500 font-semibold mb-8 leading-relaxed">Thank you, <strong class="text-slate-800">{{ $booking->customer_name }}</strong>. Your laundry pickup has been scheduled successfully.</p>
        
        <div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 mb-8 text-left space-y-4 shadow-inner">
            <div class="flex justify-between items-center pb-4 border-b border-slate-200">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Booking ID</span>
                <span class="text-sm font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-lg">#{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex justify-between items-center pb-4 border-b border-slate-200">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Date</span>
                <span class="text-sm font-black text-slate-700">{{ $booking->preferred_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Status</span>
                <span class="text-xs font-black text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    PENDING CONFIRMATION
                </span>
            </div>
        </div>

        <a href="{{ route('booking.page') }}" class="inline-flex w-full justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300">
            Book Another Order
        </a>
        
        <p class="mt-6 text-xs font-bold text-slate-400 flex items-center justify-center gap-2">
            <span>📞</span> You will receive a WhatsApp message shortly.
        </p>
    </div>
</body>
</html>
