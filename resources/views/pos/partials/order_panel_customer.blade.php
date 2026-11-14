    <!-- Customer Section -->
    <div class="p-5 border-b-2 border-slate-100 bg-white z-20">
        <div class="flex justify-between items-center mb-3">
            <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Customer Details</span>
            <button @click="$dispatch('open-customer-modal')"
                class="text-[11px] font-bold px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                New Customer
            </button>
        </div>

        <!-- Search customers -->
        <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none"
                stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <input type="text" x-model="customerSearch" @input="filterCustomers" @focus="showCustDropdown = true"
                @click.away="showCustDropdown = false"
                :placeholder="selectedCustomer ? 'Change customerâ€¦' : 'Search by name or phone'"
                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-2 border-slate-100 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 rounded-xl text-[13px] font-bold text-slate-800 transition-all outline-none placeholder:text-slate-400">

            <!-- Dropdown -->
            <div x-show="showCustDropdown && customerSearch.trim().length > 0" x-transition
                class="absolute z-50 w-full mt-2 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-xl max-h-[260px] overflow-y-auto custom-scrollbar">
                
                <!-- Matching customers list -->
                <template x-for="cust in filteredCustomers" :key="cust.id">
                    <div @click="selectCustomer(cust)"
                        class="px-4 py-3 cursor-pointer border-b border-slate-50 hover:bg-blue-50 transition-colors flex items-center justify-between group">
                        <div>
                            <p class="text-[13px] font-black text-slate-800 group-hover:text-blue-700 transition-colors"
                                x-text="cust.name"></p>
                            <p class="text-[11px] font-bold text-slate-500 mt-0.5" x-text="cust.phone"></p>
                        </div>
                        <svg class="w-4 h-4 text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity"
                            fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </template>

                <!-- When customer not found, offer register button with pre-filled search term -->
                <div x-show="filteredCustomers.length === 0" class="p-4 text-center">
                    <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 mx-auto flex items-center justify-center mb-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <p class="text-xs font-bold text-slate-600 mb-1">Customer not found</p>
                    <p class="text-[11px] text-slate-400 font-medium mb-3">No customer registered with "<span class="font-bold text-slate-700" x-text="customerSearch"></span>"</p>
                    <button type="button" @click="registerNewCustomerFromSearch()"
                        class="w-full py-2.5 px-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        <span>+ Register New Customer</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Selected Customer badge -->
        <div x-show="selectedCustomer" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl relative flex items-center gap-3">
            <div
                class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                <span x-text="selectedCustomer?.name?.charAt(0).toUpperCase()"></span>
            </div>
            <div class="flex-1 overflow-hidden">
                <div class="flex justify-between items-start">
                    <span class="text-[14px] font-black text-blue-900 truncate pr-6"
                        x-text="selectedCustomer?.name"></span>
                    <button @click="clearCustomer()"
                        class="absolute top-3 right-3 p-1.5 text-blue-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <span class="text-[11px] font-bold text-blue-600 block" x-text="selectedCustomer?.phone"></span>

                <template x-if="activePackage">
                    <button type="button" @click="openPackageModal()"
                        class="relative z-50 mt-2 text-[11px] font-black px-3 py-1.5 bg-gradient-to-r from-amber-400 to-orange-500 text-white hover:from-amber-500 hover:to-orange-600 rounded-lg shadow-sm hover:shadow-md transition-all flex items-center gap-1.5 w-max">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Use Package: <span x-text="activePackage?.package?.name || 'Active'"></span>
                    </button>
                </template>
            </div>
        </div>

    </div>
