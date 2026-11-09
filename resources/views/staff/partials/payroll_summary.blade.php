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
                
                <!-- Current Month Payroll -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl border border-green-100 shadow-sm overflow-hidden p-5 mb-6">
                    <h3 class="font-bold text-gray-800 mb-4">Payroll ({{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }})</h3>
                    <div class="space-y-2 mb-4 text-xs">
                        <div class="flex justify-between text-blue-600">
                            <span class="font-medium">Regular Pay ({{ $payroll->total_regular_hours_for_pay_formatted }} × Rs. {{ number_format($payroll->hourly_rate, 2) }}/h):</span>
                            <span class="font-bold">Rs. {{ number_format($payroll->base_pay, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-indigo-600">
                            <span>OT ({{ $payroll->ot_hours_formatted }}):</span>
                            <span class="font-bold">+ Rs. {{ number_format($payroll->ot_amount, 2) }}</span>
                        </div>
                        @if($payroll->allowances > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Allowances:</span>
                            <span class="font-bold">+ Rs. {{ number_format($payroll->allowances, 2) }}</span>
                        </div>
                        @endif
                        @if($payroll->manual_deductions > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Manual Deductions:</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->manual_deductions, 2) }}</span>
                        </div>
                        @endif
                        
                        <!-- Full Total Salary Row -->
                        <div class="pt-2 pb-1 my-1 border-t border-b border-blue-100 bg-blue-50/60 px-2 rounded-lg flex justify-between items-center font-black">
                            <span class="text-slate-800">Full Total Salary:</span>
                            <span class="text-blue-700 text-sm">Rs. {{ number_format($payroll->gross_salary, 2) }}</span>
                        </div>

                        @if($payroll->advances > 0)
                        <div class="flex justify-between text-purple-600 px-1 pt-1">
                            <span class="font-semibold">Salary Advances:</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->advances, 2) }}</span>
                        </div>
                        @endif

                        @if($payroll->already_paid > 0)
                        <div class="flex justify-between text-orange-600 px-1 pt-1">
                            <span class="font-semibold">Already Paid:</span>
                            <span class="font-bold">- Rs. {{ number_format($payroll->already_paid, 2) }}</span>
                        </div>
                        @endif
                        
                        <!-- Payable Balance Row -->
                        <div class="pt-2 border-t border-green-200 flex justify-between items-center text-sm">
                            <span class="font-black text-slate-800">Payable Balance:</span>
                            <span class="font-black text-green-700 text-lg">Rs. {{ number_format($payroll->net_salary, 2) }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('hr.payslip.download', ['staff' => $staff->id, 'month' => $payroll->month, 'year' => $payroll->year]) }}" 
                           target="_blank"
                           class="flex-1 text-center py-2 px-3 bg-white text-gray-700 text-xs font-bold rounded-lg border border-gray-200 hover:bg-gray-50 transition shadow-sm">
                           📄 PDF Payslip
                        </a>
                        @if($payroll->net_salary > 0)
                        <button type="button" @click="paySalaryAmount = '{{ $payroll->net_salary }}'; paySalaryModalOpen = true" class="flex-1 text-center py-2 px-3 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition shadow-sm shadow-green-600/30">
                            💸 Pay Now
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Allowances & Deductions History -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-xs">Allowances & Deductions</h3>
                        <div class="flex gap-1">
                            <button @click="allowanceModalOpen = true" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded hover:bg-emerald-100">+ Allowance</button>
                            <button @click="deductionModalOpen = true" class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded hover:bg-rose-100">- Deduction</button>
                        </div>
                    </div>
                    <div class="p-0 divide-y divide-gray-50 text-xs">
                        @forelse($staff->salaryAdjustments()->whereMonth('date', $payroll->month)->whereYear('date', $payroll->year)->latest('date')->get() as $adj)
                            <div class="p-3 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase {{ $adj->type === 'allowance' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $adj->type }}
                                    </span>
                                    <p class="text-[11px] font-bold text-gray-700 mt-1">{{ \Carbon\Carbon::parse($adj->date)->format('M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[140px]">{{ $adj->reason ?? 'No reason' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold {{ $adj->type === 'allowance' ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $adj->type === 'allowance' ? '+' : '-' }} Rs. {{ number_format($adj->amount, 2) }}
                                    </span>
                                    <form method="POST" action="{{ route('hr.adjustments.destroy', $adj->id) }}" onsubmit="return confirm('Remove this adjustment?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 font-bold text-xs">&times;</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center">
                                <p class="text-xs text-gray-400 font-medium">No adjustments for this month.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
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
