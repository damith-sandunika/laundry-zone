            <!-- PETTY CASH LOG -->
            <div x-show="activeReport === 'expenses'" style="display:none;" x-transition>
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-white">
                    <h3 class="font-bold text-slate-800">Petty Cash Log</h3>
                    <select x-model="expenseCategoryFilter" class="px-3 py-1.5 border-2 border-slate-200 rounded-lg text-[10px] md:text-sm font-bold bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0">
                        <option value="">All Categories</option>
                        @php $expenseCategories = $expensesList->pluck('category')->unique(); @endphp
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Date</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Category</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Details</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($expensesList as $exp)
                                <tr x-show="expenseCategoryFilter === '' || expenseCategoryFilter === '{{ $exp->category }}'" class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-[10px] md:text-sm font-semibold text-slate-500">{{ \Carbon\Carbon::parse($exp->created_at)->format('M d, Y h:i A') }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200 rounded-lg">
                                            {{ str_replace('_', ' ', $exp->category) }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-slate-900">{{ $exp->description }}</td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-rose-500">Rs.{{ number_format($exp->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No petty cash expenses logged in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Expenses Pagination -->
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $expensesList->appends(request()->query())->links() }}
                </div>
            </div>
