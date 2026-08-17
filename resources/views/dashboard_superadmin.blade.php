<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Overview of all branches and system activity</p>
            </div>
        </div>
    </x-slot>

    <!-- Global Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Branches -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Branches</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $superAdminStats['total_branches'] }}</h3>
                    <p class="text-xs font-semibold mt-2 {{ $superAdminStats['active_branches'] === $superAdminStats['total_branches'] ? 'text-green-600' : 'text-orange-500' }}">
                        {{ $superAdminStats['active_branches'] }} Active
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Staff</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $superAdminStats['total_staff'] }}</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-2">Across all branches</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Global Customers</p>
                    <h3 class="text-3xl font-black text-gray-800">{{ $superAdminStats['total_customers'] }}</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-2">Registered globally</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-md transition-all duration-300">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Global Revenue</p>
                    <h3 class="text-2xl font-black text-gray-800">Rs.{{ number_format($superAdminStats['global_revenue']) }}</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-2">From {{ $superAdminStats['global_orders'] }} total orders</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Global Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-800">Recent Global Activity</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Order ID</th>
                        <th class="px-6 py-3 font-semibold">Branch</th>
                        <th class="px-6 py-3 font-semibold">Customer</th>
                        <th class="px-6 py-3 font-semibold">Date</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                        <th class="px-6 py-3 font-semibold text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentGlobalOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium text-gray-900">#{{ $order->id }}</td>
                            <td class="px-6 py-3 text-xs font-bold text-blue-600">{{ $order->branch->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $order->customer->name ?? 'Walk-in' }}</td>
                            <td class="px-6 py-3 text-xs text-gray-500">{{ $order->created_at->format('d M h:i A') }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($order->status === 'received')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-yellow-100 text-yellow-700 uppercase tracking-wider">Received</span>
                                @elseif($order->status === 'washing' || $order->status === 'ironing')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-blue-100 text-blue-700 uppercase tracking-wider">Processing</span>
                                @elseif($order->status === 'done')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-green-100 text-green-700 uppercase tracking-wider">Ready</span>
                                @elseif($order->status === 'delivered')
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-gray-100 text-gray-600 uppercase tracking-wider">Delivered</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-gray-900">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 font-medium">No recent orders found globally.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
