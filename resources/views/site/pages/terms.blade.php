@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl lg:text-4xl font-black text-brand mb-4">Terms and Conditions</h1>
        <p class="text-slate-600">Last updated: {{ date('F d, Y') }}</p>
    </div>
</div>

<div class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-slate prose-blue max-w-none">
            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">1. Introduction</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Welcome to 365laundry.lk. By accessing our website, using our mobile application, or utilizing our services, you agree to comply with and be bound by the following terms and conditions of use, which together with our privacy policy govern 365laundry.lk's relationship with you in relation to this website and service.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">2. Services Provided</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                365laundry.lk provides pick-up and delivery laundry, dry cleaning, and ironing services. We exercise the utmost care in cleaning and processing garments entrusted to us and use processes which, in our opinion, are best suited to the nature and conditions of each individual garment.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">3. Suitability for Cleaning</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                We evaluate every item upon receipt. If we deem an item unsuitable for our cleaning processes due to severe damage, extremely delicate material, or lack of care labels, we reserve the right to return it uncleaned. We are not responsible for damage to items that do not have a care label or have an incorrect care label.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">4. Pick-up and Delivery</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                We strive to pick up and deliver your garments within the requested time slots. However, we do not guarantee specific times and are not liable for delays caused by traffic, weather, or other unforeseen circumstances. 
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">5. Payment Terms</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Final pricing is determined after items are weighed/counted and inspected at our facility. You will be notified of the final bill before delivery. Payment must be cleared before or upon delivery of the cleaned items.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">6. Loss or Damage</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                In the rare event of loss or damage, our liability shall be limited to an amount not exceeding three (3) times the cleaning charge for the specific item, regardless of brand or original cost. Any claims must be made within 48 hours of delivery.
            </p>

            <h2 class="text-2xl font-bold text-slate-800 mb-4 mt-8">7. Unclaimed Items</h2>
            <p class="text-slate-600 mb-6 leading-relaxed">
                Garments not collected or undeliverable after 30 days from the original delivery date may be donated to charity or disposed of at our discretion.
            </p>
        </div>
    </div>
</div>
@endsection


