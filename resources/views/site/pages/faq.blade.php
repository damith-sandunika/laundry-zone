@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-black text-brand mb-4">Frequently Asked Questions</h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">Got questions? We've got answers. If you can't find what you're looking for, feel free to contact us.</p>
    </div>
</div>

<div class="py-16 lg:py-24 bg-white" x-data="{ activeAccordion: 1 }">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- FAQ 1 -->
        <div class="mb-4 border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
            <button @click="activeAccordion = activeAccordion === 1 ? null : 1" class="w-full px-6 py-5 text-left flex justify-between items-center bg-white hover:bg-slate-50 transition-colors">
                <span class="font-bold text-lg text-slate-800">What is the turnaround time for my laundry?</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="activeAccordion === 1 ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 1" x-collapse class="px-6 pb-5 text-slate-600 bg-white">
                Our standard turnaround time is 48 hours from the time of pickup. For express services (if available in your area), we can deliver within 24 hours for an additional charge.
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="mb-4 border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
            <button @click="activeAccordion = activeAccordion === 2 ? null : 2" class="w-full px-6 py-5 text-left flex justify-between items-center bg-white hover:bg-slate-50 transition-colors">
                <span class="font-bold text-lg text-slate-800">Do I need to separate my laundry?</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="activeAccordion === 2 ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 2" x-collapse class="px-6 pb-5 text-slate-600 bg-white">
                No, you don't need to separate your clothes! Our expert team will carefully sort your laundry by color, fabric type, and washing requirements before processing.
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="mb-4 border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
            <button @click="activeAccordion = activeAccordion === 3 ? null : 3" class="w-full px-6 py-5 text-left flex justify-between items-center bg-white hover:bg-slate-50 transition-colors">
                <span class="font-bold text-lg text-slate-800">How do I pay for my order?</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="activeAccordion === 3 ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 3" x-collapse class="px-6 pb-5 text-slate-600 bg-white">
                Once your laundry reaches our workshop and is inspected, we will finalize the bill and send you a notification (via SMS/WhatsApp). You can then pay online via bank transfer or choose cash on delivery.
            </div>
        </div>

        <!-- FAQ 4 -->
        <div class="mb-4 border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
            <button @click="activeAccordion = activeAccordion === 4 ? null : 4" class="w-full px-6 py-5 text-left flex justify-between items-center bg-white hover:bg-slate-50 transition-colors">
                <span class="font-bold text-lg text-slate-800">What if I have special instructions for a garment?</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="activeAccordion === 4 ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 4" x-collapse class="px-6 pb-5 text-slate-600 bg-white">
                When placing your order on the app or website, you can add notes or special instructions. Alternatively, you can inform our rider during pickup, and they will tag your bag accordingly.
            </div>
        </div>

        <!-- FAQ 5 -->
        <div class="mb-4 border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
            <button @click="activeAccordion = activeAccordion === 5 ? null : 5" class="w-full px-6 py-5 text-left flex justify-between items-center bg-white hover:bg-slate-50 transition-colors">
                <span class="font-bold text-lg text-slate-800">What do I put my clothes in for pickup?</span>
                <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-300" :class="activeAccordion === 5 ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="activeAccordion === 5" x-collapse class="px-6 pb-5 text-slate-600 bg-white">
                For your first order, you can put your clothes in any disposable bag (like a garbage bag or shopping bag). When we return your clean clothes, we will provide a reusable {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} bag for future pickups!
            </div>
        </div>

    </div>
</div>
@endsection


