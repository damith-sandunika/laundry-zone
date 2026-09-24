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
