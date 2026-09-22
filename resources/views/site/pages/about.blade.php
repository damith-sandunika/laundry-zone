@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-black text-brand mb-4">About Us</h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">We are on a mission to give you back your most valuable asset—your time.</p>
    </div>
</div>

<div class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <img src="{{ asset('images/laundry_slider_desktop_1.png') }}" alt="Our Team" class="rounded-2xl shadow-lg w-full object-cover h-[400px]">
            </div>
            <div>
                <h2 class="text-3xl font-black text-slate-800 mb-6">Our Story</h2>
                <div class="space-y-4 text-slate-600 leading-relaxed">
                    <p>
                        At {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}, we believe that doing laundry shouldn't take away from the things you love doing. That’s why we’ve built a seamless, on-demand laundry and dry-cleaning service designed around your busy lifestyle.
                    </p>
                    <p>
                        Founded by a team of professionals who were tired of spending their weekends washing and ironing, we understand the value of convenience and quality. We use state-of-the-art cleaning technologies and eco-friendly detergents to ensure your clothes are not just clean, but cared for.
                    </p>
                    <p>
                        From our humble beginnings, we have grown into a trusted partner for households and businesses alike, offering reliable pickup and delivery right to your doorstep.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-black text-slate-800 mb-12">Our Core Values</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Eco-Friendly</h3>
                <p class="text-slate-500 text-sm">We use biodegradable detergents that are tough on stains but gentle on the environment and your fabrics.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Reliability</h3>
                <p class="text-slate-500 text-sm">We value your time. Our 48-hour standard turnaround ensures you always have fresh clothes when you need them.</p>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-2xl mx-auto mb-6">
                    <i class="fa-solid fa-gem"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Premium Care</h3>
                <p class="text-slate-500 text-sm">Every garment is inspected, treated, and handled by laundry experts to maintain its pristine condition.</p>
            </div>
        </div>
    </div>
</div>
@endsection


