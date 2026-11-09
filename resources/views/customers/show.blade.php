<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-xs text-slate-400 mb-1"><a href="{{ route('customers.index') }}" class="hover:text-blue-600 font-semibold transition-colors">← Back to Customers</a></p>
                <h2 class="text-xl font-black text-slate-800">Customer Profile</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-white border-2 border-slate-200 text-slate-700 text-xs font-bold rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                    ✏️ Edit Profile
                </a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 p-3 mb-4 text-sm font-bold border border-green-100 rounded-xl flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="bg-red-50 text-red-700 p-3 mb-4 text-sm font-bold border border-red-100 rounded-xl">
            @if(session('error'))
                <div class="flex items-center gap-2"><i class="fa-solid fa-circle-exclamation text-red-500"></i> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <ul class="list-disc list-inside mt-1 font-semibold text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

        <!-- Alpine App Wrapper -->
    <div x-data="{ 
        collectModalOpen: false,
        collectOrderId: null,
        collectOrderNumber: '',
        collectAmount: 0,
        
        // Bulk Settlement State
        bulkCollectModalOpen: false,
        bulkCollectAmount: 0,
        pendingOrdersList: @js($allPendingForBulk),
        
        openMonth: null,
        toggleMonth(ym) { this.openMonth = this.openMonth === ym ? null : ym; },
        openCollectModal(id, number, amount) {
            this.collectOrderId = id;
            this.collectOrderNumber = number;
            this.collectAmount = amount;
            this.collectModalOpen = true;
        },
        openBulkCollectModal(totalAmount) {
            this.bulkCollectAmount = totalAmount;
            this.bulkCollectModalOpen = true;
        },
        get bulkSettlementPreview() {
            let remaining = parseFloat(this.bulkCollectAmount) || 0;
            let preview = [];
            
            for (let order of this.pendingOrdersList) {
                if (remaining <= 0) break;
                
                let payForOrder = Math.min(remaining, order.due);
                preview.push({
                    number: order.number,
                    due: order.due,
                    paid: payForOrder,
                    status: (payForOrder >= order.due) ? 'Fully Settled' : 'Partially Settled'
                });
                
                remaining -= payForOrder;
            }
            return preview;
        }
    }">

        <!-- Profile Card (Large) -->
    <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm p-6 mb-4 flex flex-col md:flex-row items-start md:items-center gap-5 relative overflow-hidden">
        <!-- Background decorative blobs -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>
        
        <div class="w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-4xl md:text-5xl font-black shadow-lg shadow-blue-500/30 shrink-0 z-10 relative">
            {{ substr($customer->name, 0, 1) }}
        </div>
        
        <div class="flex-1 z-10 relative">
            <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight">
                    {{ $customer->title ? $customer->title . ' ' : '' }}{{ $customer->name }}
                </h2>
                @if($customer->customer_mark)
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 font-bold text-xs md:text-sm rounded-lg border border-amber-200 uppercase tracking-widest shadow-sm">
                        {{ $customer->customer_mark }}
                    </span>
                @endif
                @if($customer->isCorporate())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 font-extrabold text-xs md:text-sm rounded-lg border border-indigo-200 uppercase tracking-wider shadow-sm">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Corporate Client
                    </span>
                @elseif($customer->isOnline())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs md:text-sm rounded-lg border border-emerald-200 uppercase tracking-wider shadow-sm">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Online Client
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-600 font-bold text-xs rounded-lg border border-slate-200 uppercase tracking-wider">
                        Walk-in Client
                    </span>
                @endif
            </div>
            
            <div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-6 text-sm md:text-base font-semibold text-slate-600">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                        <i class="fa-solid fa-phone text-sm"></i>
                    </div> 
                    <span class="text-slate-700 tracking-wide">{{ $customer->phone }}</span>
                </div>
                
                @if($customer->address)
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shadow-sm">
                        <i class="fa-solid fa-location-dot text-sm"></i>
                    </div> 
                    <span class="text-slate-600">{{ $customer->address }}</span>
                </div>
                @endif
            </div>
            
            @if($customer->date_of_birth || $customer->nic)
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-4 pt-4 border-t border-slate-100">
                @if($customer->date_of_birth)
                <span class="flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-bold border border-rose-100"><i class="fa-solid fa-cake-candles"></i> {{ \Carbon\Carbon::parse($customer->date_of_birth)->format('M d, Y') }}</span>
                @endif
                @if($customer->nic)
                <span class="flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-600 rounded-lg text-xs font-bold border border-purple-100"><i class="fa-solid fa-id-card"></i> NIC: {{ $customer->nic }}</span>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Compact Stats & Pending Row -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        
        <!-- Stats -->
        <div class="flex items-center gap-6 text-center pl-2">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Orders</p>
                <p class="text-xl md:text-2xl font-black text-slate-700 leading-none">{{ $totalOrders }}</p>
            </div>
            <div class="w-px h-10 bg-slate-200"></div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Total Spend</p>
                <p class="text-xl md:text-2xl font-black text-emerald-600 leading-none">Rs.{{ number_format($totalSpend, 0) }}</p>
            </div>
        </div>

        <!-- Pending Alert & Button -->
        @if($pendingCount > 0)
        <div class="flex flex-wrap items-center justify-between lg:justify-end gap-4 bg-red-50 border border-red-100 rounded-xl px-5 py-3 ml-auto w-full lg:w-auto">
            <div class="text-left lg:text-right flex-1 lg:flex-none">
                <p class="text-[10px] md:text-xs font-bold text-red-500 uppercase tracking-wider mb-0.5">Pending Due ({{ $pendingCount }})</p>
                <p class="text-lg md:text-xl font-black text-red-700 leading-none">Rs.{{ number_format($totalPending, 2) }}</p>
            </div>
            <button @click="openBulkCollectModal({{ $totalPending }})" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-black text-xs md:text-sm rounded-lg transition-all shadow-md shadow-red-500/20 flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-coins"></i> Settle
            </button>
        </div>
        @endif
    </div>    <!-- Tabs Container -->
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-5 pt-3 border-b border-gray-100 bg-gray-50 flex flex-wrap justify-between items-center gap-4">
            <div class="flex gap-4">
                <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => 'history']) }}" class="{{ $tab === 'history' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-gray-500 font-semibold hover:text-gray-700' }} pb-3 px-2 text-sm transition-colors outline-none block">
                    Order History
                </a>
                <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => 'pending']) }}" class="{{ $tab === 'pending' ? 'border-b-2 border-red-600 text-red-600 font-bold' : 'text-gray-500 font-semibold hover:text-gray-700' }} pb-3 px-2 text-sm transition-colors outline-none flex items-center gap-2">
                    Pending Clearance
                    @if($pendingCount > 0)
                        <span class="bg-red-100 text-red-700 py-0.5 px-2 rounded-full text-[10px]">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => 'sales']) }}" class="{{ $tab === 'sales' ? 'border-b-2 border-emerald-600 text-emerald-600 font-bold' : 'text-gray-500 font-semibold hover:text-gray-700' }} pb-3 px-2 text-sm transition-colors outline-none block">
                    Sales Report
                </a>
            </div>

            @if($tab === 'sales')
            <form method="GET" action="{{ route('customers.show', $customer) }}" class="flex flex-wrap items-center gap-2 pb-2">
                <input type="hidden" name="tab" value="sales">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-2 py-1 text-xs font-semibold text-slate-700 border-2 border-slate-200 rounded-lg focus:ring-0 focus:border-emerald-500 bg-white">
                <span class="text-xs font-bold text-slate-400">to</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-2 py-1 text-xs font-semibold text-slate-700 border-2 border-slate-200 rounded-lg focus:ring-0 focus:border-emerald-500 bg-white">
                
                <div class="flex items-center gap-1.5 ml-1">
                    <button type="submit" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-bold rounded-lg transition-colors shadow-sm">
                        Filter
                    </button>
                    @if(request('start_date') || request('end_date'))
                    <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => 'sales']) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-500 text-xs font-bold rounded-lg transition-colors shadow-sm" title="Clear Filters">
                        Clear
                    </a>
                    @endif
                    <a href="{{ route('customers.salesReport.pdf', ['customer' => $customer, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'month' => request('month')]) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5 ml-2">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </a>
                </div>
            </form>
            @endif
        </div>

        @if($tab === 'history')
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
        @endif

        @if($tab === 'pending')
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
        @endif

        @if($tab === 'sales')
        <!-- Sales Report Tab -->
        <div>
            @if($monthlySales->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">No orders found for this customer.</div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($monthlySales as $month)
                <div>
                    <!-- Month Header Row (clickable) -->
                    <div @click="toggleMonth('{{ $month['year_month'] }}')" class="flex items-center justify-between px-5 py-3 cursor-pointer hover:bg-emerald-50/40 transition-colors select-none">
                        <!-- Left: month name + stats -->
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs shrink-0">📅</div>
                            <div>
                                <div class="text-xs font-semibold text-slate-700">{{ $month['label'] }}</div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                    <span>{{ $month['order_count'] }} order{{ $month['order_count'] !== 1 ? 's' : '' }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span>{{ number_format($month['total_qty'], 0) }} items</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-emerald-600">Paid: Rs.{{ number_format($month['orders']->sum('paid_amount'), 2) }}</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-slate-700">Bill: Rs.{{ number_format($month['total_bill'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Right: PDF + arrow -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('customers.salesReport.pdf', [$customer, 'month' => $month['year_month']]) }}"
                               target="_blank"
                               @click.stop
                               class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors"
                               title="Download PDF">
                               PDF
                            </a>
                            <svg :class="openMonth === '{{ $month['year_month'] }}' ? 'rotate-180' : ''"
                                 class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Expanded Orders -->
                    <div x-show="openMonth === '{{ $month['year_month'] }}'" x-transition style="display:none;">
                        <div class="bg-slate-50 border-t border-slate-100">
                            <table class="w-full text-xs">
                                <thead>
                                    <tr class="text-[10px] font-black text-slate-400 uppercase tracking-wider border-b border-slate-200">
                                        <th class="px-5 py-2 text-left">Order #</th>
                                        <th class="px-5 py-2 text-left">Date</th>
                                        <th class="px-5 py-2 text-center">Total Qty</th>
                                        <th class="px-5 py-2 text-right">Bill</th>
                                        <th class="px-5 py-2 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($month['orders'] as $ord)
                                    <tr class="hover:bg-white transition-colors">
                                        <td class="px-5 py-2.5">
                                            <a href="{{ route('orders.show', $ord->order_number) }}" class="font-mono font-black text-blue-600 hover:underline">{{ $ord->order_number }}</a>
                                        </td>
                                        <td class="px-5 py-2.5 text-slate-500 font-semibold">{{ $ord->created_at->format('d M, H:i') }}</td>
                                        <td class="px-5 py-2.5 text-center font-bold text-slate-700">
                                            {{ number_format($ord->items->sum('quantity'), 0) }}
                                        </td>
                                        <td class="px-5 py-2.5 text-right font-black text-slate-800">
                                            {{ $ord->total == 0 && $ord->isPackageOrder() ? 'Package' : 'Rs.'.number_format($ord->total, 2) }}
                                        </td>
                                        <td class="px-5 py-2.5 text-right">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold
                                                {{ in_array($ord->status, ['delivered','completed']) ? 'bg-emerald-100 text-emerald-700' :
                                                   ($ord->status === 'received' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                                {{ ucfirst(str_replace('_',' ',$ord->status)) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!-- Month totals footer -->
                                <tfoot>
                                    <tr class="border-t-2 border-emerald-200 bg-emerald-50">
                                        <td colspan="2" class="px-5 py-2.5 text-[11px] font-black text-emerald-700 uppercase tracking-wide">Month Total</td>
                                        <td class="px-5 py-2.5 text-center font-black text-emerald-700">{{ number_format($month['total_qty'], 0) }}</td>
                                        <td class="px-5 py-2.5 text-right font-black text-emerald-700">Rs.{{ number_format($month['total_bill'], 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-gray-100">
                {{ $salesOrders->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Collect Credit Payment Modal (Single Order) -->
    <div x-cloak x-show="collectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.away="collectModalOpen = false">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-red-50">
                <div>
                    <h3 class="text-sm md:text-base font-black text-red-600">Collect Credit Payment</h3>
                    <p class="text-[10px] md:text-sm font-bold text-red-700 mt-1" x-text="'Order: ' + collectOrderNumber"></p>
                </div>
                <button @click="collectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors font-bold">×</button>
            </div>
            <div class="p-5 bg-white">
                <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount to Collect (Rs)</label>
                        <input type="number" step="0.01" name="amount" x-model="collectAmount" max="" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                    </div>
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                        <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="bank">🏦 Bank Transfer</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl shadow-lg shadow-red-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                        Confirm Collection
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bulk Collect Credit Payment Modal (All Orders) -->
    <div x-cloak x-show="bulkCollectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col" @click.away="bulkCollectModalOpen = false">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-indigo-50">
                <div>
                    <h3 class="text-sm md:text-base font-black text-indigo-600">Settle Customer Payments</h3>
                    <p class="text-[10px] md:text-sm font-bold text-indigo-700 mt-1">Payments will be applied to oldest orders first.</p>
                </div>
                <button @click="bulkCollectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors font-bold">×</button>
            </div>
            
            <div class="p-5 bg-white max-h-[70vh] overflow-y-auto">
                <form action="{{ route('customers.bulkCreditPayment', $customer) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount (Rs)</label>
                            <input type="number" step="0.01" name="amount" x-model="bulkCollectAmount" required min="1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-lg font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                            <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0 h-[46px]">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                                <option value="bank">🏦 Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Real-time Preview Table -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-2">Settlement Preview</label>
                        
                        <div x-show="bulkSettlementPreview.length === 0" class="p-4 bg-slate-50 rounded-xl text-center text-xs font-semibold text-slate-500">
                            Enter an amount to see how it will be distributed.
                        </div>
                        
                        <div x-show="bulkSettlementPreview.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-3 py-2 font-black text-slate-600">Order #</th>
                                        <th class="px-3 py-2 font-black text-slate-600 text-right">Due</th>
                                        <th class="px-3 py-2 font-black text-emerald-600 text-right">Applied</th>
                                        <th class="px-3 py-2 font-black text-slate-600 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="item in bulkSettlementPreview" :key="item.number">
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-3 py-2 font-mono font-bold text-blue-600" x-text="item.number"></td>
                                            <td class="px-3 py-2 font-semibold text-right text-slate-600" x-text="'Rs.' + Number(item.due).toFixed(2)"></td>
                                            <td class="px-3 py-2 font-black text-right text-emerald-600" x-text="'Rs.' + Number(item.paid).toFixed(2)"></td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                                                      :class="item.status === 'Fully Settled' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                                      x-text="item.status"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <button type="submit" :disabled="bulkCollectAmount <= 0" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider disabled:opacity-50 disabled:cursor-not-allowed">
                        Confirm Bulk Collection
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    </div> <!-- Close Alpine Wrapper -->
</x-app-layout>



