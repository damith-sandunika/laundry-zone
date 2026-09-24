{{-- Chart --}}
            <div class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm p-6 flex flex-col animate-slide-up" style="animation-delay: 250ms;">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Order Performance</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Pending vs Delivered</p>
                    </div>
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button data-chart-tab="today" class="chart-tab-btn active-tab px-4 py-1.5 text-xs font-bold rounded-lg transition-all bg-white text-slate-800 shadow-sm">Today</button>
                        <button data-chart-tab="weekly" class="chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Weekly</button>
                        <button data-chart-tab="monthly" class="chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Monthly</button>
                    </div>
                </div>
                <div class="relative flex-1 min-h-[220px]">
                    <canvas id="orderChart"></canvas>
                </div>
                <div class="flex justify-center gap-6 mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Pending</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Delivered</span>
                    </div>
                </div>
            </div>
