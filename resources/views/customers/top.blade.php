<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="ph-title text-emerald-600 flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    Top Customers
                </h2>
                <p class="text-[13px] text-slate-500 font-medium m-0">Identify and filter your best customers</p>
            </div>
            
            <a href="{{ route('customers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-bold text-sm hover:bg-slate-200 transition-colors">
                Back to All
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-[20px] border border-slate-200/60 shadow-sm overflow-hidden mb-6">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-black text-slate-800 mb-4 uppercase tracking-wider">Dynamic Filters</h3>
            <form method="GET" action="{{ route('customers.top') }}" class="m-0 flex flex-wrap gap-4 items-end">
                @if(auth()->user()->isSuperAdmin())
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Branch</label>
                    <select name="branch_id" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none">
                        <option value="">All Branches</option>
                        @foreach($allBranches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Min Spent (Rs)</label>
                    <input type="number" name="min_spent" value="{{ $minSpent }}" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none" placeholder="e.g. 5000">
                </div>

                <div class="flex-1 min-w-[120px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Min Orders</label>
                    <input type="number" name="min_orders" value="{{ $minOrders }}" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none" placeholder="e.g. 5">
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none">
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none">
                </div>

                <div class="flex-1 min-w-[140px]">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Sort By</label>
                    <select name="sort_by" class="w-full text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl p-2.5 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm outline-none">
                        <option value="total_spent" {{ $sortBy === 'total_spent' ? 'selected' : '' }}>Highest Revenue</option>
                        <option value="order_count" {{ $sortBy === 'order_count' ? 'selected' : '' }}>Most Orders</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-500 text-white rounded-xl font-black text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/30 transition-all">Filter</button>
                    <a href="{{ route('customers.top') }}" class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 ml-2">Clear</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Customer Name</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Phone / DOB</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Orders</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Total Spent</th>
                        <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($topCustomers as $index => $customer)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $index < 3 && $topCustomers->currentPage() == 1 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600' }} font-black text-sm">
                                    {{ ($topCustomers->currentPage() - 1) * $topCustomers->perPage() + $index + 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-black text-slate-800 text-[14px]">{{ $customer->name }}</div>
                                @if($customer->branch_id)
                                    <div class="text-[11px] font-bold text-blue-600">{{ $customer->branch->name ?? 'Unknown Branch' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-600 text-[13px]">{{ $customer->phone }}</div>
                                @if($customer->date_of_birth)
                                    <div class="text-[11px] font-bold text-purple-500">DOB: {{ \Carbon\Carbon::parse($customer->date_of_birth)->format('d M Y') }}</div>
                                @else
                                    <div class="text-[11px] font-semibold text-slate-400">No DOB</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-black text-[13px] rounded-lg border border-blue-100">
                                    {{ $customer->order_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-black text-emerald-600 text-[15px]">Rs. {{ number_format($customer->total_spent, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('customers.show', $customer->id) }}" class="inline-block px-4 py-2 bg-white text-blue-600 font-bold text-[11px] uppercase tracking-wide border-2 border-blue-100 rounded-xl hover:bg-blue-50 transition-colors">
                                    Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-slate-400 font-bold text-sm">No customers match your filters.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $topCustomers->links() }}
        </div>
    </div>
</x-app-layout>
