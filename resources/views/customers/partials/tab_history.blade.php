<!-- Order History Tab -->
<div>
    @if($historyOrders->isEmpty())
        <div class="p-8 text-center text-gray-400 text-sm">No orders yet.</div>
    @else
    <table class="w-full text-sm text-left">
        <thead class="text-xs text-gray-500 uppercase border-b border-gray-100 bg-gray-50">
            <tr>
                <th class="px-5 py-2">Order #</th>
                <th class="px-5 py-2">Services</th>
                <th class="px-5 py-2">Total</th>
                <th class="px-5 py-2">Paid</th>
                <th class="px-5 py-2">Status</th>
                <th class="px-5 py-2">Date</th>
                <th class="px-5 py-2"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($historyOrders as $order)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono font-bold text-blue-600 text-xs">{{ $order->order_number }}</td>
                <td class="px-5 py-3 text-gray-600 text-xs">
                    {{ $order->items->pluck('serviceType.name')->filter()->join(', ') ?: '—' }}
                </td>
                <td class="px-5 py-3 font-semibold">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</td>
                <td class="px-5 py-3 text-green-600">Rs.{{ number_format($order->paid_amount, 2) }}</td>
                <td class="px-5 py-3">
                    @php $colors = ['received'=>'blue','washing'=>'indigo','ready'=>'amber','delivered'=>'green','cancelled'=>'red']; $c = $colors[$order->status] ?? 'gray'; @endphp
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-{{ $c }}-100 text-{{ $c }}-700">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    <a href="{{ route('orders.show', $order->order_number) }}" class="text-xs text-blue-600 hover:underline">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-100">
        {{ $historyOrders->links() }}
    </div>
    @endif
</div>

