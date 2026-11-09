{{-- Shift Register (Dark Card) --}}
            <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-xl shadow-slate-900/20 animate-slide-up" style="animation-delay: 350ms;">
                <div class="px-6 py-5 border-b border-slate-700/50 flex items-center justify-between gap-4 bg-slate-800/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white tracking-tight">Shift Register</h3>
                            <p class="text-[12px] font-medium text-slate-400">Current session metrics</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('depositModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-500/20 border border-emerald-500/30 hover:bg-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl transition-colors whitespace-nowrap">
                        Deposit to Bank
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-0 divide-y divide-slate-800">
                    <div class="flex justify-between items-center py-3">
                        <span class="text-[12px] font-semibold text-slate-400">Opening Balance</span>
                        <span class="text-[14px] font-black text-white tracking-tight">Rs.{{ number_format($activeSession->opening_cash ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Cash Sales</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['cash_collected'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Card Sales</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['card_payments_amount'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Bank Transfers</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['bank_transfers'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-700 mb-2">
                        <span class="text-[12px] font-bold text-slate-300">Total Payments Collected</span>
                        <span class="text-[14px] font-black text-white">Rs.{{ number_format($stats['total_payments_collected'] ?? 0, 0) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 mt-2">
                        <span class="text-[12px] font-semibold text-slate-400">Gross Sales</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['gross_sales'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Less: Delivery Fees</span>
                        <span class="text-[14px] font-bold text-slate-400">- Rs.{{ number_format($stats['total_delivery_fees'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 bg-slate-800/30 -mx-6 px-6">
                        <span class="text-[12px] font-bold text-blue-400">Commissionable Sales</span>
                        <span class="text-[14px] font-black text-blue-400">Rs.{{ number_format($stats['commissionable_sales'] ?? 0, 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-3">
                        <span class="text-[12px] font-semibold text-emerald-400">Branch Commission ({{ $stats['branch_profit_percentage'] }}%)</span>
                        <span class="text-[14px] font-bold text-emerald-400">Rs.{{ number_format($stats['branch_commission'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Delivery Fees Collected</span>
                        <span class="text-[14px] font-bold text-emerald-400">+ Rs.{{ number_format($stats['total_delivery_fees'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 bg-blue-900/20 -mx-6 px-6 border-y border-slate-800">
                        <span class="text-[13px] font-bold text-amber-400">Total Branch Revenue</span>
                        <span class="text-[15px] font-black text-amber-400">Rs.{{ number_format($stats['total_branch_revenue'] ?? 0, 0) }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3">
                        <span class="text-[12px] font-semibold text-slate-400">Expenses</span>
                        <span class="text-[14px] font-bold text-red-400">- Rs.{{ number_format($stats['expenses_logged'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3">
                        <span class="text-[13px] font-bold text-emerald-400">Net Income</span>
                        <span class="text-[15px] font-black text-emerald-400">Rs.{{ number_format($stats['net_income'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4 bg-emerald-900/20 -mx-6 px-6 border-t border-emerald-900/30">
                        <span class="text-[13px] font-black text-emerald-400 uppercase tracking-wide">Cash in Drawer</span>
                        <span class="text-[18px] font-black text-emerald-400">Rs.{{ number_format($stats['cash_balance'] ?? 0, 0) }}</span>
                    </div>
                    
                    <div class="py-2 mt-2 border-t border-slate-700/50 pt-3">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Pending Payments</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Today's Orders Pending</span>
                        <span class="text-[13px] font-bold text-amber-400">Rs.{{ number_format($stats['pending_payments'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-t border-slate-700">
                        <span class="text-[11px] font-semibold text-slate-500">Opened By</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ $activeSession ? ($activeSession->user?->name ?? 'Unknown') : 'No Active Shift' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b-0">
                        <span class="text-[11px] font-semibold text-slate-500">Time Started</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ $activeSession && $activeSession->opened_at ? \Carbon\Carbon::parse($activeSession->opened_at)->format('h:i A') : '-' }}</span>
                    </div>
                </div>
            </div>
