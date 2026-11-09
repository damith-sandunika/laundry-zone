<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">Accounts & Finance Dashboard</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Financial performance, petty cash & cash flow overview</p>
            </div>

            <!-- Branch Filter -->
            <form method="GET" action="{{ route('accountant.dashboard') }}" class="flex items-center gap-2">
                <select name="branch_id" onchange="this.form.submit()" class="text-xs font-bold bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">🏢 All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                @if($selectedBranchId)
                    <a href="{{ route('accountant.dashboard') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 px-2 py-1">Reset</a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Today's Revenue -->
            <div class="p-5 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl text-white shadow-lg shadow-blue-500/20">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-100">Today Sales</span>
                    <span class="p-2 bg-white/10 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <h3 class="text-2xl font-black mt-3">Rs. {{ number_format($todayRevenue, 2) }}</h3>
                <p class="text-[11px] text-blue-100 mt-1 font-semibold">Month: Rs. {{ number_format($monthRevenue, 2) }}</p>
            </div>

            <!-- Today's Cash In Hand -->
            <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Cash Collected Today</span>
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </span>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mt-3">Rs. {{ number_format($todayCashCollected, 2) }}</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-1">Digital: Rs. {{ number_format($todayDigitalCollected, 2) }}</p>
            </div>

            <!-- Today's Petty Cash Expenses -->
            <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Petty Cash Today</span>
                    <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <h3 class="text-2xl font-black text-rose-600 mt-3">Rs. {{ number_format($todayExpenses, 2) }}</h3>
                <p class="text-[11px] text-slate-400 font-semibold mt-1">Month Total: Rs. {{ number_format($monthExpenses, 2) }}</p>
            </div>

            <!-- Outstanding Receivables -->
            <div class="p-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between text-slate-500">
                    <span class="text-xs font-bold uppercase tracking-wider">Credit Receivables</span>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <h3 class="text-2xl font-black text-amber-600 mt-3">Rs. {{ number_format($totalPendingReceivables, 2) }}</h3>
                <p class="text-[11px] text-slate-500 font-bold mt-1">
                    Corrections Pending: <span class="text-red-500 font-extrabold">{{ $pendingCorrectionsCount }}</span>
                </p>
            </div>
        </div>

        <!-- Quick Links / Shortcuts -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('reports.sales') }}" class="p-4 bg-white border border-slate-100 rounded-2xl hover:border-blue-200 hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">📈</div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Sales Report</h4>
                    <p class="text-[10px] text-slate-400">View daily & monthly breakdowns</p>
                </div>
            </a>
            <a href="{{ route('reports.credit-orders') }}" class="p-4 bg-white border border-slate-100 rounded-2xl hover:border-blue-200 hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">💳</div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Credit Report</h4>
                    <p class="text-[10px] text-slate-400">Unpaid customer invoices</p>
                </div>
            </a>
            <a href="{{ route('expenses.index') }}" class="p-4 bg-white border border-slate-100 rounded-2xl hover:border-blue-200 hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">💵</div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Petty Cash Logs</h4>
                    <p class="text-[10px] text-slate-400">Manage expenses & vouchers</p>
                </div>
            </a>
            <a href="{{ route('superadmin.order-logs') }}" class="p-4 bg-white border border-slate-100 rounded-2xl hover:border-blue-200 hover:shadow-sm transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">🔍</div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Order Corrections</h4>
                    <p class="text-[10px] text-slate-400">Audit price changes & overrides</p>
                </div>
            </a>
        </div>

        <!-- 2 Column Content: Open Registers & Recent Petty Cash -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Active Registers / Shifts -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800">Active Branch Shifts & Cash Drawers</h3>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">{{ $activeSessions->count() }} Open</span>
                </div>
                <div class="divide-y divide-slate-100 overflow-x-auto">
                    @forelse($activeSessions as $session)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $session->branch->name ?? 'Main Branch' }}</p>
                                <p class="text-[11px] text-slate-400">Cashier: {{ $session->user->name ?? 'N/A' }} • Opened {{ $session->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-slate-500">Opening: Rs. {{ number_format($session->opening_cash, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400 font-semibold">No active shifts open at the moment.</div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Expenses / Petty Cash -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-800">Recent Petty Cash Expenses</h3>
                    <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentExpenses as $expense)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $expense->description }}</p>
                                <p class="text-[11px] text-slate-400">{{ $expense->branch->name ?? 'General' }} • {{ $expense->category }} • {{ $expense->expense_date }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black text-rose-600">- Rs. {{ number_format($expense->amount, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400 font-semibold">No recent expenses logged.</div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

