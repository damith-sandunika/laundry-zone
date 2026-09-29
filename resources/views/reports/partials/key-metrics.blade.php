    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Order Value -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <div class="flex items-center gap-3 mb-4 relative z-10">
                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Total Order Value</h3>
            </div>
            <p class="text-3xl font-black text-slate-900 tracking-tight relative z-10">Rs.{{ number_format($totalSales, 2) }}</p>
            <div class="mt-3 text-[11px] font-bold text-slate-500 relative z-10">
                Total value of all orders placed.
            </div>
        </div>

        <!-- Walk-in vs Delivery -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-violet-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <div class="flex items-center gap-3 mb-4 relative z-10">
                <div class="w-10 h-10 bg-violet-50 text-violet-500 rounded-xl flex items-center justify-center">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Channel Revenue</h3>
            </div>
            <div class="flex items-end gap-4 relative z-10">
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">🚶 Walk-in</div>
                    <div class="text-[11px] md:text-base font-black text-slate-900 tracking-tight">Rs.{{ number_format($walkinRevenue, 2) }}</div>
                </div>
                <div class="w-px h-10 bg-slate-200"></div>
                <div class="flex-1">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">🚚 Delivery</div>
                    <div class="text-[11px] md:text-base font-black text-violet-500 tracking-tight">Rs.{{ number_format($deliveryRevenue, 2) }}</div>
                </div>
            </div>
            <div class="mt-3 text-[11px] font-bold text-slate-500 relative z-10">
                Gross value broken down by channel.
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-rose-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <div class="flex items-center gap-3 mb-4 relative z-10">
                <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Pending / Due</h3>
            </div>
            <p class="text-3xl font-black text-slate-900 tracking-tight relative z-10 mb-3">Rs.{{ number_format($totalPending + $totalCredit, 2) }}</p>
            <div class="flex items-center gap-2 relative z-10">
                <div class="flex-1 bg-rose-50/50 rounded-xl p-2.5 border border-rose-100">
                    <div class="text-[9px] font-bold text-rose-500 uppercase tracking-widest mb-0.5">Regular Pending</div>
                    <div class="text-sm font-black text-rose-700">Rs.{{ number_format($totalPending, 2) }}</div>
                </div>
                <div class="flex-1 bg-orange-50/50 rounded-xl p-2.5 border border-orange-100">
                    <div class="text-[9px] font-bold text-orange-500 uppercase tracking-widest mb-0.5">Credit Orders</div>
                    <div class="text-sm font-black text-orange-700">Rs.{{ number_format($totalCredit, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Collections -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 ease-out"></div>
            <div class="flex items-center gap-3 mb-4 relative z-10">
                <div class="w-10 h-10 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Collected Payments</h3>
            </div>
            <p class="text-3xl font-black text-slate-900 tracking-tight relative z-10 mb-3">Rs.{{ number_format($totalCollected, 2) }}</p>
            
            <div class="grid grid-cols-3 gap-2 relative z-10 mb-3">
                <div class="bg-emerald-50/50 rounded-lg p-2 border border-emerald-100 text-center">
                    <div class="text-[8px] font-bold text-emerald-600 uppercase tracking-wider mb-0.5">💵 Cash</div>
                    <div class="text-xs font-black text-emerald-700">{{ number_format($collectedCash, 0) }}</div>
                </div>
                <div class="bg-blue-50/50 rounded-lg p-2 border border-blue-100 text-center">
                    <div class="text-[8px] font-bold text-blue-600 uppercase tracking-wider mb-0.5">💳 Card</div>
                    <div class="text-xs font-black text-blue-700">{{ number_format($collectedCard, 0) }}</div>
                </div>
                <div class="bg-indigo-50/50 rounded-lg p-2 border border-indigo-100 text-center">
                    <div class="text-[8px] font-bold text-indigo-600 uppercase tracking-wider mb-0.5">🏦 Bank</div>
                    <div class="text-xs font-black text-indigo-700">{{ number_format($collectedBank, 0) }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-1.5 text-[10px] font-bold relative z-10">
                <span class="text-emerald-700 bg-emerald-100/30 px-2 py-1 rounded inline-flex justify-between items-center w-full">
                    <span>🏪 Branch</span>
                    <span>Rs.{{ number_format($totalBranchProfit, 2) }}</span>
                </span>
                <span class="text-blue-700 bg-blue-100/30 px-2 py-1 rounded inline-flex justify-between items-center w-full">
                    <span>🏭 Workshop</span>
                    <span>Rs.{{ number_format($totalWorkshopProfit, 2) }}</span>
                </span>
                <span class="text-violet-700 bg-violet-100/30 px-2 py-1 rounded inline-flex justify-between items-center w-full">
                    <span>🚚 Delivery</span>
                    <span>Rs.{{ number_format($totalDeliveryFees, 2) }}</span>
                </span>
            </div>
        </div>
    </div>
