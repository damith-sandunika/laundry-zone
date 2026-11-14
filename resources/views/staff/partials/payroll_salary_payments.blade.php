                <!-- Recent Salary Payments (Disbursed) -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Salary Payment Status</h3>
                        <p class="text-[10px] text-gray-400">All recorded salary payouts</p>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($salaries as $salary)
                            <div class="p-4 {{ $salary->trashed() ? 'bg-rose-50/40 border-l-4 border-rose-400' : 'hover:bg-gray-50/60' }} transition flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-bold text-gray-800">{{ $salary->description }}</p>
                                            @if($salary->trashed())
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-600 text-white">Deleted</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Date: {{ \Carbon\Carbon::parse($salary->expense_date)->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-sm font-black {{ $salary->trashed() ? 'text-rose-500 line-through' : 'text-green-600' }}">Rs. {{ number_format($salary->amount, 2) }}</span>
                                        @if($salary->previous_amount)
                                            <div class="text-[10px] text-gray-400">Previous: Rs. {{ number_format($salary->previous_amount, 2) }}</div>
                                        @endif
                                    </div>
                                </div>
                                <!-- Audit History Log for Salary Payment -->
                                <div class="pt-2 border-t border-gray-100/80 text-[10px] text-gray-500 flex flex-col sm:flex-row justify-between sm:items-end gap-2">
                                    <div class="space-y-1">
                                        @if($salary->createdBy)
                                            <div>Paid by: <strong class="text-gray-700">{{ $salary->createdBy->name }}</strong> on {{ $salary->created_at ? $salary->created_at->format('M d, h:i A') : '-' }}</div>
                                        @endif

                                        @if($salary->auditLogs && $salary->auditLogs->where('action', 'edited')->count() > 0)
                                            <div class="space-y-0.5 mt-1 border-l-2 border-amber-300 pl-2">
                                                @foreach($salary->auditLogs->where('action', 'edited') as $editLog)
                                                    <div class="text-amber-800">
                                                        ✏️ <strong>Edited by {{ $editLog->user->name ?? 'User' }}</strong> ({{ $editLog->created_at->format('M d, h:i A') }}): 
                                                        <span class="line-through text-gray-400">Rs. {{ number_format($editLog->old_amount, 2) }}</span> → 
                                                        <span class="font-bold">Rs. {{ number_format($editLog->new_amount, 2) }}</span> 
                                                        | <span class="italic text-gray-600">Reason: "{{ $editLog->reason }}"</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($salary->updatedBy && $salary->edit_reason)
                                            <div class="text-amber-700 font-semibold">✏️ Edited by: <strong>{{ $salary->updatedBy->name }}</strong> ({{ $salary->updated_at->format('M d, h:i A') }}) | Reason: "{{ $salary->edit_reason }}"</div>
                                        @endif

                                        @if($salary->trashed())
                                            <div class="text-rose-700 font-bold border-l-2 border-rose-400 pl-2 mt-1">
                                                🗑️ Deleted by: <strong>{{ optional($salary->deletedBy)->name ?? 'User' }}</strong> ({{ $salary->deleted_at->format('M d, h:i A') }}) | Reason: "{{ $salary->delete_reason ?? '-' }}"
                                            </div>
                                        @endif
                                    </div>
                                    @if(!$salary->trashed())
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" 
                                                data-id="{{ $salary->id }}" 
                                                data-amount="{{ $salary->amount }}" 
                                                data-date="{{ $salary->expense_date ? \Carbon\Carbon::parse($salary->expense_date)->format('Y-m-d') : '' }}" 
                                                data-description="{{ $salary->description }}"
                                                @click="openEditSalaryEl($el)" 
                                                class="px-2 py-0.5 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded text-[11px] transition-colors">
                                            Edit
                                        </button>
                                        <button type="button" @click="openDeleteSalary({{ $salary->id }})" class="px-2 py-0.5 bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold rounded text-[11px] transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center">
                                <p class="text-xs font-semibold text-gray-400">No salary records found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
