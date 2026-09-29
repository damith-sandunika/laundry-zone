<div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
    <table class="w-full text-left border-collapse text-[9px] md:text-xs">
        <thead class="bg-blue-50 border-b border-blue-100">
            <tr>
                <th class="px-4 py-2 font-black text-blue-800 uppercase tracking-widest">Order No</th>
                <th class="px-4 py-2 font-black text-blue-800 uppercase tracking-widest">Date</th>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th class="px-4 py-2 font-black text-blue-800 uppercase tracking-widest">Branch</th>
                @endif
                <th class="px-4 py-2 font-black text-blue-800 uppercase tracking-widest text-right">Total</th>
                <th class="px-4 py-2 font-black text-blue-800 uppercase tracking-widest text-right">Paid</th>
                <th class="px-4 py-2 font-black text-red-700 uppercase tracking-widest text-right">Pending</th>
                <th class="px-4 py-2 text-center"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-2 font-black text-slate-800">
                        <a href="{{ route('orders.show', $order->order_number) }}" class="hover:text-blue-600 transition-colors">{{ $order->order_number }}</a>
                    </td>
                    <td class="px-4 py-2 text-slate-500 font-semibold">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td class="px-4 py-2 text-slate-600 font-bold">{{ $order->branch->name ?? 'N/A' }}</td>
                    @endif
                    <td class="px-4 py-2 text-right font-black text-slate-700">Rs.{{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-2 text-right font-bold text-emerald-600">Rs.{{ number_format($order->paid_amount, 2) }}</td>
                    <td class="px-4 py-2 text-right font-black text-red-600">Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('pos.index', ['order' => $order->id]) }}" class="inline-block px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 rounded font-bold transition-colors shadow-sm">Pay</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-4 text-center text-slate-400 font-semibold">No pending orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
