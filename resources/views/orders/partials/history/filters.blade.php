<div class="mb-4 flex flex-wrap gap-3 items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-slate-100">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        @if(auth()->user()->isSuperAdmin())
            <div>
                <select name="branch_id"
                    class="border-slate-200 rounded-md text-xs font-bold w-[140px] shadow-sm py-1.5 text-slate-600">
                    <option value="">All Branches</option>
                    @foreach($allBranches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="workshop_id"
                    class="border-slate-200 rounded-md text-xs font-bold w-[140px] shadow-sm py-1.5 text-slate-600">
                    <option value="">All Workshops</option>
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
                class="border-slate-200 rounded-md text-xs font-bold w-[140px] shadow-sm py-1.5 text-slate-600">
                <option value="">All Statuses</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Pickup</option>
                <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                <option value="dispatched_to_workshop" {{ request('status') == 'dispatched_to_workshop' ? 'selected' : '' }}>Dispatched</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed_at_workshop" {{ request('status') == 'completed_at_workshop' ? 'selected' : '' }}>Completed at Workshop</option>
                <option value="returned_from_workshop" {{ request('status') == 'returned_from_workshop' ? 'selected' : '' }}>Returned to Branch</option>
                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <input type="date" name="start_date" id="start_date" value="{{ $startDate ?? '' }}"
                class="border-slate-200 rounded-md text-xs font-bold w-[130px] shadow-sm py-1.5 text-slate-600">
        </div>
        <div>
            <input type="date" name="end_date" id="end_date" value="{{ $endDate ?? '' }}"
                class="border-slate-200 rounded-md text-xs font-bold w-[130px] shadow-sm py-1.5 text-slate-600">
        </div>
        <button type="submit"
            class="px-3 py-1.5 bg-slate-800 text-white rounded-md text-xs font-bold shadow-sm hover:bg-slate-700 transition-colors">
            Filter
        </button>

        <div class="flex items-center gap-1.5 ml-2 border-l border-slate-200 pl-3">
            @php 
                $isThisMonth = ($startDate == now()->startOfMonth()->format('Y-m-d') && $endDate == now()->format('Y-m-d'));
                $isToday = ($startDate == now()->format('Y-m-d') && $endDate == now()->format('Y-m-d'));
                $isYesterday = ($startDate == now()->subDay()->format('Y-m-d') && $endDate == now()->subDay()->format('Y-m-d'));
                $isAllTime = empty($startDate) && empty($endDate);
            @endphp
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isThisMonth && !$isToday ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                This Month
            </a>
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isToday ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                Today
            </a>
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => now()->subDay()->format('Y-m-d'), 'end_date' => now()->subDay()->format('Y-m-d')])) }}"
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isYesterday ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100' }}">
                Yesterday
            </a>
            <a href="{{ route('orders.history', array_merge(request()->only(['branch_id', 'workshop_id', 'status']), ['start_date' => '', 'end_date' => ''])) }}"
                class="px-3 py-1.5 rounded-md text-xs font-bold transition-colors {{ $isAllTime ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Time
            </a>
        </div>

        @if (request()->has('start_date') || request()->has('end_date') || request('branch_id') || request('workshop_id') || request('status'))
            <a href="{{ route('orders.history') }}"
                class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-md text-xs font-bold hover:bg-slate-200 transition-colors ml-1">
                Reset
            </a>
        @endif
    </form>
</div>
