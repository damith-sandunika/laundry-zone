            <!-- CREDIT ORDERS LOG -->
            <div x-show="activeReport === 'credit_orders'" style="display:none;" x-transition>
                <div class="p-4 border-b border-slate-100 bg-white flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Pending Credit Orders</h3>
                    <span class="text-xs font-bold text-orange-600 bg-orange-50 px-3 py-1 rounded-full border border-orange-200">
                        Total Pending: Rs.{{ number_format($totalCredit, 2) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order Details</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Bill Value</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Balance Due</th>
                                <th class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($creditOrders as $order)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="/orders/view/{{ $order->order_number }}" target="_blank" class="font-bold text-blue-600 hover:underline">{{ $order->order_number }}</a>
                                            <span class="text-[9px] font-bold text-slate-400">#{{ $order->id }}</span>
                                        </div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y') }}</div>
                                        @if($order->branch)
                                            <div class="text-[9px] md:text-xs font-bold text-slate-400 mt-0.5">🏪 {{ $order->branch->name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4">
                                        <div class="font-bold text-slate-900">
                                            @if($order->customer_id)
                                                <a href="{{ route('customers.show', $order->customer_id) }}" class="text-blue-600 hover:underline">{{ $order->customer->name }}</a>
                                            @else
                                                Walk-in
                                            @endif
                                        </div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1">{{ $order->customer->phone ?? '' }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right">
                                        <div class="font-bold text-slate-600">Rs.{{ number_format($order->total, 2) }}</div>
                                        @if($order->paid_amount > 0)
                                            <div class="text-[9px] md:text-xs font-semibold text-emerald-600 mt-1">Paid: Rs.{{ number_format($order->paid_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-right">
                                        <div class="font-black text-rose-500 text-sm md:text-base">Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</div>
                                    </td>
                                    <td class="px-2 py-2 md:px-6 md:py-4 text-center">
                                        <button @click="openCollectModal({{ $order->id }}, '{{ $order->order_number }}', {{ $order->total - $order->paid_amount }})" class="px-3 py-1.5 md:px-4 md:py-2 bg-orange-500 hover:bg-orange-600 text-white text-[10px] md:text-xs font-bold rounded-lg shadow-sm transition-colors">
                                            Collect Pay
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">No pending credit orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-100 bg-slate-50">
                    {{ $creditOrders->appends(request()->query())->links() }}
                </div>
            </div>
