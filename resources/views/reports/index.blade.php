<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Business Analytics & Reports</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Comprehensive insights, sales trends, and performance metrics</p>
            </div>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Filters Section -->
    <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap gap-2 md:gap-4 items-end">
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
            <div class="flex-none">
                <button type="submit" class="px-3 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl flex items-center gap-1 md:gap-2 shadow-lg shadow-blue-600/25 transition-all h-[34px] md:h-[46px]">
                    <svg width="14" height="14" class="md:w-[18px] md:h-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span class="hidden md:inline">Apply Filter</span>
                    <span class="md:hidden">Apply</span>
                </button>
            </div>
        </form>
    </div>

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
            <p class="text-3xl font-black text-slate-900 tracking-tight relative z-10">Rs.{{ number_format($totalPending + $totalCredit, 2) }}</p>
            <div class="mt-3 text-[9px] md:text-xs font-bold text-rose-500 flex items-center gap-1 relative z-10">
                Includes Rs.{{ number_format($totalCredit, 2) }} from Credit Customers
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
            <p class="text-3xl font-black text-slate-900 tracking-tight relative z-10">Rs.{{ number_format($totalCollected, 2) }}</p>
            <div class="mt-3 flex flex-col gap-1.5 text-[11px] font-bold relative z-10">
                <span class="text-emerald-700 bg-emerald-100/50 px-2.5 py-1 rounded-md inline-flex justify-between items-center w-full">
                    <span>🏪 Branch Revenue</span>
                    <span>Rs.{{ number_format($totalBranchProfit, 2) }}</span>
                </span>
                <span class="text-blue-700 bg-blue-100/50 px-2.5 py-1 rounded-md inline-flex justify-between items-center w-full">
                    <span>🏭 Workshop Revenue</span>
                    <span>Rs.{{ number_format($totalWorkshopProfit, 2) }}</span>
                </span>
                <span class="text-violet-700 bg-violet-100/50 px-2.5 py-1 rounded-md inline-flex justify-between items-center w-full">
                    <span>🚚 Delivery Fees</span>
                    <span>Rs.{{ number_format($totalDeliveryFees, 2) }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <h3 class="text-[10px] md:text-sm font-black text-slate-900 mb-4">Daily Revenue Trend</h3>
            <div id="revenueChart" class="h-[300px] w-full"></div>
        </div>
        <!-- Payment Breakdown Chart -->
        <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col h-[350px]">
            <h3 class="text-[10px] md:text-sm font-black text-slate-900 mb-4">Payment Breakdown</h3>
            <div class="flex-1 flex items-center justify-center">
                <div id="paymentChart" class="w-full"></div>
            </div>
        </div>
    </div>

    <!-- Reports Content Wrapper -->
    <div x-data="reportData()" class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-4 border-b border-slate-100 bg-slate-50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-slate-200/60 p-1.5 rounded-2xl">
                <button @click="activeReport = 'credit_orders'" :class="activeReport === 'credit_orders' ? 'bg-white text-orange-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'" class="px-3 py-1.5 md:px-5 md:py-2.5 text-[11px] md:text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-all whitespace-nowrap">
                    Credit Orders
                </button>
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

        <div>
            <!-- CREDIT ORDERS LOG -->
            <div x-show="activeReport === 'credit_orders'" style="display:none;" x-transition>
                <div class="p-4 border-b border-slate-100 bg-white flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Pending Credit Orders</h3>
                    <span class="text-xs font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-full border border-orange-200">
                        Total Pending: Rs.{{ number_format($totalCredit, 2) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order Details</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Bill Value</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Balance Due</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($creditOrders as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="/orders/view/{{ $order->order_number }}" target="_blank" class="font-bold text-blue-600 hover:underline">{{ $order->order_number }}</a>
                                            <span class="text-[9px] font-bold text-slate-400">#{{ $order->id }}</span>
                                        </div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</div>
                                        @if($order->branch)
                                            <div class="text-[9px] md:text-xs font-bold text-slate-400 mt-0.5">🏪 {{ $order->branch->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="font-bold text-slate-900">{{ $order->customer->name ?? 'Walk-in' }}</div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1">{{ $order->customer->phone ?? '' }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right">
                                        <div class="font-bold text-slate-600">Rs.{{ number_format($order->total, 2) }}</div>
                                        @if($order->paid_amount > 0)
                                            <div class="text-[9px] md:text-xs font-semibold text-emerald-600 mt-1">Paid: Rs.{{ number_format($order->paid_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right">
                                        <div class="font-black text-rose-500 text-sm md:text-base">Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-center">
                                        <button @click="openCollectModal({{ $order->id }}, '{{ $order->order_number }}', {{ $order->total - $order->paid_amount }})" class="px-3 py-1.5 md:px-4 md:py-2 bg-orange-500 hover:bg-orange-600 text-white text-[10px] md:text-xs font-bold rounded-lg shadow-sm transition-colors">
                                            Collect Pay
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No pending credit orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $creditOrders->appends(request()->query())->links() }}
                </div>
            </div>

            <!-- PETTY CASH LOG -->
            <div x-show="activeReport === 'expenses'" style="display:none;" x-transition>
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-slate-800">Petty Cash Log</h3>
                    <select x-model="expenseCategoryFilter" class="px-3 py-1.5 border-2 border-slate-200 rounded-lg text-[10px] md:text-sm font-bold bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0">
                        <option value="">All Categories</option>
                        @php $expenseCategories = $expensesList->pluck('category')->unique(); @endphp
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Category</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Details</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($expensesList as $exp)
                                <tr x-show="expenseCategoryFilter === '' || expenseCategoryFilter === '{{ $exp->category }}'" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-semibold text-slate-500">{{ \Carbon\Carbon::parse($exp->created_at)->format('M d, Y h:i A') }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200 rounded-lg">
                                            {{ str_replace('_', ' ', $exp->category) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $exp->description }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-rose-500">Rs.{{ number_format($exp->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No petty cash expenses logged in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Expenses Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $expensesList->appends(request()->query())->links() }}
                </div>
            </div>

            <!-- INVENTORY USAGE LOG -->
            <div x-show="activeReport === 'inventory'" style="display:none;" x-transition>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Supply Item</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Type</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Qty Changes</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Reason / By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($inventoryLogs as $log)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-semibold text-slate-500">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $log->inventoryItem->name ?? 'Deleted Item' }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-lg border {{ $log->type === 'stock_in' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                            {{ strtoupper(str_replace('_', ' ', $log->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-center font-black text-[10px] md:text-sm {{ $log->type === 'stock_in' ? 'text-emerald-500' : 'text-rose-500' }}">
                                        {{ $log->type === 'stock_in' ? '+' : '-' }}{{ $log->quantity }}
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-medium text-slate-500">
                                        {{ $log->notes ?? '-' }} <br><span class="font-bold text-slate-900">{{ $log->createdBy->name ?? 'System' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No inventory transactions tracked in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Inventory Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $inventoryLogs->appends(request()->query())->links() }}
                </div>
            </div>



            <!-- PETTY CASH SUMMARY (Super Admin) -->
            <div x-show="activeReport === 'petty_summary'" style="display:none;" x-transition>
                <div class="p-4 border-b border-slate-100 bg-white flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Petty Cash — Branch Summary</h3>
                    <span class="text-xs font-bold text-slate-400">Current live balances per branch</span>
                </div>

                @if($isSuperAdmin && $branchPettyCashSummary->isNotEmpty())
                {{-- Branch Summary Cards --}}
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($branchPettyCashSummary as $bs)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest mb-3">{{ $bs->name }}</p>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[12px] font-semibold text-slate-500">Current Balance</span>
                            <span class="text-[15px] font-black {{ $bs->petty_cash_balance >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                Rs.{{ number_format($bs->petty_cash_balance, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center mb-2 pt-2 border-t border-slate-100">
                            <span class="text-[12px] font-semibold text-slate-500">Expenses (Period)</span>
                            <span class="text-[13px] font-bold text-rose-500">- Rs.{{ number_format($bs->total_expenses, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[12px] font-semibold text-slate-500">Top-ups (Period)</span>
                            <span class="text-[13px] font-bold text-emerald-500">+ Rs.{{ number_format($bs->total_topups, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-10 text-center text-slate-400 text-sm font-semibold">No branch data available.</div>
                @endif

                {{-- Topups Table --}}
                <div class="border-t border-slate-100">
                    <div class="p-4 bg-slate-50 flex justify-between items-center">
                        <h4 class="font-bold text-slate-700 text-sm">Top-ups Log (Selected Period)</h4>
                        <span class="text-xs font-bold text-emerald-600">Total: Rs.{{ number_format($totalTopupsAmount ?? 0, 2) }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Branch</th>
                                    <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date & Time</th>
                                    <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Reference</th>
                                    <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Added By</th>
                                    <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pettyCashTopups ?? [] as $topup)
                                    @php
                                        $topupBranch = $branches->firstWhere('id', $topup->branch_id);
                                        $topupUser = \App\Models\User::find($topup->user_id);
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $topupBranch->name ?? 'N/A' }}</td>
                                        <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-semibold text-slate-500">{{ \Carbon\Carbon::parse($topup->created_at)->format('M d, Y h:i A') }}</td>
                                        <td class="px-2 py-2 md:px-6 md:py-4 font-semibold text-slate-700">{{ $topup->reference_note }}</td>
                                        <td class="px-2 py-2 md:px-6 md:py-4 font-semibold text-slate-700">{{ $topupUser->name ?? 'System' }}</td>
                                        <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-emerald-600">+ Rs.{{ number_format($topup->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No top-ups found in this range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-100 bg-slate-50">
                        {{ $pettyCashTopups->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>


            <!-- BANK DEPOSITS LOG -->
            <div x-show="activeReport === 'deposits'" style="display:none;" x-transition>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Branch</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Deposit Date</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Reference / Notes</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Logged By</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bankDeposits ?? [] as $deposit)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $deposit->branch->name ?? 'N/A' }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-semibold text-slate-500">{{ $deposit->deposited_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="font-black text-slate-700">{{ $deposit->reference_number ?? '-' }}</div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-400 mt-1">{{ $deposit->notes ?? '' }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-semibold text-slate-700">{{ $deposit->user->name ?? 'System' }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-emerald-600">Rs.{{ number_format($deposit->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No bank deposits found in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Bank Deposits Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $bankDeposits->appends(request()->query())->links() }}
                </div>
            </div>
        </div>

        <!-- Pending Orders Modal -->
        <div x-cloak x-show="pendingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[85vh]" @click.away="pendingModalOpen = false">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-amber-50">
                    <div>
                        <h3 class="text-[11px] md:text-base font-black text-amber-600">Pending Orders</h3>
                        <p class="text-[10px] md:text-sm font-bold text-amber-700 mt-1" x-text="'Service: ' + selectedServiceName"></p>
                    </div>
                    <button @click="pendingModalOpen = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors font-bold text-[11px] md:text-base">✕</button>
                </div>
                <div class="overflow-y-auto bg-slate-50 flex-1">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Service Amount</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="order in selectedServiceOrders" :key="order.id">
                                <tr class="bg-white hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <a :href="'/orders/view/' + order.order_number" target="_blank" class="font-bold text-blue-600 hover:underline" x-text="order.order_number"></a>
                                            <span x-show="order.payment_method === 'credit'" class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md">CREDIT</span>
                                            <span x-show="order.payment_method === 'pay_on_pickup'" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md">PAY ON PICKUP</span>
                                        </div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-400 mt-1" x-text="new Date(order.created_at).toLocaleDateString()"></div>
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-900">
                                        <span x-text="order.customer ? order.customer.name : 'Walk-in'"></span>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1" x-text="order.customer ? order.customer.phone : ''"></div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-black text-rose-500" x-text="'Rs.' + parseFloat(order.service_due || 0).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-center">
                                        <a :href="'/orders/view/' + order.order_number" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[9px] md:text-xs font-bold rounded-lg shadow-sm transition-colors inline-block">View</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="selectedServiceOrders.length === 0">
                                <td colspan="4" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 font-semibold text-[10px] md:text-sm">No pending orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Collect Credit Payment Modal -->
        <div x-cloak x-show="collectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.away="collectModalOpen = false">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-orange-50">
                    <div>
                        <h3 class="text-sm md:text-base font-black text-orange-600">Collect Credit Payment</h3>
                        <p class="text-[10px] md:text-sm font-bold text-orange-700 mt-1" x-text="'Order: ' + collectOrderNumber"></p>
                    </div>
                    <button @click="collectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200 transition-colors font-bold">✕</button>
                </div>
                <div class="p-5 bg-white">
                    <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount to Collect (Rs)</label>
                            <input type="number" step="0.01" name="amount" x-model="collectAmount" max="" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-0">
                        </div>
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                            <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-0">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-black rounded-xl shadow-lg shadow-orange-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                            Confirm Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ApexCharts logic
            const dailyData = @json($dailyRevenue);
            const walkinRev = {{ $walkinRevenue }};
            const deliveryRev = {{ $deliveryRevenue }};
            
            // Format dates and series for revenue trend
            const categories = dailyData.map(d => {
                let date = new Date(d.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            const seriesData = dailyData.map(d => parseFloat(d.revenue));

            // Revenue Chart
            var revOptions = {
                series: [{
                    name: 'Revenue',
                    data: seriesData
                }],
                chart: {
                    height: 300,
                    type: 'area',
                    toolbar: { show: false },
                    fontFamily: 'inherit'
                },
                colors: ['#2563eb'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: { style: { colors: '#64748b', fontWeight: 600 } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#64748b', fontWeight: 600 },
                        formatter: function(val) { return "Rs." + val.toLocaleString() }
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    y: { formatter: function(val) { return "Rs." + val.toLocaleString() } }
                }
            };

            var revChart = new ApexCharts(document.querySelector("#revenueChart"), revOptions);
            revChart.render();

            // Payment Chart (Donut)
            let collectedCash = {{ $collectedCash ?? 0 }};
            let collectedCard = {{ $collectedCard ?? 0 }};
            let collectedBank = {{ $collectedBank ?? 0 }};
            let totalPending = {{ $totalPending ?? 0 }};

            if (collectedCash > 0 || collectedCard > 0 || collectedBank > 0 || totalPending > 0) {
                var payOptions = {
                    series: [collectedCash, collectedCard, collectedBank, totalPending],
                    labels: ['Cash', 'Card', 'Bank Transfer', 'Pending Receivables'],
                    chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                    colors: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    name: { fontSize: '14px', fontWeight: 600, color: '#64748b' },
                                    value: { fontSize: '24px', fontWeight: 900, color: '#0f172a', formatter: function(val) { return "Rs." + val.toLocaleString() } },
                                    total: {
                                        show: true,
                                        label: 'Total Expected',
                                        formatter: function (w) {
                                            return "Rs." + (w.globals.seriesTotals.reduce((a, b) => a + b, 0)).toLocaleString()
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 },
                    legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 700, markers: { radius: 12 } },
                    tooltip: { theme: 'light', y: { formatter: function(val) { return "Rs." + val.toLocaleString() } } }
                };
                var payChart = new ApexCharts(document.querySelector("#paymentChart"), payOptions);
                payChart.render();
            } else {
                document.querySelector("#paymentChart").innerHTML = '<div class="text-center text-slate-400 font-semibold p-10">No payment data to display</div>';
            }
        });

        function reportData() {
            return {
                activeReport: '{{ request()->has('credit_page') ? 'credit_orders' : 'expenses' }}',
                expenseCategoryFilter: '',
                orders: @json($orders->getCollection()),
                pendingModalOpen: false,
                selectedServiceName: '',
                selectedServiceOrders: [],
                
                collectModalOpen: false,
                collectOrderId: null,
                collectOrderNumber: '',
                collectAmount: 0,

                openCollectModal(id, number, amount) {
                    this.collectOrderId = id;
                    this.collectOrderNumber = number;
                    this.collectAmount = amount;
                    this.collectModalOpen = true;
                },

                openPendingOrders(serviceName) {
                    this.selectedServiceName = serviceName;
                    
                    let filtered = [];
                    this.orders.forEach(order => {
                        const total = parseFloat(order.total) || 0;
                        const paid = parseFloat(order.paid_amount) || 0;
                        if (total <= paid) return;

                        if (serviceName === '__all__') {
                            order.service_due = total - paid;
                            filtered.push(order);
                            return;
                        }

                        let matchFound = false;
                        let serviceValue = 0;
                        
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                const svc = item.service_type || item.serviceType || item.service_type_id;
                                if (!svc) return;
                                const name = typeof svc === 'object' ? (svc.name || '') : '';
                                if (name === serviceName) {
                                    matchFound = true;
                                    serviceValue += parseFloat(item.total_price) || 0;
                                }
                            });
                        }

                        if (matchFound) {
                            // Calculate specific service due
                            let subtotal = parseFloat(order.subtotal) || 0;
                            let discount = parseFloat(order.discount) || 0;
                            let proportion = subtotal > 0 ? (1 - (discount / subtotal)) : 1;
                            let finalServiceValue = serviceValue * proportion;
                            
                            // Calculate pending proportion
                            let orderPending = total - paid;
                            let pendingProportion = total > 0 ? (orderPending / total) : 0;
                            
                            order.service_due = finalServiceValue * pendingProportion;
                            filtered.push(order);
                        }
                    });
                    
                    this.selectedServiceOrders = filtered;
                    this.pendingModalOpen = true;
                }
            }
        }
    </script>
</x-app-layout>


