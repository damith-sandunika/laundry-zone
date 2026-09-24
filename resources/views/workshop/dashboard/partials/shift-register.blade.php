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
                        <span class="text-[14px] font-black text-white tracking-tight">Rs.{{ number_format($activeSession->opening_cash, 0) }}</span>
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

                    <div class="py-2 mt-2">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Shift Revenue (Order Value)</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Total Payments Collected</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['total_payments_collected'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-700/50 mb-2">
                        <span class="text-[12px] font-semibold text-slate-400">Less: Workshop Delivery Fees</span>
                        <span class="text-[14px] font-bold text-slate-400">- Rs.{{ number_format($stats['workshop_direct_delivery_fees'] ?? 0, 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 bg-slate-800/30 -mx-6 px-6 mb-2">
                        <span class="text-[12px] font-bold text-blue-400">Direct Workshop Sales</span>
                        <span class="text-[14px] font-black text-blue-400">Rs.{{ number_format($stats['workshop_direct_commissionable_sales'] ?? 0, 0) }}</span>
                    </div>

                    <div class="py-1">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Branch Orders Breakdown</span>
                    </div>
                    @if(!empty($stats['branch_breakdown']))
                        @foreach($stats['branch_breakdown'] as $b)
                            <div class="bg-slate-800/40 rounded-xl p-3 my-1 border border-slate-700/50">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[12px] font-bold text-slate-300">{{ $b['name'] }}</span>
                                    <span class="text-[13px] font-bold text-slate-300">Total: Rs.{{ number_format($b['gross_sales'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[11px] font-semibold text-slate-400">Given Comm. ({{ $b['profit_percentage'] }}%)</span>
                                    <span class="text-[12px] font-bold text-red-400">- Rs.{{ number_format($b['branch_commission'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-1.5 border-t border-slate-700/50">
                                    <span class="text-[11px] font-bold text-emerald-500">Workshop Portion</span>
                                    <span class="text-[12px] font-bold text-emerald-400">Rs.{{ number_format($b['workshop_commission'], 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="py-3 text-center text-[12px] font-semibold text-slate-500 bg-slate-800/20 rounded-xl mb-2">
                            No branch orders processed in this shift.
                        </div>
                    @endif

                    <div class="flex justify-between items-center py-2 border-b border-slate-700/50">
                        <span class="text-[12px] font-semibold text-emerald-400">Total Workshop Commission (From Branches)</span>
                        <span class="text-[14px] font-bold text-emerald-400">Rs.{{ number_format($stats['workshop_commission_from_branches'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-slate-700">
                        <span class="text-[13px] font-bold text-emerald-400">Total Sales Revenue</span>
                        <span class="text-[14px] font-black text-emerald-400">Rs.{{ number_format($stats['total_sales_revenue'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Workshop Delivery Fees</span>
                        <span class="text-[14px] font-bold text-slate-300">+ Rs.{{ number_format($stats['workshop_direct_delivery_fees'] ?? 0, 0) }}</span>
                    </div>


                    <div class="flex justify-between items-center py-3 bg-blue-900/20 -mx-6 px-6 border-y border-slate-800">
                        <span class="text-[13px] font-bold text-amber-400">Today Total Sales (Net Income)</span>
                        <span class="text-[16px] font-black text-amber-400">Rs.{{ number_format($stats['net_income'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4 bg-emerald-900/20 -mx-6 px-6 border-b border-emerald-900/30">
                        <span class="text-[13px] font-black text-emerald-400 uppercase tracking-wide">Cash in Drawer</span>
                        <span class="text-[18px] font-black text-emerald-400">Rs.{{ number_format($stats['cash_balance'] ?? 0, 0) }}</span>
                    </div>

                    <!-- Pending Payments -->
                    <div class="py-2 mt-2 border-t border-slate-700 pt-3">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Pending Payments (Today's Orders)</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Our Workshop</span>
                        <span class="text-[13px] font-bold text-amber-400">Rs.{{ number_format($stats['our_pending_payments'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 mb-2">
                        <span class="text-[12px] font-semibold text-slate-400">Branches</span>
                        <span class="text-[13px] font-bold text-amber-400">Rs.{{ number_format($stats['branch_pending_payments'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-t border-slate-700">
                        <span class="text-[11px] font-semibold text-slate-500">Opened By</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ $activeSession->user?->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b-0">
                        <span class="text-[11px] font-semibold text-slate-500">Time Started</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ \Carbon\Carbon::parse($activeSession->opened_at)->format('h:i A') }}</span>
                    </div>
                </div>
            </div>
