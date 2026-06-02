<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 style="font-size:24px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 4px;">Business Reports</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Analyze sales, revenue, stock deductions and payroll</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-md border border-gray-100 shadow-sm mb-6 flex flex-wrap items-end gap-4 bg-gray-50">
        <form action="{{ route('reports.index') }}" method="GET" class="w-full flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="text-sm border-gray-200 rounded-md focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors">
                Apply Filter
            </button>
            <a href="{{ route('reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank"
               class="px-4 py-2 bg-gray-700 text-white text-xs font-semibold rounded hover:bg-gray-800 transition-colors">
                🖨 Export / Print PDF
            </a>
            <a href="{{ route('reports.closeShift') }}" target="_blank"
               class="px-4 py-2 bg-amber-500 text-white text-xs font-semibold rounded hover:bg-amber-600 transition-colors">
                📋 Close Shift Report
            </a>
        </form>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-md border border-gray-100 shadow-sm">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Sales Revenue</h4>
            <p class="text-2xl font-bold text-gray-900">Rs.{{ number_format($totalSales, 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-md border border-gray-100 shadow-sm">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Salary Expenses</h4>
            <p class="text-2xl font-bold text-red-600">Rs.{{ number_format($totalSalaries, 2) }}</p>
        </div>
        <div class="bg-white p-5 rounded-md border border-gray-100 shadow-sm">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Net Flow</h4>
            @php $net = $totalSales - $totalSalaries; @endphp
            <p class="text-2xl font-bold {{ $net >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Rs.{{ number_format($net, 2) }}
            </p>
        </div>
    </div>

    <!-- Reports Content Wrapper -->
    <div x-data="{ activeReport: 'sales' }" class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
        
        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-3 border-b border-gray-100 bg-gray-50/50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-gray-200/50 p-1 rounded-xl">
                <button @click="activeReport = 'sales'" 
                    :class="activeReport === 'sales' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Sales Log
                </button>
                <button @click="activeReport = 'services'" 
                    :class="activeReport === 'services' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Service Breakdown
                </button>
                <button @click="activeReport = 'salaries'" 
                    :class="activeReport === 'salaries' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Salaries Log
                </button>
                <button @click="activeReport = 'inventory'" 
                    :class="activeReport === 'inventory' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Inventory Usage Log
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- SALES LOG -->
            <div x-show="activeReport === 'sales'">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2">Order No</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-blue-600">
                                        <a href="{{ route('orders.show', $order->id) }}" class="hover:underline">{{ $order->order_number }}</a>
                                    </td>
                                    <td class="px-4 py-3">{{ $order->customer->name }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $order->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rs.{{ number_format($order->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">No sales record in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SERVICE BREAKDOWN -->
            <div x-show="activeReport === 'services'" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2">Service Name</th>
                                <th class="px-4 py-2 text-center">Total Volume Performed</th>
                                <th class="px-4 py-2 text-right">Revenue Generated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($services as $serv)
                                <tr>
                                    <td class="px-4 py-3 font-semibold">{{ $serv->name }}</td>
                                    <td class="px-4 py-3 text-center">{{ number_format($serv->total_qty, 1) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">Rs.{{ number_format($serv->total_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">No service usage tracked in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SALARIES LOG -->
            <div x-show="activeReport === 'salaries'" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Payment Details</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($salaries as $sal)
                                <tr>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $sal->expense_date }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-700">{{ $sal->notes }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-red-600">Rs.{{ number_format($sal->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">No payroll expenses logged in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INVENTORY USAGE LOG -->
            <div x-show="activeReport === 'inventory'" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Supply Item</th>
                                <th class="px-4 py-2">Type</th>
                                <th class="px-4 py-2 text-center">Qty Changes</th>
                                <th class="px-4 py-2">Reason / Authorized By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($inventoryLogs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $log->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ $log->inventoryItem->name ?? 'Deleted Item' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $log->type === 'stock_in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ strtoupper(str_replace('_', ' ', $log->type)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold {{ $log->type === 'stock_in' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $log->type === 'stock_in' ? '+' : '-' }}{{ $log->quantity }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $log->notes ?? '-' }} / <span class="font-medium text-gray-700">{{ $log->createdBy->name ?? 'System' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No inventory transactions tracked in this range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
