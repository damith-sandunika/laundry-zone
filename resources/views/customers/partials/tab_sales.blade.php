<!-- Sales Report Tab -->
<div>
    @if($monthlySales->isEmpty())
        <div class="p-8 text-center text-gray-400 text-sm">No orders found for this customer.</div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($monthlySales as $month)
        <div>
            <!-- Month Header Row (clickable) -->
            <div @click="toggleMonth('{{ $month['year_month'] }}')" class="flex items-center justify-between px-5 py-3 cursor-pointer hover:bg-emerald-50/40 transition-colors select-none">
                <!-- Left: month name + stats -->
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0">📅</div>
                    <div>
                        <div class="text-xs font-semibold text-slate-700">{{ $month['label'] }}</div>
                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                            <span>{{ $month['order_count'] }} order{{ $month['order_count'] !== 1 ? 's' : '' }}</span>
                            <span class="text-slate-300">|</span>
                            <span>{{ number_format($month['total_qty'], 0) }} items</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-emerald-600">Paid: Rs.{{ number_format($month['orders']->sum('paid_amount'), 2) }}</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-slate-700">Bill: Rs.{{ number_format($month['total_bill'], 2) }}</span>
                        </div>
                    </div>
                </div>
                <!-- Right: PDF + arrow -->
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('customers.salesReport.pdf', [$customer, 'month' => $month['year_month']]) }}"
                       target="_blank"
                       @click.stop
                       class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors"
                       title="Download PDF">
                       PDF
                    </a>
                    <svg :class="openMonth === '{{ $month['year_month'] }}' ? 'rotate-180' : ''"
                         class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <!-- Expanded Orders -->
            <div x-show="openMonth === '{{ $month['year_month'] }}'" x-transition style="display:none;">
                <div class="bg-slate-50 border-t border-slate-100">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-200">
                                <th class="px-5 py-2 text-left">Order #</th>
                                <th class="px-5 py-2 text-left">Date</th>
                                <th class="px-5 py-2 text-center">Total Qty</th>
                                <th class="px-5 py-2 text-right">Bill</th>
                                <th class="px-5 py-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($month['orders'] as $ord)
                            <tr class="hover:bg-white transition-colors">
                                <td class="px-5 py-2.5">
                                    <a href="{{ route('orders.show', $ord->order_number) }}" class="font-mono font-black text-blue-600 hover:underline">{{ $ord->order_number }}</a>
                                </td>
                                <td class="px-5 py-2.5 text-slate-500 font-semibold">{{ $ord->created_at->format('d M, H:i') }}</td>
                                <td class="px-5 py-2.5 text-center font-bold text-slate-700">
                                    {{ number_format($ord->items->sum('quantity'), 0) }}
                                </td>
                                <td class="px-5 py-2.5 text-right font-black text-slate-800">
                                    {{ $ord->total == 0 && $ord->isPackageOrder() ? 'Package' : 'Rs.'.number_format($ord->total, 2) }}
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                        {{ in_array($ord->status, ['delivered','completed']) ? 'bg-emerald-100 text-emerald-700' :
                                           ($ord->status === 'received' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                        {{ ucfirst(str_replace('_',' ',$ord->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-emerald-200 bg-emerald-50">
                                <td colspan="2" class="px-5 py-2.5 text-[11px] font-black text-emerald-700 uppercase tracking-wide">Month Total</td>
                                <td class="px-5 py-2.5 text-center font-black text-emerald-700">{{ number_format($month['total_qty'], 0) }}</td>
                                <td class="px-5 py-2.5 text-right font-black text-emerald-700">Rs.{{ number_format($month['total_bill'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="p-4 border-t border-gray-100">
        {{ $salesOrders->links() }}
    </div>
    @endif
</div>

