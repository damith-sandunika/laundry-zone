        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-4 border-b border-slate-100 bg-slate-50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-slate-200/60 p-1.5 rounded-2xl">
                <button @click="activeReport = 'expenses'" :class="activeReport === 'expenses' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1.5 md:px-5 md:py-2.5 text-[11px] md:text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-all whitespace-nowrap">
                    Petty Cash Log
                </button>
                <button @click="activeReport = 'petty_summary'" :class="activeReport === 'petty_summary' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1.5 md:px-5 md:py-2.5 text-[11px] md:text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-all whitespace-nowrap">
                    Petty Cash Summary
                </button>
                <button @click="activeReport = 'inventory'" :class="activeReport === 'inventory' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1.5 md:px-5 md:py-2.5 text-[11px] md:text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-all whitespace-nowrap">
                    Inventory Usage
                </button>
                <button @click="activeReport = 'deposits'" :class="activeReport === 'deposits' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1.5 md:px-5 md:py-2.5 text-[11px] md:text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-all whitespace-nowrap">
                    Bank Deposits
                </button>

            </div>
        </div>
