    <!-- Top Metrics Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Revenue Today -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-200/50 group hover:shadow-xl hover:shadow-emerald-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Revenue</p>
                <h3 class="text-xl font-black text-white leading-tight">Rs.{{ number_format($superAdminStats['total_revenue_today']) }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Today</p>
            </div>
        </div>

        <!-- New Orders Today -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-200/50 group hover:shadow-xl hover:shadow-blue-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Orders</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['new_orders_today'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Today</p>
            </div>
        </div>

        <!-- Present Staff -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-orange-500 to-amber-500 shadow-lg shadow-orange-200/50 group hover:shadow-xl hover:shadow-orange-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Staff</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['today_present_staff'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Present Today</p>
            </div>
        </div>

        <!-- Opening Branches -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-lg shadow-purple-200/50 group hover:shadow-xl hover:shadow-purple-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Branches</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['opening_branches'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Open Now</p>
            </div>
        </div>
    </div>
