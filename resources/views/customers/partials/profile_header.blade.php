<!-- Profile Card (Large) -->
<div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6 mb-4 flex flex-col md:flex-row items-start md:items-center gap-5 relative overflow-hidden">
    <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
    <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-4xl md:text-5xl font-black shadow-lg shadow-blue-500/30 shrink-0 z-10 relative">
        {{ substr($customer->name, 0, 1) }}
    </div>
    
    <div class="flex-1 z-10 relative">
        <div class="flex items-center gap-3 mb-2">
            <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                {{ $customer->title ? $customer->title . ' ' : '' }}{{ $customer->name }}
            </h2>
            @if($customer->customer_mark)
                <span class="px-3 py-1 bg-amber-100 text-amber-700 font-bold text-xs md:text-sm rounded-lg border border-amber-200 uppercase tracking-widest shadow-sm">
                    {{ $customer->customer_mark }}
                </span>
            @endif
            @if($customer->isCorporate())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 font-extrabold text-xs md:text-sm rounded-lg border border-indigo-200 uppercase tracking-wider shadow-sm">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    Corporate Client
                </span>
            @elseif($customer->isOnline())
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs md:text-sm rounded-lg border border-emerald-200 uppercase tracking-wider shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Online Client
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg border border-slate-200 uppercase tracking-wider">
                    Walk-in Client
                </span>
            @endif
        </div>
        
        <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 text-sm md:text-base font-semibold text-slate-600">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                    <i class="fa-solid fa-phone text-sm"></i>
                </div> 
                <span class="text-slate-700 tracking-wide">{{ $customer->phone }}</span>
            </div>
            @if($customer->address)
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                    <i class="fa-solid fa-location-dot text-sm"></i>
                </div> 
                <span class="text-slate-600">{{ $customer->address }}</span>
            </div>
            @endif
        </div>
        
        @if($customer->date_of_birth || $customer->nic)
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-4 pt-4 border-t border-slate-100">
            @if($customer->date_of_birth)
            <span class="flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold border border-rose-100"><i class="fa-solid fa-cake-candles"></i> {{ \Carbon\Carbon::parse($customer->date_of_birth)->format('M d, Y') }}</span>
            @endif
            @if($customer->nic)
            <span class="flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold border border-purple-100"><i class="fa-solid fa-id-card"></i> NIC: {{ $customer->nic }}</span>
            @endif
        </div>
        @endif
    </div>
</div>

<!-- Compact Stats & Pending Row -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-6 text-center pl-2">
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Orders</p>
            <p class="text-xl md:text-2xl font-black text-slate-700 leading-none">{{ $totalOrders }}</p>
        </div>
        <div class="w-px h-10 bg-slate-200"></div>
        <div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Spend</p>
            <p class="text-xl md:text-2xl font-black text-emerald-600 leading-none">Rs.{{ number_format($totalSpend, 0) }}</p>
        </div>
    </div>

    @if($pendingCount > 0)
    <div class="flex flex-wrap items-center justify-between lg:justify-end gap-4 bg-red-50 border border-red-100 rounded-xl px-5 py-3 ml-auto w-full lg:w-auto">
        <div class="text-left lg:text-right flex-1 lg:flex-none">
            <p class="text-[10px] md:text-xs font-bold text-red-500 uppercase tracking-wider mb-0.5">Pending Due ({{ $pendingCount }})</p>
            <p class="text-lg md:text-xl font-black text-red-700 leading-none">Rs.{{ number_format($totalPending, 2) }}</p>
        </div>
        <button @click="openBulkCollectModal({{ $totalPending }})" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs md:text-sm rounded-lg transition-all shadow-md shadow-red-500/20 flex items-center gap-2 whitespace-nowrap">
            <i class="fa-solid fa-coins"></i> Settle
        </button>
    </div>
    @endif
</div>

