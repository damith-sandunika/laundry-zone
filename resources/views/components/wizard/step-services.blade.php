            <!-- Step 1/3: Services (Drill-Down UI) -->
            <div x-show="currentView === 'services'" x-transition.opacity.duration.300ms class="pb-12">
                @if(!$isHomepage)
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-slate-800">Select Services</h3>
                    <p class="text-sm text-slate-500 mt-1">Navigate through categories to add items to your cart.</p>
                </div>
                @endif

                <!-- Drill Down Container -->
                <div class="flex flex-col">
                    
                    <!-- Header / Navigation Bar -->
                    <div class="flex items-center justify-center mb-6 sm:mb-8">
                        <!-- Breadcrumbs -->
                        <div class="inline-flex items-center justify-center flex-wrap gap-2 sm:gap-3 text-sm sm:text-base font-bold text-slate-800 bg-white px-5 sm:px-8 py-2.5 sm:py-3.5 rounded-full shadow-sm border border-slate-100">
                            <button type="button" @click="currentViewLevel = 0" class="hover:text-blue-600 transition-colors flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-blue-500"></i> All Categories
                            </button>
                            
                            <span x-show="currentViewLevel > 0" class="text-slate-300"><i class="fa-solid fa-chevron-right text-[10px] sm:text-xs"></i></span>
                            
                            <button type="button" x-show="currentViewLevel > 0" @click="currentViewLevel = 1" class="hover:text-blue-600 transition-colors" x-text="selectedMainCategory?.name"></button>
                            
                            <span x-show="currentViewLevel > 1" class="text-slate-300"><i class="fa-solid fa-chevron-right text-[10px] sm:text-xs"></i></span>
                            
                            <span x-show="currentViewLevel > 1" class="text-blue-600" x-text="selectedSubcategory?.name || selectedMainCategory?.name"></span>
                        </div>
                    </div>

                    <div class="flex-1">
                        <!-- Level 0: Main Categories Grid -->
                        <div x-show="currentViewLevel === 0">
                            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                                <template x-for="cat in categories" :key="cat.id">
                                    <button type="button" @click="selectLevel1(cat)" class="w-[30%] sm:w-[22%] md:w-[18%] bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group">
                                        <div class="w-full aspect-[4/3] bg-blue-50 text-blue-600 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="cat.image_path">
                                                    <img :src="cat.image_path?.startsWith('http') ? cat.image_path : '/' + cat.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!cat.image_path">
                                                    <i class="fa-solid fa-shirt text-4xl opacity-40"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-4 w-full bg-white relative z-10 border-t border-slate-50 flex-1 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full" x-text="cat.name"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Level 1: Subcategories Grid -->
                        <div x-show="currentViewLevel === 1">
                            <div class="flex flex-wrap justify-center gap-3 sm:gap-4">
                                <template x-for="sub in currentSubcategories" :key="sub.id">
                                    <button type="button" @click="selectLevel2(sub)" class="w-[30%] sm:w-[22%] md:w-[18%] bg-white rounded-xl overflow-hidden flex flex-col border border-slate-100 shadow-sm hover:border-blue-200 hover:shadow-md transition-all group">
                                        <div class="w-full aspect-[4/3] bg-slate-50 text-slate-400 flex items-center justify-center relative overflow-hidden">
                                            <div class="absolute inset-0 transition-transform duration-300 group-hover:scale-110 flex items-center justify-center">
                                                <template x-if="sub.image_path">
                                                    <img :src="sub.image_path?.startsWith('http') ? sub.image_path : '/' + sub.image_path" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!sub.image_path">
                                                    <i class="fa-solid fa-layer-group text-3xl opacity-40 group-hover:text-blue-400 transition-colors"></i>
                                                </template>
                                            </div>
                                        </div>
                                        <div class="p-2 sm:p-3 w-full bg-white relative z-10 border-t border-slate-50 flex-1 flex items-center justify-center">
                                            <span class="text-xs sm:text-sm font-bold text-slate-700 group-hover:text-blue-700 text-center block w-full" x-text="sub.name"></span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="currentSubcategories.length === 0" class="col-span-full py-12 text-center text-slate-500 text-sm font-medium">
                                    No subcategories found.
                                </div>
                            </div>
                        </div>

                        <!-- Level 2: Services List -->
                        <div x-show="currentViewLevel === 2" class="space-y-4">
                            <template x-for="service in currentServices" :key="service.id">
                                <div class="bg-white border-2 border-slate-100 rounded-xl p-4 flex gap-4 transition-all hover:border-blue-200 hover:shadow-md cursor-pointer group" @click="openQtyModal(service)">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0 text-blue-600 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-jug-detergent"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-bold text-slate-800 text-sm group-hover:text-blue-700 transition-colors" x-text="service.name"></div>
                                        <div class="text-[10px] text-slate-500 font-medium mt-0.5 leading-tight" x-text="service.description || 'Professional cleaning'"></div>
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="text-blue-600 font-bold text-sm">LKR <span x-text="service.base_price"></span></div>
                                            <div class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : (service.price_type === 'per_pair' ? 'Per Pair' : 'Per Item'))"></div>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center">
                                        <button type="button" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors flex items-center justify-center shadow-sm">
                                            <i class="fa-solid fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="currentServices.length === 0" class="py-12 text-center text-slate-500 text-sm font-medium">
                                No services found in this category.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('components.wizard.partials.bottom_cart_bar')
            @include('components.wizard.partials.checkout_modals')
