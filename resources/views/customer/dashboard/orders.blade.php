<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Order History</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">View all your past and current laundry orders.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs uppercase tracking-wider text-slate-500 font-bold">
                        <th class="p-4">Order ID</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Items</th>
                        <th class="p-4">Total</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 font-bold text-slate-800">{{ $order->order_number }}</td>
                            <td class="p-4 text-slate-600">{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="p-4 text-slate-600">{{ $order->items->count() }} Items</td>
                            <td class="p-4 font-bold text-slate-800">LKR {{ number_format($order->total_amount, 2) }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold inline-block
                                    @if($order->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif(in_array($order->status, ['processing', 'washing', 'drying', 'ironing'])) bg-blue-100 text-blue-700
                                    @elseif($order->status === 'ready') bg-green-100 text-green-700
                                    @elseif($order->status === 'delivered') bg-slate-100 text-slate-700
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-700
                                    @endif
                                ">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($order->payment_status === 'unpaid')
                                    <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-1 rounded-md">Unpaid</span>
                                @else
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-md flex items-center gap-1 w-max">
                                        <i class="fa-solid fa-check"></i> Paid
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-medium">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
