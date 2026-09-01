@extends('layouts.site')

@section('content')
<div class="bg-blue-50 py-16 lg:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-black text-brand mb-4">Quality Guarantee</h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">We don't just wash clothes; we care for them. Experience the 365 Laundry standard of excellence.</p>
    </div>
</div>

<div class="py-16 lg:py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid md:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 md:order-1">
                <h2 class="text-3xl font-black text-slate-800 mb-6">Our Promise to You</h2>
                <p class="text-slate-600 text-lg leading-relaxed mb-6">
                    At 365 Laundry, quality isn't just a buzzword—it's the foundation of everything we do. We understand that your clothes are an investment, and we treat them with the utmost respect.
                </p>
                <p class="text-slate-600 text-lg leading-relaxed">
                    If you are ever unsatisfied with the cleaning quality of an item, simply let us know within 24 hours of delivery. We will gladly re-clean the item for you completely free of charge. No questions asked.
                </p>
            </div>
            <div class="order-1 md:order-2 flex justify-center">
                <div class="w-64 h-64 bg-amber-50 rounded-full flex items-center justify-center relative shadow-inner border-8 border-white outline outline-1 outline-slate-100">
                    <i class="fa-solid fa-award text-9xl text-amber-400 drop-shadow-md"></i>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="p-8 bg-slate-50 rounded-2xl border border-slate-100 text-center hover:-translate-y-1 transition-transform duration-300">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-2xl mx-auto mb-6 shadow-sm text-blue-600">
                    <i class="fa-solid fa-shirt"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Fabric Protection</h3>
                <p class="text-slate-500">We carefully read care labels and sort garments to ensure they are treated with the correct temperatures and solvents, preventing shrinkage and color bleeding.</p>
            </div>

            <div class="p-8 bg-slate-50 rounded-2xl border border-slate-100 text-center hover:-translate-y-1 transition-transform duration-300">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-2xl mx-auto mb-6 shadow-sm text-blue-600">
                    <i class="fa-solid fa-spray-can-sparkles"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Advanced Stain Removal</h3>
                <p class="text-slate-500">Our laundry experts are trained in advanced spot-treatment techniques to tackle tough stains like wine, grease, and ink before the main wash.</p>
            </div>

            <div class="p-8 bg-slate-50 rounded-2xl border border-slate-100 text-center hover:-translate-y-1 transition-transform duration-300">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-2xl mx-auto mb-6 shadow-sm text-blue-600">
                    <i class="fa-solid fa-hands-holding-circle"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-3">Expert Hand Finishing</h3>
                <p class="text-slate-500">Every item goes through a rigorous quality check before delivery. Shirts are crisply ironed, and garments are folded or hung perfectly to avoid creases.</p>
            </div>
        </div>

    </div>
</div>
@endsection
