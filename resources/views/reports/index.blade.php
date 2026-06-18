<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 class="ph-title">Business Reports</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Analyze sales, revenue, stock deductions and payroll</p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-4 rounded-md border border-gray-100 shadow-sm mb-6 bg-gray-50">
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
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
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Done Payments (Collected)</h4>
            <p class="text-2xl font-bold text-green-600">Rs.{{ number_format($totalCollected, 2) }}</p>
            <div class="mt-2 text-[11px] font-bold text-gray-500 flex justify-between">
                <span>💵 Cash: Rs.{{ number_format($collectedCash, 2) }}</span>
                <span>💳 Card: Rs.{{ number_format($collectedCard, 2) }}</span>
            </div>
        </div>
        <div class="bg-white p-5 rounded-md border border-gray-100 shadow-sm">
            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-1">Pending Payments</h4>
            <p class="text-2xl font-bold text-red-500">Rs.{{ number_format($totalPending + $totalCredit, 2) }}</p>
        </div>
    </div>

    <!-- Reports Content Wrapper -->
    <div x-data="reportData()" class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden">
        
        <!-- Premium Horizontally Scrollable Tab Header -->
        <div class="p-3 border-b border-gray-100 bg-gray-50/50 overflow-x-auto scrollbar-none">
            <div class="flex gap-2 w-max bg-gray-200/50 p-1 rounded-xl">
                <button @click="activeReport = 'services'" 
                    :class="activeReport === 'services' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Service Breakdown
                </button>
                <button @click="activeReport = 'sales'" 
                    :class="activeReport === 'sales' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Sales Log
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
                <button @click="activeReport = 'credit'" 
                    :class="activeReport === 'credit' ? 'bg-white text-blue-700 shadow-sm ring-1 ring-gray-200/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'" 
                    class="px-5 py-2 text-sm font-bold rounded-lg transition-all duration-200">
                    Credit Customers
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
                                <th class="px-4 py-2 text-center">Total Count</th>
                                <th class="px-4 py-2 text-right">Full Total</th>
                                <th class="px-4 py-2 text-right">Done Payment</th>
                                <th class="px-4 py-2 text-right">Pending Payment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($services as $serv)
                                <tr>
                                    <td class="px-4 py-3 font-semibold">{{ $serv->name }}</td>
                                    <td class="px-4 py-3 text-center">{{ number_format($serv->total_qty, 1) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-blue-600">Rs.{{ number_format($serv->total_revenue, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">Rs.{{ number_format($serv->done_payment, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-red-500">
                                        <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px;">
                                            <span>Rs.{{ number_format($serv->pending_payment + $serv->credit_payment, 2) }}</span>
                                            @if($serv->pending_payment > 0 || $serv->credit_payment > 0)
                                                <button @click="openPendingOrders('{{ addslashes($serv->name) }}')" class="px-3 py-1 text-[11px] uppercase tracking-wider bg-blue-50 text-blue-600 rounded-md border border-blue-200 hover:bg-blue-100 hover:text-blue-700 font-bold transition-all shadow-sm">View</button>
                                            @else
                                                <button class="px-3 py-1 text-[11px] uppercase tracking-wider bg-gray-50 text-gray-400 rounded-md border border-gray-200 cursor-not-allowed font-bold" disabled>View</button>
                                            @endif
                                        </div>
                                    </td>
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

            <!-- CREDIT CUSTOMERS LOG -->
            <div x-show="activeReport === 'credit'" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-2">Order No</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2">Date Issued</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2 text-right">Balance Due</th>
                                <th class="px-4 py-2 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($creditOrders as $order)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-blue-600">
                                        <a href="{{ route('orders.show', $order->id) }}" class="hover:underline">{{ $order->order_number }}</a>
                                    </td>
                                    <td class="px-4 py-3 font-semibold">{{ $order->customer->name }} <br><span class="text-xs text-gray-500">{{ $order->customer->phone }}</span></td>
                                    <td class="px-4 py-3 text-xs text-gray-500">{{ $order->created_at->format('Y-m-d h:i A') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">Rs.{{ number_format($order->total, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-orange-600">Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <form action="{{ route('orders.collectCreditPayment', $order->id) }}" method="POST" class="flex gap-2 items-center justify-center">
                                            @csrf
                                            <input type="number" name="amount" value="{{ $order->total - $order->paid_amount }}" step="0.01" max="{{ $order->total - $order->paid_amount }}" class="w-24 px-2 py-1 border border-gray-300 rounded text-xs" required>
                                            <select name="payment_method" class="px-2 py-1 border border-gray-300 rounded text-xs">
                                                <option value="cash">Cash</option>
                                                <option value="card">Card</option>
                                            </select>
                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-xs font-bold hover:bg-green-700">Pay</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">No active credit customers.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Orders Modal -->
        <div x-cloak x-show="pendingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:500px;overflow:hidden;display:flex;flex-direction:column;max-height:85vh;" @click.away="pendingModalOpen = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fffbeb;">
                    <div>
                        <h3 style="font-size:16px;font-weight:900;color:#d97706;margin:0;">Pending Orders</h3>
                        <p style="font-size:12px;color:#b45309;margin:2px 0 0;font-weight:600;" x-text="'Service: ' + selectedServiceName"></p>
                    </div>
                    <button @click="pendingModalOpen = false" style="background:none;border:none;cursor:pointer;color:#d97706;font-size:20px;font-weight:bold;">✕</button>
                </div>
                <div style="padding:0;overflow-y:auto;background:#f8fafc;flex:1;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;">
                        <thead style="background:#f1f5f9;position:sticky;top:0;">
                            <tr>
                                <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Order</th>
                                <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">Customer</th>
                                <th style="padding:12px 16px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;text-align:right;">Pending Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="order in selectedServiceOrders" :key="order.id">
                                <tr style="border-bottom:1px solid #e2e8f0;background:white;transition:background 0.1s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding:12px 16px;">
                                        <div class="flex items-center gap-2">
                                            <a :href="'/orders/' + order.id" target="_blank" style="font-weight:800;color:#2563eb;text-decoration:none;" x-text="order.order_number"></a>
                                            <span x-show="order.payment_method === 'credit'" class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded">CREDIT</span>
                                            <span x-show="order.payment_method === 'pay_on_pickup'" class="px-1.5 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded">PAY ON PICKUP</span>
                                        </div>
                                        <div style="font-size:11px;color:#94a3b8;font-weight:500;margin-top:2px;" x-text="new Date(order.created_at).toLocaleDateString()"></div>
                                    </td>
                                    <td style="padding:12px 16px;font-weight:600;color:#334155;">
                                        <span x-text="order.customer ? order.customer.name : 'Walk-in'"></span>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;" x-text="order.customer ? order.customer.phone : ''"></div>
                                    </td>
                                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#dc2626;" x-text="'Rs.' + (parseFloat(order.total) - parseFloat(order.paid_amount)).toFixed(2)">
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="selectedServiceOrders.length === 0">
                                <td colspan="3" style="padding:30px;text-align:center;color:#94a3b8;font-weight:500;font-size:13px;">No pending orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="padding:16px 24px;border-top:1px solid #f1f5f9;background:white;text-align:right;">
                    <button @click="pendingModalOpen = false" style="padding:10px 20px;background:#f1f5f9;color:#475569;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;">Close</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function reportData() {
            return {
                activeReport: 'services',
                orders: @json($orders),
                pendingModalOpen: false,
                selectedServiceName: '',
                selectedServiceOrders: [],

                openPendingOrders(serviceName) {
                    this.selectedServiceName = serviceName;
                    this.selectedServiceOrders = this.orders.filter(order => {
                        const total = parseFloat(order.total) || 0;
                        const paid = parseFloat(order.paid_amount) || 0;
                        if (total <= paid) return false;

                        if (serviceName === '__all__') return true;

                        if (order.items && order.items.length > 0) {
                            return order.items.some(item => {
                                const svc = item.service_type || item.serviceType || item.service_type_id;
                                if (!svc) return true;
                                const name = typeof svc === 'object' ? (svc.name || '') : '';
                                return name === serviceName || serviceName === '';
                            });
                        }
                        return true;
                    });
                    this.pendingModalOpen = true;
                }
            }
        }
    </script>
</x-app-layout>
