<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-gray-400 mb-1"><a href="{{ route('customers.index') }}" class="hover:text-blue-600">← Customers</a></p>
                <h2 class="text-xl font-bold text-gray-800">{{ $customer->name }}</h2>
                <p class="text-xs text-gray-500">{{ $customer->phone }} @if($customer->address) &bull; {{ $customer->address }}@endif</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-semibold rounded hover:bg-gray-50 transition-colors">
                    ✏️ Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-3 mb-4 text-sm border border-green-100 rounded-md">{{ session('success') }}</div>
    @endif

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $totalOrders }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Orders</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">Rs.{{ number_format($totalSpend, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Spend</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">
                {{ $customer->orders->where('status', 'delivered')->count() }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Completed</p>
        </div>
    </div>

    <!-- Order History -->
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-700">Order History</h3>
        </div>
        @if($customer->orders->isEmpty())
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
                @foreach($customer->orders as $order)
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
        @endif
    </div>
</x-app-layout>


