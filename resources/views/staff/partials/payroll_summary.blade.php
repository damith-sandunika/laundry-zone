                <!-- Salary Advances -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Salary Advances</h3>
                        <button @click="advanceModalOpen = true" class="text-[10px] uppercase font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 hover:bg-indigo-100">Add New</button>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($staff->salaryAdvances()->whereMonth('date', $payroll->month)->whereYear('date', $payroll->year)->latest('date')->get() as $adv)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-xs font-bold text-gray-700">{{ \Carbon\Carbon::parse($adv->date)->format('M d, Y') }}</p>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[120px]">{{ $adv->reason ?? 'No reason provided' }}</p>
                                </div>
                                <span class="text-sm font-black text-indigo-600">Rs. {{ number_format($adv->amount, 2) }}</span>
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
                        <form method="POST" action="{{ route('staff.paySalary') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $staff->id }}">
                            <input type="hidden" name="month" value="{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10, $payroll->year)) }}">
                            <input type="hidden" name="amount" value="{{ $payroll->net_salary }}">
                            <button type="submit" class="w-full text-center py-2 px-3 bg-green-600 text-white text-xs font-bold rounded-lg hover:bg-green-700 transition shadow-sm shadow-green-600/30">
                                💸 Pay Now
                            </button>
                        </form>
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
                
                <!-- Recent Payslips -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-800">Salary Payment Status</h3>
                    </div>
                    <div class="p-0 divide-y divide-gray-50">
                        @forelse($salaries as $salary)
                            <div class="p-4 flex justify-between items-center hover:bg-gray-50/50 transition">
                                <div>
                                    <p class="text-[11px] font-bold text-gray-700">{{ $salary->description }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">
                                        Paid by: <span class="font-bold text-gray-700">{{ optional($salary->createdBy)->name ?? 'System' }}</span>
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $salary->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-green-600">Rs. {{ number_format($salary->amount, 2) }}</span>
                                    <form method="POST" action="{{ route('expenses.destroy', $salary->id) }}" onsubmit="return confirm('Remove this salary payment record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-300 hover:text-red-500 font-bold text-xs">&times;</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center">
                                <p class="text-xs font-semibold text-gray-400">No salary records found.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
