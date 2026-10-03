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
                                        <template x-for="service in allServices.filter(s => s.category_id === sub.id)" :key="service.id">
                                            <!-- SMALL Service Card -->
                                            <button @click="addToCart(service)"
                                                class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                                :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                                <div class="flex flex-col items-start mr-3">
                                                    <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                                    <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : (service.price_type === 'per_pair' ? 'Per Pair' : 'Per Item'))"></span>
                                                </div>
                                                <span class="text-[13px] font-black text-blue-700 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                            </button>
                                        </template>
                                        <div x-show="allServices.filter(s => s.category_id === sub.id).length === 0" class="w-full text-[11px] font-bold text-slate-400 py-2">
                                            No services found in this category.
                                        </div>
                                    </div>

                                </div>
                            </template>

                        </div>
                    </template>

                    <!-- Main Category Services / Uncategorized -->
                    <div class="flex flex-col gap-3">
                        <template x-if="activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0 && allServices.filter(s => s.category_id === activeCategory).length > 0">
                            <h3 class="text-[13px] font-black text-slate-700 tracking-wider uppercase border-b-2 border-slate-100 pb-1.5 mt-2">Other Services</h3>
                        </template>
                        <div class="flex flex-wrap gap-2 lg:gap-3">
                            <template x-for="service in (activeCategory === 'uncategorized' ? allServices.filter(s => s.category_id === null) : allServices.filter(s => s.category_id === activeCategory))" :key="service.id">
                                <button @click="addToCart(service)"
                                    class="flex flex-row items-center justify-between px-3 py-2 sm:px-3 sm:py-2.5 rounded-[12px] bg-white border-2 border-slate-100 shadow-[0_2px_8px_rgba(0,0,0,0.02)] hover:shadow-md transition-all hover:-translate-y-0.5 group shrink-0 w-[calc(50%-0.3rem)] sm:w-auto sm:min-w-[140px]"
                                    :class="{'hover:border-blue-300': service.color === 'blue', 'hover:border-green-300': service.color === 'green', 'hover:border-purple-300': service.color === 'purple', 'hover:border-orange-300': service.color === 'orange', 'hover:border-teal-300': service.color === 'teal', 'hover:border-red-300': service.color === 'red'}">
                                    <div class="flex flex-col items-start mr-3">
                                        <span class="text-[11px] font-black text-slate-700 leading-tight text-left" x-text="service.name"></span>
                                        <span class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest mt-0.5" x-text="service.price_type === 'per_kg' ? 'Per KG' : (service.price_type === 'per_sqft' ? 'Per SQFT' : (service.price_type === 'per_pair' ? 'Per Pair' : 'Per Item'))"></span>
                                    </div>
                                    <span class="text-[12px] font-black text-blue-600 shrink-0" x-text="'Rs.' + parseFloat(service.base_price).toFixed(0)"></span>
                                </button>
                            </template>
                            
                            <template x-if="!(activeCategoryData && activeCategoryData.children && activeCategoryData.children.length > 0)">
                                <div x-show="(activeCategory === 'uncategorized' ? allServices.filter(s => s.category_id === null) : allServices.filter(s => s.category_id === activeCategory)).length === 0" class="col-span-full py-12 flex flex-col items-center justify-center text-center opacity-70">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-500">No services found</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- ========== FAST ENTRY BAR ========== -->
                <div class="p-3 bg-white border-t-2 border-slate-100 z-20 flex gap-2 items-center relative" style="border-radius:0 0 24px 24px;">

                    <!-- Search Box -->
                    <div class="relative flex-[2]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            type="text"
                            id="fast-search-input"
                            x-model="fastSearchQuery"
                            @input="onFastSearchInput"
                            @keydown.enter.prevent="onFastSearchEnter"
                            @keydown="onFastSearchKeyDown"
                            @keydown.escape="fastSearchSuggestions = []"
                            @focus="if(fastSearchQuery) onFastSearchInput()"
                            @click.away="fastSearchSuggestions = []"
                            placeholder="Quick Search: name, code, category..."
                            autocomplete="off"
                            class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border-2 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[13px] font-black text-slate-800 transition-all outline-none placeholder:text-slate-400 placeholder:font-medium">

                        <!-- Suggestions Dropdown (pops UP) -->
                        <div
                            x-show="fastSearchSuggestions.length > 0"
                            x-transition
                            class="absolute bottom-full mb-2 left-0 w-full bg-white border border-slate-200 rounded-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.12)] max-h-[280px] overflow-y-auto custom-scrollbar z-50">
                            <template x-for="(sug, idx) in fastSearchSuggestions" :key="idx">
                                <div
                                    @click="fastSearchHighlightIndex = idx; onFastSearchEnter();"
                                    @mouseenter="fastSearchHighlightIndex = idx"
                                    :class="fastSearchHighlightIndex === idx ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'border-l-4 border-l-transparent hover:bg-slate-50'"
                                    class="px-4 py-2.5 cursor-pointer transition-colors flex items-center justify-between border-b border-slate-50 last:border-0">
                                    <div class="flex items-center gap-2">
                                        <div x-show="sug.type === 'service'" class="w-6 h-6 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                        </div>
                                        <div x-show="sug.type !== 'service'" class="w-6 h-6 rounded-md bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[13px] font-black text-slate-800" :class="fastSearchHighlightIndex === idx ? 'text-blue-700' : ''" x-text="sug.label"></span>
                                            <span x-show="sug.type === 'service' && sug.item.item_code" class="text-[10px] font-bold text-slate-400" x-text="sug.item.item_code"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span x-show="sug.type === 'service'" class="text-[12px] font-black text-blue-600" x-text="'Rs.' + parseFloat(sug.item.base_price).toFixed(0)"></span>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase tracking-wider" :class="sug.type === 'service' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'" x-text="sug.type"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Qty Box -->
                    <div class="w-20">
                        <input
                            type="number"
                            id="fast-qty-input"
                            x-model="fastSearchQty"
                            @keydown.enter.prevent="onFastQtyEnter"
                            @keydown.escape="focusFastSearch"
                            placeholder="Qty"
                            min="0.01"
                            step="any"
                            class="w-full px-3 py-2.5 bg-slate-50 border-2 border-slate-200 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 rounded-xl text-[14px] font-black text-slate-800 transition-all outline-none text-center hide-arrows">
                    </div>

                    <!-- Add Button -->
                    <button @click="onFastQtyEnter" class="w-11 h-11 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl shadow-md flex items-center justify-center transition-all shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </button>
                </div>
            </div>

