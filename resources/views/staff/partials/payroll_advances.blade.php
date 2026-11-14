                <!-- Salary Advances -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-gray-800">Salary Advances</h3>
                            <p class="text-[10px] text-gray-400">Advances issued for this month</p>
                        </div>
                        <button @click="advanceModalOpen = true" class="text-[10px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition-colors">
                            + Add Advance
                        </button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->salaryAdvances()->withTrashed()->with(['createdBy', 'updatedBy', 'deletedBy', 'auditLogs.user'])->whereMonth('date', $payroll->month)->whereYear('date', $payroll->year)->latest('date')->get() as $adv)
                            <div class="p-4 {{ $adv->trashed() ? 'bg-rose-50/40 border-l-4 border-rose-400' : 'hover:bg-gray-50/60' }} transition flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-bold text-gray-800">{{ \Carbon\Carbon::parse($adv->date)->format('M d, Y') }}</p>
                                            @if($adv->trashed())
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-600 text-white">Deleted</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] font-medium text-gray-600 mt-0.5">
                                            <span class="text-gray-400">Advance Reason:</span> {{ $adv->reason ?: 'No reason provided' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-black {{ $adv->trashed() ? 'text-rose-500 line-through' : 'text-indigo-600' }}">Rs. {{ number_format($adv->amount, 2) }}</span>
                                        @if($adv->previous_amount)
                                            <div class="text-[10px] text-gray-400">Previous: Rs. {{ number_format($adv->previous_amount, 2) }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Audit History Log for Advance -->
                                <div class="pt-2 border-t border-gray-100/80 text-[10px] text-gray-500 flex flex-col sm:flex-row justify-between sm:items-end gap-2">
                                    <div class="space-y-1">
                                        @if($adv->createdBy)
                                            <div>Added by: <strong class="text-gray-700">{{ $adv->createdBy->name }}</strong> on {{ $adv->created_at ? $adv->created_at->format('M d, h:i A') : '-' }}</div>
                                        @endif

                                        @if($adv->auditLogs && $adv->auditLogs->where('action', 'edited')->count() > 0)
                                            <div class="space-y-0.5 mt-1 border-l-2 border-amber-300 pl-2">
                                                @foreach($adv->auditLogs->where('action', 'edited') as $editLog)
                                                    <div class="text-amber-800">
                                                        ✏️ <strong>Edited by {{ $editLog->user->name ?? 'User' }}</strong> ({{ $editLog->created_at->format('M d, h:i A') }}): 
                                                        <span class="line-through text-gray-400">Rs. {{ number_format($editLog->old_amount, 2) }}</span> → 
                                                        <span class="font-bold">Rs. {{ number_format($editLog->new_amount, 2) }}</span> 
                                                        | <span class="italic text-gray-600">Reason: "{{ $editLog->reason }}"</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($adv->updatedBy && $adv->edit_reason)
                                            <div class="text-amber-700 font-semibold">✏️ Edited by: <strong>{{ $adv->updatedBy->name }}</strong> ({{ $adv->updated_at->format('M d, h:i A') }}) | Reason: "{{ $adv->edit_reason }}"</div>
                                        @endif

                                        @if($adv->trashed())
                                            <div class="text-rose-700 font-bold border-l-2 border-rose-400 pl-2 mt-1">
                                                🗑️ Deleted by: <strong>{{ optional($adv->deletedBy)->name ?? 'User' }}</strong> ({{ $adv->deleted_at->format('M d, h:i A') }}) | Reason: "{{ $adv->delete_reason ?? '-' }}"
                                            </div>
                                        @endif
                                    </div>
                                    @if(!$adv->trashed())
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" 
                                                data-id="{{ $adv->id }}" 
                                                data-amount="{{ $adv->amount }}" 
                                                data-date="{{ $adv->date ? \Carbon\Carbon::parse($adv->date)->format('Y-m-d') : '' }}" 
                                                data-reason="{{ $adv->reason }}"
                                                @click="openEditAdvanceEl($el)" 
                                                class="px-2 py-0.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded text-[11px] transition-colors">
                                            Edit
                                        </button>
                                        <button type="button" @click="openDeleteAdvance({{ $adv->id }})" class="px-2 py-0.5 bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold rounded text-[11px] transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-xs font-semibold text-gray-400">No active salary advances.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
