    <!-- Filters Section -->
    <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
        <form action="{{ route($route_name ?? 'reports.index') }}" method="GET" class="flex flex-wrap gap-2 md:gap-4 items-end">
            @if($isSuperAdmin)
                <div class="w-full md:flex-1 md:min-w-[200px]">
                    <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Branch</label>
                    <select name="branch_id" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">🌍 All Branches (Company Wide)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                🏪 {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex-1 md:min-w-[150px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Start</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>
            <div class="flex-1 md:min-w-[150px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">End</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>
            <div class="flex-none flex gap-2">
                <button type="button" onclick="applyQuickDate('{{ date('Y-m-d') }}', '{{ date('Y-m-d') }}')" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center">Today</button>
                <button type="button" onclick="applyQuickDate('{{ date('Y-m-d', strtotime('-1 day')) }}', '{{ date('Y-m-d', strtotime('-1 day')) }}')" class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-blue-100 text-slate-600 hover:text-blue-700 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center">Yesterday</button>
            </div>
            
            <div class="flex-none">
                <button type="submit" class="px-3 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl flex items-center gap-1 md:gap-2 shadow-lg shadow-blue-600/25 transition-all h-[34px] md:h-[46px]">
                    <svg width="14" height="14" class="md:w-[18px] md:h-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span class="hidden md:inline">Apply Filter</span>
                    <span class="md:hidden">Apply</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        function applyQuickDate(start, end) {
            document.querySelector('input[name="start_date"]').value = start;
            document.querySelector('input[name="end_date"]').value = end;
            document.querySelector('form[action="{{ route($route_name ?? 'reports.index') }}"]').submit();
        }
    </script>
