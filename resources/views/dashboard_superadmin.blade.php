<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Global Headquarters Dashboard</h2>
                <p class="text-sm font-medium text-gray-500 mt-1">Today's live metrics across all branches</p>
            </div>
        </div>
    </x-slot>

    <!-- Top Metrics Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <!-- Revenue Today -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-200/50 group hover:shadow-xl hover:shadow-emerald-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Revenue</p>
                <h3 class="text-xl font-black text-white leading-tight">Rs.{{ number_format($superAdminStats['total_revenue_today']) }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Today</p>
            </div>
        </div>

        <!-- New Orders Today -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg shadow-blue-200/50 group hover:shadow-xl hover:shadow-blue-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Orders</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['new_orders_today'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Today</p>
            </div>
        </div>

        <!-- Present Staff -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-orange-500 to-amber-500 shadow-lg shadow-orange-200/50 group hover:shadow-xl hover:shadow-orange-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Staff</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['today_present_staff'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Present Today</p>
            </div>
        </div>

        <!-- Opening Branches -->
        <div class="relative overflow-hidden rounded-2xl p-4 bg-gradient-to-br from-purple-500 to-fuchsia-600 shadow-lg shadow-purple-200/50 group hover:shadow-xl hover:shadow-purple-200/70 transition-all duration-300">
            <div class="absolute -right-3 -top-3 w-16 h-16 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            <div class="absolute -right-1 -bottom-4 w-24 h-24 bg-white/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <p class="text-[10px] font-bold text-white/70 uppercase tracking-widest mb-1">Branches</p>
                <h3 class="text-xl font-black text-white leading-tight">{{ $superAdminStats['opening_branches'] }}</h3>
                <p class="text-[10px] text-white/60 font-semibold mt-1">Open Now</p>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Sales by Branch Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Today's Sales by Branch</h3>
            <div class="relative h-64 w-full">
                <canvas id="branchSalesChart"></canvas>
            </div>
        </div>

        <!-- Orders by Status Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4">Today's Orders by Status</h3>
            <div class="relative h-64 w-full flex justify-center">
                <canvas id="orderStatusChart"></canvas>
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
                        <th class="px-6 py-4">Order ID</th>
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
                            <td class="px-6 py-4 font-bold text-gray-900 text-sm">#{{ $order->id }}</td>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Chart Defaults for Modern Aesthetic
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = "#94a3b8";
            
            // --- Sales By Branch Chart ---
            const branchSalesCtx = document.getElementById('branchSalesChart').getContext('2d');
            const branchSalesData = {!! json_encode($chartBranchSales) !!};
            
            new Chart(branchSalesCtx, {
                type: 'bar',
                data: {
                    labels: branchSalesData.labels,
                    datasets: [{
                        label: 'Sales Revenue (Rs)',
                        data: branchSalesData.data,
                        backgroundColor: '#10b981', // Emerald 500
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 13 },
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Rs. ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            ticks: {
                                callback: function(value) {
                                    if(value >= 1000) return (value/1000) + 'k';
                                    return value;
                                }
                            }
                        },
                        x: {
                            border: { display: false },
                            grid: { display: false }
                        }
                    }
                }
            });

            // --- Orders By Status Chart ---
            const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
            const orderStatusData = {!! json_encode($chartOrderStatus) !!};
            
            new Chart(orderStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: orderStatusData.labels,
                    datasets: [{
                        data: orderStatusData.data,
                        backgroundColor: [
                            '#f59e0b', // Amber (Received)
                            '#3b82f6', // Blue (Processing)
                            '#0ea5e9', // Sky (Washing)
                            '#6366f1', // Indigo (Ironing)
                            '#10b981', // Emerald (Ready)
                            '#64748b'  // Slate (Delivered)
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 12, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 13 },
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
