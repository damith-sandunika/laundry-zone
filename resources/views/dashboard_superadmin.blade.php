<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Headquarters Dashboard</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Today's live metrics across all branches</p>
            </div>
        </div>
    </x-slot>

    @include('superadmin.partials.dashboard_top_metrics')

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales by Branch Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Today's Sales by Branch</h3>
            <div class="relative h-64 w-full">
                <canvas id="branchSalesChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Branch Cash Balances --}}
        <div class="bg-white/80 backdrop-blur-xl border border-slate-200/60 rounded-3xl p-6 shadow-sm flex flex-col h-[350px]">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-black text-slate-900 tracking-tight">Branch Cash Balances</h3>
                    <p class="text-[12px] font-semibold text-slate-500">Current cash on hand</p>
                </div>
            </div>
            <div class="relative flex-1">
                <canvas id="cashBalanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Global Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-800">Recent Global Activity</h3>
            <span class="text-xs font-semibold text-gray-500">Live feed across all branches</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="text-xs text-gray-500 uppercase font-extrabold tracking-wider bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Order Number</th>
                        <th class="px-6 py-4">Branch</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($recentGlobalOrders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 text-sm">#{{ $order->order_number }}</td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $order->branch->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-700">{{ $order->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-6 py-4 text-xs font-medium text-gray-500">{{ $order->created_at->format('h:i A') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($order->status === 'received')
                                    <span class="px-2 py-1 text-[10px] font-black rounded-lg bg-yellow-100 text-yellow-700 uppercase tracking-wider">Received</span>
                                @elseif($order->status === 'washing' || $order->status === 'ironing')
                                    <span class="px-2 py-1 text-[10px] font-black rounded-lg bg-blue-100 text-blue-700 uppercase tracking-wider">Processing</span>
                                @elseif($order->status === 'done')
                                    <span class="px-2 py-1 text-[10px] font-black rounded-lg bg-green-100 text-green-700 uppercase tracking-wider">Ready</span>
                                @elseif($order->status === 'delivered')
                                    <span class="px-2 py-1 text-[10px] font-black rounded-lg bg-gray-100 text-gray-600 uppercase tracking-wider">Delivered</span>
                                @else
                                    <span class="px-2 py-1 text-[10px] font-black rounded-lg bg-gray-100 text-gray-600 uppercase tracking-wider">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-black text-gray-900 text-sm">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 font-medium">No recent orders found globally today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        @include('superadmin.partials.dashboard_charts_js')
    @endpush
</x-app-layout>
