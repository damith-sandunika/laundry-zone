        <!-- OVERALL SUMMARY -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Overall Summary
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Active</span>
                    <span class="text-2xl font-black text-slate-800">{{ array_sum($counts) }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed Today</span>
                    <span class="text-2xl font-black text-slate-800">{{ $completedToday }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">In Processing</span>
                    <span class="text-2xl font-black text-orange-600">{{ $counts['incoming'] + $counts['to_wash'] + $counts['washing'] }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed & Dispatched</span>
                    <span class="text-2xl font-black text-green-600">{{ $counts['completed'] + $counts['returned'] }}</span>
                </div>
            </div>
        </div>