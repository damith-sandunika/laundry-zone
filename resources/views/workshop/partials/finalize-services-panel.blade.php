            <!-- ===================== Left: Services ===================== -->
            <div
                class="flex-1 lg:flex-[1.2] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">

                <!-- Background Decoration -->
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none">
                </div>

                <!-- Search bar & Category Filters Container -->
                <div x-data="{ searchExpanded: false }"
                    class="border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10 relative flex items-start gap-2 p-3">

                    <!-- Search Bar Icon (Collapsible) -->
                    <div class="relative shrink-0 mt-0.5"
                        :class="searchExpanded ? 'w-full max-w-sm absolute top-2 left-2 z-50 bg-white rounded-xl shadow-lg' : ''">
                        <button @click="searchExpanded = true" x-show="!searchExpanded"
                            class="w-9 h-9 rounded-full bg-slate-100 hover:bg-blue-50 text-slate-500 hover:text-blue-600 flex items-center justify-center transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                        <div x-show="searchExpanded" x-cloak
                            class="w-full relative flex items-center transition-all animate-fade-in">
                            <input type="text" x-model="searchQuery" x-ref="searchInput"
                                placeholder="Search services..."
                                class="w-full pl-10 pr-10 py-2 bg-slate-50 hover:bg-white border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[12px] font-bold text-slate-800 transition-all outline-none placeholder:text-slate-400">
                            <svg class="absolute left-3.5 w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <button @click="searchExpanded = false; searchQuery = ''"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Category Filters (2 rows with horizontal scroll) -->
                    <div class="flex-1 min-w-0 relative group" x-data="{
                        hasOverflow: false,
                        checkOverflow() {
                            this.$nextTick(() => {
                                if (this.$refs.catScroll) {
                                    this.hasOverflow = this.$refs.catScroll.scrollWidth > this.$refs.catScroll.clientWidth;
                                }
                            });
                        },
                        init() {
                            this.checkOverflow();
                            // If you have search filtering that alters width, it'll trigger resize or you can call checkOverflow
                        },
                        scrollCatLeft() { $refs.catScroll.scrollBy({ left: -200, behavior: 'smooth' }); },
                        scrollCatRight() { $refs.catScroll.scrollBy({ left: 200, behavior: 'smooth' }); }
                    }" @resize.window="checkOverflow()">
                        <!-- Left Arrow -->
                        <button x-show="hasOverflow" x-transition @click="scrollCatLeft()"
                            class="absolute -left-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Right Arrow -->
                        <button x-show="hasOverflow" x-transition @click="scrollCatRight()"
                            class="absolute -right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full bg-white shadow-md border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center z-20 opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <div class="flex flex-col min-w-0 gap-1.5 pb-1">
                            <!-- Bottom Layer: Parent Categories View (2 Rows) -->
                            <div class="relative w-full">
                                <div x-ref="catScroll" class="overflow-x-auto scrollbar-none custom-scrollbar-hide">
                                    <div class="grid grid-rows-2 grid-flow-col gap-1.5 w-max">
                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                            <button
                                                @click="activeCategory = cat.id; activeSubcategory = (cat.children && cat.children.length > 0) ? cat.children[0].id : null"
                                                :class="activeCategory === cat.id ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                                class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[70px] flex items-center gap-1 justify-center">
                                                <span x-text="cat.name"></span>
                                                <template x-if="cat.children && cat.children.length > 0">
                                                    <svg class="w-3 h-3 opacity-60 shrink-0" fill="none"
                                                        stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </template>
                                            </button>
                                        </template>
                                        <button @click="activeCategory = 'uncategorized'; activeSubcategory = null"
                                            :class="activeCategory === 'uncategorized' ? 'bg-blue-600 text-white shadow-sm ring-1 ring-blue-700' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300'"
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-all whitespace-nowrap min-w-[70px]">
                                            Uncategorized
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Distinct 3rd Row: Subcategories -->
                            <template
                                x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0">
                                <div
                                    class="overflow-x-auto custom-scrollbar mt-1 pt-1.5 border-t border-slate-200/60 pb-1">
                                    <div class="flex items-center gap-2 w-max pl-1 pr-2">

                                        <template x-for="sub in activeCategoryData.children" :key="sub.id">
                                            <button @click="activeSubcategory = sub.id"
                                                :class="activeSubcategory === sub.id ? 'bg-indigo-600 text-white shadow-sm ring-1 ring-indigo-700' : 'bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100'"
                                                class="px-3 py-0.5 text-[10px] font-bold rounded-md transition-all whitespace-nowrap min-w-[60px]"
                                                x-text="sub.name">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Services Grid -->
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar z-10">
                    <div class="grid grid-cols-4 sm:grid-cols-6 xl:grid-cols-6 gap-2 lg:gap-3 auto-rows-max">


                        <!-- Service Cards -->
                        <template x-for="service in filteredServices" :key="service.id">
                            <button @click="addToCart(service)"
                                class="col-span-2 sm:col-span-1 xl:col-span-1 flex flex-col p-3 rounded-[14px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-[0_8px_16px_rgba(0,0,0,0.06)] hover:-translate-y-0.5 transition-all duration-300 text-left group overflow-hidden relative justify-center items-center text-center"
                                :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">

                                <span
                                    class="text-[12px] font-black text-slate-800 leading-tight tracking-tight line-clamp-2 mb-1"
                                    x-text="service.name"></span>
                                <div class="flex items-end justify-center gap-1">
                                    <span class="text-[13px] font-black text-blue-600"
                                        x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                    <span
                                        class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest pb-0.5"
                                        x-text="service.price_type === 'per_kg' ? '/KG' : (service.price_type === 'per_sqft' ? '/SQFT' : (service.price_type === 'per_pair' ? '/PAIR' : '/ITM'))"></span>
                                </div>
                            </button>
                        </template>

                        <!-- Empty State -->
                        <div x-show="filteredServices.length === 0"
                            class="col-span-full py-16 flex flex-col items-center justify-center text-center opacity-70">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-500">No services found</p>
                        </div>
                    </div>
                </div>
            </div>
