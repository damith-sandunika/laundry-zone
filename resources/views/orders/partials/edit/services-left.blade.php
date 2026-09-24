            <!-- ===================== Left: Services ===================== -->
            <div class="flex-1 lg:flex-[1.2] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">
                
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>

                <!-- Main Category Filters (Wrapped) -->
                <div class="border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10 relative p-3">
                    <div class="flex flex-wrap gap-2">
                        <template x-for="cat in filteredCategories" :key="cat.id">
                            <button @click="activeCategory = cat.id; activeSubcategory = null" 
                                :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                class="px-3 py-1.5 text-[12px] font-bold rounded-lg transition-all flex items-center gap-1 justify-center">
                                <span x-text="cat.name"></span>
                            </button>
                        </template>
                        <button @click="activeCategory = 'uncategorized'; activeSubcategory = null" 
                            :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                            class="px-3 py-1.5 text-[12px] font-bold rounded-lg transition-all">
                            Uncategorized
                        </button>
                    </div>
                </div>

                <!-- Services Grid grouped by Subcategory -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar z-10 flex flex-col gap-6">
                    
                    <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0">
                        <div class="flex flex-col gap-6">
                            
                            <!-- Loop through all subcategories -->
                            <template x-for="sub in activeCategoryData.children" :key="sub.id">
                                <div class="flex flex-col md:flex-row gap-3 sm:gap-4 bg-slate-50/50 rounded-[16px] p-3 sm:p-4 border border-slate-100">
                                    
                                    <!-- Subcategory Name (Left Side) -->
                                    <div class="flex flex-col items-start justify-center p-1 sm:p-2 shrink-0 w-full md:w-[100px]">
                                        <span class="text-[11px] sm:text-[12px] font-black text-black uppercase tracking-widest break-words whitespace-normal leading-tight w-full" x-text="sub.name"></span>
                                    </div>

                                    <!-- Services List (Right Side) -->
                                    <div class="flex flex-wrap gap-2 lg:gap-2.5 flex-1 items-start">
                                        <template x-for="service in services.filter(s => s.category_id === sub.id)" :key="service.id">
                                            <!-- SMALL Service Card -->
                                            <button @click="addToCart(service)"
                                                class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                                :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                                <div class="flex flex-col items-start mr-3">
                                                    <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                                    <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : 'Per Item')"></span>
                                                </div>
                                                <span class="text-[13px] font-black text-blue-700 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                            </button>
                                        </template>
                                        <div x-show="services.filter(s => s.category_id === sub.id).length === 0" class="w-full text-[11px] font-bold text-slate-400 py-2">
                                            No services found in this category.
                                        </div>
                                    </div>

                                </div>
                            </template>

                        </div>
                    </template>

                    <!-- Main Category Services / Uncategorized -->
                    <div class="flex flex-col gap-3">
                        <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0 && services.filter(s => s.category_id === activeCategory).length > 0">
                            <h3 class="text-[13px] font-black text-slate-700 tracking-wider uppercase border-b-2 border-slate-100 pb-1.5 mt-2">Other Services</h3>
                        </template>
                        <div class="flex flex-wrap gap-2 lg:gap-3">
                            <template x-for="service in (activeCategory === 'uncategorized' ? services.filter(s => s.category_id === null) : services.filter(s => s.category_id === activeCategory))" :key="service.id">
                                <button @click="addToCart(service)"
                                    class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                    :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                    <div class="flex flex-col items-start mr-3">
                                        <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                        <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : 'Per Item')"></span>
                                    </div>
                                    <span class="text-[12px] font-black text-blue-600 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                </button>
                            </template>
                            
                            <template x-if="!(activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0)">
                                <div x-show="(activeCategory === 'uncategorized' ? services.filter(s => s.category_id === null) : services.filter(s => s.category_id === activeCategory)).length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-center opacity-70">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">No services found</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
