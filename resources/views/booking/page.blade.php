<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Laundry Pickup - {{ $branch->name ?? \App\Models\Setting::get('company_name', 'Laundry Zone') }}</title>
    <meta name="description" content="Book your laundry pickup online. Fast, easy and convenient.">
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

    <div class="w-full max-w-[560px] bg-white rounded-3xl shadow-2xl overflow-hidden relative z-10 animate-slide-up">
        
        <!-- Header -->
        <div class="bg-gradient-to-br from-blue-700 to-indigo-700 p-8 text-center relative">
            <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mb-2">Book a Laundry Pickup</h1>
            <p class="text-sm font-semibold text-blue-200">Schedule your pickup — we'll handle the rest</p>
            
            @if($branch)
            <div class="inline-flex items-center gap-2 mt-5 bg-white/10 border border-white/20 backdrop-blur-md px-4 py-1.5 rounded-full">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-bold text-white/90">{{ $branch->name }}</span>
            </div>
            @endif
        </div>

        <!-- Body -->
        <div class="p-8">
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm font-bold mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                {{ $errors->first() }}
            </div>
            @endif

            <form action="{{ route('customer.booking.store') }}" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id ?? 1 }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Your Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Kamal Perera" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Phone Number *</label>
                        <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="077 123 4567" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Email Address</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}" placeholder="kamal@gmail.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Pickup Address</label>
                    <input type="text" name="customer_address" value="{{ old('customer_address') }}" placeholder="No. 12, Main Street, Colombo" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">What services do you need? *</label>
                    <textarea name="service_description" placeholder="e.g. 5 shirts, 2 trousers, 1 bedsheet for wash & iron..." required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400 min-h-[100px] resize-y">{{ old('service_description') }}</textarea>
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Preferred Pickup Scheduled Date *</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-600 uppercase tracking-widest">Additional Notes</label>
                    <textarea name="notes" placeholder="Any special instructions..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder:text-slate-400 min-h-[80px] resize-y">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="w-full mt-4 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-sm rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-1 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Confirm Booking
                </button>
            </form>

            <p class="text-center text-xs font-semibold text-slate-400 mt-6">
                📞 We'll send a confirmation to your WhatsApp number shortly.
            </p>
        </div>
    </div>
</body>
</html>


