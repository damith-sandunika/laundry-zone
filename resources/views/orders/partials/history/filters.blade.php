<div class="mb-3 bg-white p-2.5 rounded-xl shadow-sm border border-slate-100 overflow-x-auto">
    <form method="GET" class="flex items-center gap-2 min-w-max">
        @if(auth()->user()->isSuperAdmin())
            <div>
                <select name="branch_id"
                    class="border-slate-200 rounded-lg text-xs font-bold w-[170px] shadow-sm py-1.5 px-2 text-slate-700 bg-white hover:border-indigo-300 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">🏢 All Collection Points</option>
                    @foreach($allBranches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ number_format($branch->branch_profit_percentage ?? 0, 0) }}% Comm)
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="workshop_id"
                    class="border-slate-200 rounded-lg text-xs font-bold w-[145px] shadow-sm py-1.5 px-2 text-slate-700 bg-white hover:border-indigo-300 focus:ring-1 focus:ring-indigo-500 transition-colors">
                    <option value="">🏭 All Workshops</option>
                    @foreach($allWorkshops as $workshop)
                        <option value="{{ $workshop->id }}" {{ request('workshop_id') == $workshop->id ? 'selected' : '' }}>
                            {{ $workshop->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div>
            <select name="status"
                class="border-slate-200 rounded-lg text-xs font-bold w-[125px] shadow-sm py-1.5 px-2 text-slate-600 hover:border-indigo-300 focus:ring-1 focus:ring-indigo-500 transition-colors">
                <option value="">All Statuses</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Pickup</option>
                <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                <option value="dispatched_to_workshop" {{ request('status') == 'dispatched_to_workshop' ? 'selected' : '' }}>Dispatched</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed_at_workshop" {{ request('status') == 'completed_at_workshop' ? 'selected' : '' }}>Completed at WS</option>
                <option value="returned_from_workshop" {{ request('status') == 'returned_from_workshop' ? 'selected' : '' }}>Returned</option>
                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="flex items-center gap-1">
            <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                class="border-slate-200 rounded-lg text-xs font-bold w-[125px] shadow-sm py-1.5 px-2 text-slate-600">
            <span class="text-xs text-slate-400 font-bold">to</span>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                class="border-slate-200 rounded-lg text-xs font-bold w-[125px] shadow-sm py-1.5 px-2 text-slate-600">
        </div>
        <button type="submit"
            class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold shadow-sm hover:bg-slate-800 transition-colors flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>

        <div class="flex items-center gap-1 ml-1 border-l border-slate-200 pl-2">
            @php 
                $isThisMonth = ($startDate == now()->startOfMonth()->format('Y-m-d') && $endDate == now()->format('Y-m-d'));
                $isToday = ($startDate == now()->format('Y-m-d') && $endDate == now()->format('Y-m-d'));
                $isYesterday = ($startDate == now()->subDay()->format('Y-m-d') && $endDate == now()->subDay()->format('Y-m-d'));
            @endphp
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $isThisMonth && !$isToday ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                Month
            </a>
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                Today
            </a>
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->subDay()->format('Y-m-d'), 'end_date' => now()->subDay()->format('Y-m-d')])) }}"
                class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $isYesterday ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                Yesterday
            </a>
        </div>

        @if (request()->has('start_date') || request()->has('end_date') || request('branch_id') || request('workshop_id') || request('status'))
            <a href="{{ route('orders.history') }}"
                class="px-2.5 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold hover:bg-rose-100 transition-colors ml-1"
                title="Reset All Filters">
                ✕ Reset
            </a>
        @endif
    </form>
</div>
