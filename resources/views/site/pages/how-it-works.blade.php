@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-black text-brand mb-4">How It Works</h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">Clean laundry at your fingertips. Washing clothes was never so convenient.</p>
    </div>
</div>

<div class="py-16 lg:py-24 bg-white relative">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Step 1 -->
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-16 mb-16 lg:mb-24 relative z-10">
            <div class="w-full md:w-1/2 flex justify-center md:justify-end">
                <div class="w-64 h-64 bg-slate-100 rounded-full flex items-center justify-center relative shadow-inner">
                    <div class="absolute -right-4 -top-4 w-12 h-12 bg-accent text-white rounded-full flex items-center justify-center text-xl font-black shadow-lg">1</div>
                    <i class="fa-solid fa-mobile-screen-button text-7xl text-blue-400"></i>
                </div>
            </div>
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h3 class="text-3xl font-black text-slate-800 mb-3">Place Your Order</h3>
                <p class="text-slate-600 text-lg leading-relaxed">
                    Click on "Book Now", select the services you need, and choose a date and time that fits your schedule.
                </p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="flex flex-col md:flex-row-reverse items-center gap-8 md:gap-16 mb-16 lg:mb-24 relative z-10">
            <div class="w-full md:w-1/2 flex justify-center md:justify-start">
                <div class="w-64 h-64 bg-slate-100 rounded-full flex items-center justify-center relative shadow-inner">
                    <div class="absolute -left-4 -top-4 w-12 h-12 bg-accent text-white rounded-full flex items-center justify-center text-xl font-black shadow-lg">2</div>
                    <i class="fa-solid fa-truck-pickup text-7xl text-blue-400"></i>
                </div>
            </div>
            <div class="w-full md:w-1/2 text-center md:text-right">
                <h3 class="text-3xl font-black text-slate-800 mb-3">We Pick It Up</h3>
                <p class="text-slate-600 text-lg leading-relaxed">
                    Our friendly rider will arrive at your doorstep to collect your dirty laundry. You can hand it over in any bag.
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="flex flex-col md:flex-row items-center gap-8 md:gap-16 mb-16 lg:mb-24 relative z-10">
            <div class="w-full md:w-1/2 flex justify-center md:justify-end">
                <div class="w-64 h-64 bg-slate-100 rounded-full flex items-center justify-center relative shadow-inner">
                    <div class="absolute -right-4 -top-4 w-12 h-12 bg-accent text-white rounded-full flex items-center justify-center text-xl font-black shadow-lg">3</div>
                    <i class="fa-solid fa-hands-bubbles text-7xl text-blue-400"></i>
                </div>
            </div>
            <div class="w-full md:w-1/2 text-center md:text-left">
                <h3 class="text-3xl font-black text-slate-800 mb-3">We Clean with Care</h3>
                <p class="text-slate-600 text-lg leading-relaxed">
                    Your clothes are inspected, sorted, and cleaned by professionals using premium detergents in our state-of-the-art facility.
                </p>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="flex flex-col md:flex-row-reverse items-center gap-8 md:gap-16 relative z-10">
            <div class="w-full md:w-1/2 flex justify-center md:justify-start">
                <div class="w-64 h-64 bg-slate-100 rounded-full flex items-center justify-center relative shadow-inner">
                    <div class="absolute -left-4 -top-4 w-12 h-12 bg-accent text-white rounded-full flex items-center justify-center text-xl font-black shadow-lg">4</div>
                    <i class="fa-solid fa-box-open text-7xl text-blue-400"></i>
                </div>
            </div>
            <div class="w-full md:w-1/2 text-center md:text-right">
                <h3 class="text-3xl font-black text-slate-800 mb-3">Sit Back and Relax</h3>
                <p class="text-slate-600 text-lg leading-relaxed">
                    We'll deliver your fresh, clean, and perfectly pressed laundry back to you within 48 hours. Ready to wear!
                </p>
            </div>
        </div>

        <!-- Connecting Line (Desktop Only) -->
        <div class="hidden md:block absolute left-1/2 top-24 bottom-24 w-1 bg-slate-200 -translate-x-1/2 z-0 rounded-full"></div>
    </div>
</div>

<div class="py-16 bg-slate-900 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <h2 class="text-3xl font-black text-white mb-6">Ready to get started?</h2>
        <a href="{{ route('customer.booking') }}" class="inline-block bg-accent text-white px-8 py-4 rounded-xl font-black text-lg hover:bg-blue-600 transition shadow-lg">
            Book a Pickup Now
        </a>
    </div>
</div>
@endsection


