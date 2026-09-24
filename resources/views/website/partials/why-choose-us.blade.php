    <!-- Why Choose Us & Features -->
    <section class="py-16 lg:py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-5xl font-extrabold text-brand mb-4">Why Choose {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}?</h2>
                <p class="text-slate-500 max-w-2xl mx-auto font-medium">We combine state-of-the-art technology with expert care to give your clothes the premium treatment they deserve.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Experienced Team</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Trained professionals.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-cogs"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Advanced Equipment</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Modern machines.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-shield-virus"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Hygienic Cleaning</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Deep cleaning care.</p>
                </div>
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-xl sm:text-2xl mb-4">
                        <i class="fa-solid fa-thumbs-up"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 text-sm sm:text-base mb-1">100% Satisfaction</h4>
                    <p class="text-xs sm:text-sm text-slate-500">Best service assured.</p>
                </div>
            </div>

        </div>
    </section>