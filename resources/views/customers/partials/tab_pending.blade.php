<!-- Pending Clearance Tab -->
<div>
    @if($pendingOrdersList->isEmpty())
        <div class="p-8 text-center text-gray-400 text-sm font-semibold">No pending clearance for this customer.</div>
    @else
    <table class="w-full text-sm text-left">
        <thead class="text-xs text-red-500 uppercase border-b border-gray-100 bg-red-50">
            <tr>
                <th class="px-5 py-3">Order #</th>
                <th class="px-5 py-3">Bill Value</th>
                <th class="px-5 py-3">Amount Paid</th>
                <th class="px-5 py-3 text-right">Balance Due</th>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($pendingOrdersList as $order)
            <tr class="hover:bg-red-50/30 transition-colors">
                <td class="px-5 py-4 font-mono font-bold text-blue-600 text-xs">{{ $order->order_number }}</td>
                <td class="px-5 py-4 font-semibold text-gray-700">Rs.{{ number_format($order->total, 2) }}</td>
                <td class="px-5 py-4 font-semibold text-emerald-600">Rs.{{ number_format($order->paid_amount, 2) }}</td>
                <td class="px-5 py-4 font-black text-red-600 text-right text-base">Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</td>
                <td class="px-5 py-4 text-gray-500 text-xs font-semibold">{{ $order->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-4 text-center">
                    <button @click="openCollectModal({{ $order->id }}, '{{ $order->order_number }}', {{ $order->total - $order->paid_amount }})" class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 font-bold text-xs rounded-lg transition-colors">Settle Now</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-100">
        {{ $pendingOrdersList->links() }}
    </div>
    @endif
</div>

