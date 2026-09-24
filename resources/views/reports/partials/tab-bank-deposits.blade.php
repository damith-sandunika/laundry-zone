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
