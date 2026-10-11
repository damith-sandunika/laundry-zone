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
                                @if(auth()->user()->hasAdminAccess() || auth()->user()->isWorkshopAdmin())
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bankDeposits ?? [] as $deposit)
                                <tr class="hover:bg-slate-50 transition-colors {{ $deposit->trashed() ? 'opacity-60 bg-red-50/30' : '' }}">
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">
                                        {{ $deposit->branch->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-semibold text-slate-500">
                                        {{ $deposit->deposited_at->format('Y-m-d h:i A') }}
                                        @if($deposit->trashed())
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-600 border border-red-200">
                                                    <i class="fa-solid fa-trash text-[8px]"></i> DELETED
                                                </span>
                                                <div class="text-[9px] text-red-400 mt-0.5">by {{ $deposit->deletedByUser->name ?? 'Admin' }} — {{ $deposit->deleted_at->format('d M Y h:i A') }}</div>
                                                @if($deposit->delete_reason)
                                                    <div class="text-[9px] text-slate-400 mt-0.5 italic">Reason: {{ $deposit->delete_reason }}</div>
                                                @endif
                                            </div>
                                        @elseif($deposit->is_edited)
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-700 border border-amber-200">
                                                    <i class="fa-solid fa-pen text-[8px]"></i> EDITED
                                                </span>
                                                <div class="text-[9px] text-amber-500 mt-0.5">by {{ $deposit->editedByUser->name ?? 'Admin' }} — {{ $deposit->edited_at->format('d M Y h:i A') }}</div>
                                                @if($deposit->edit_notes)
                                                    <div class="text-[9px] text-slate-400 mt-0.5 italic">Note: {{ $deposit->edit_notes }}</div>
                                                @endif
                                                @if($deposit->original_amount)
                                                    <div class="text-[9px] text-slate-400 mt-0.5">Original: Rs.{{ number_format($deposit->original_amount, 2) }}</div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="font-black text-slate-700">{{ $deposit->reference_number ?? '-' }}</div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-400 mt-1">{{ $deposit->notes ?? '' }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-semibold text-slate-700">{{ $deposit->user->name ?? 'System' }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black {{ $deposit->trashed() ? 'text-red-400 line-through' : 'text-emerald-600' }}">
                                        Rs.{{ number_format($deposit->amount, 2) }}
                                    </td>
                                    @if(auth()->user()->hasAdminAccess() || auth()->user()->isWorkshopAdmin())
                                    <td class="px-2 py-2 md:px-3 md:py-4 text-center">
                                        @if(!$deposit->trashed())
                                            <div class="flex flex-row items-center justify-center gap-2" x-data="{ depositData: {{ \Illuminate\Support\Js::from(['id' => $deposit->id, 'amount' => $deposit->amount, 'reference_number' => $deposit->reference_number, 'notes' => $deposit->notes]) }} }">
                                                {{-- Edit Button --}}
                                                <button
                                                    title="Edit"
                                                    @click="$dispatch('open-deposit-edit', depositData)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100 transition-all">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 20h9"></path>
                                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                    </svg>
                                                </button>
                                                {{-- Delete Button --}}
                                                <button
                                                    title="Delete"
                                                    @click="$dispatch('open-deposit-delete', depositData)"
                                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-500 border border-red-200 hover:bg-red-100 transition-all">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                </button>
                                            </div>
                                        @else
                                            <span class="inline-block w-5 h-5 rounded-full bg-red-100 text-red-400 flex items-center justify-center" title="Deleted">
                                                <i class="fa-solid fa-ban text-[9px]"></i>
                                            </span>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No bank deposits found in this range.</td>
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

            {{-- ══════════════════════════════════════════════════════
                 BANK DEPOSIT EDIT MODAL
            ══════════════════════════════════════════════════════ --}}
            @if(auth()->user()->hasAdminAccess() || auth()->user()->isWorkshopAdmin())
            <div
                x-data="{ show: false, deposit: { id: null, amount: '', reference_number: '', notes: '' }, editNotes: '' }"
                @open-deposit-edit.window="deposit = $event.detail; editNotes = ''; show = true"
                x-show="show"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" x-transition>
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-amber-50">
                        <div>
                            <h3 class="text-sm font-black text-slate-800">Edit Bank Deposit</h3>
                            <p class="text-[10px] font-semibold text-amber-600 mt-0.5">This will be marked as edited with your name and timestamp.</p>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form
                        :action="'/bank-deposits/' + deposit.id"
                        method="POST"
                        class="p-5 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" x-model="deposit.amount"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Reference Number</label>
                            <input type="text" name="reference_number" x-model="deposit.reference_number"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                            <textarea name="notes" rows="2" x-model="deposit.notes"
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Edit Reason / Note <span class="text-red-500">*</span></label>
                            <input type="text" name="edit_notes" x-model="editNotes" placeholder="e.g. Correction of data entry error" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white">
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="show = false"
                                class="flex-1 py-2.5 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit"
                                class="flex-1 py-2.5 text-xs font-black text-white bg-amber-500 rounded-xl hover:bg-amber-600 shadow-sm transition-all">
                                <i class="fa-solid fa-pen mr-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 BANK DEPOSIT DELETE CONFIRM MODAL
            ══════════════════════════════════════════════════════ --}}
            <div
                x-data="{ show: false, deposit: { id: null, amount: '' }, reason: '' }"
                @open-deposit-delete.window="deposit = $event.detail; reason = ''; show = true"
                x-show="show"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden" x-transition>
                    <div class="p-5 border-b border-slate-100 bg-red-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-trash"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-800">Delete Deposit</h3>
                                <p class="text-[10px] font-semibold text-red-500 mt-0.5">Cash balance will be restored. Record remains visible as deleted.</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center mb-4">
                            <p class="text-[9px] font-black text-red-400 uppercase tracking-wider">Deposit Amount</p>
                            <p class="text-xl font-black text-red-600">Rs. <span x-text="parseFloat(deposit.amount).toFixed(2)"></span></p>
                        </div>
                        <form :action="'/bank-deposits/' + deposit.id" method="POST" class="space-y-4">
                            @csrf
                            @method('DELETE')
                            <div>
                                <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Reason for Deletion <span class="text-red-500">*</span></label>
                                <input type="text" name="delete_reason" x-model="reason" placeholder="e.g. Duplicate entry" required
                                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-red-400/30 focus:border-red-400 outline-none bg-white">
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="show = false"
                                    class="flex-1 py-2.5 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                                <button type="submit"
                                    class="flex-1 py-2.5 text-xs font-black text-white bg-red-500 rounded-xl hover:bg-red-600 shadow-sm transition-all">
                                    <i class="fa-solid fa-trash mr-1"></i> Confirm Delete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
