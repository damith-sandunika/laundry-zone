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

    @php
        $pendingOrders = $customer->orders->filter(function($o) {
            return ($o->total - $o->paid_amount) > 0;
        });
        $totalPending = $pendingOrders->sum(function($o) {
            return $o->total - $o->paid_amount;
        });
    @endphp

    @if($pendingOrders->count() > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-red-100 rounded-lg text-red-600">
                    <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                </div>
                <div>
                    <h3 class="text-red-800 font-bold text-lg">Pending Clearance (Credit Due)</h3>
                    <p class="text-red-600 text-sm mt-0.5">This customer has {{ $pendingOrders->count() }} unpaid order(s) requiring clearance.</p>
                </div>
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs text-red-500 font-bold uppercase tracking-wider mb-1">Total Due</p>
                <p class="text-2xl font-black text-red-700">Rs.{{ number_format($totalPending, 2) }}</p>
            </div>
        </div>
    @endif

    <!-- Tabs Container -->
    <div x-data="{ 
        activeTab: 'history',
        collectModalOpen: false,
        collectOrderId: null,
        collectOrderNumber: '',
        collectAmount: 0,
        openCollectModal(id, number, amount) {
            this.collectOrderId = id;
            this.collectOrderNumber = number;
            this.collectAmount = amount;
            this.collectModalOpen = true;
        }
    }">
    <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 pt-3 border-b border-gray-100 bg-gray-50 flex gap-4">
            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-gray-500 font-semibold hover:text-gray-700'" class="pb-3 px-2 text-sm transition-colors outline-none">
                Order History
            </button>
            <button @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'border-b-2 border-red-600 text-red-600 font-bold' : 'text-gray-500 font-semibold hover:text-gray-700'" class="pb-3 px-2 text-sm transition-colors outline-none flex items-center gap-2">
                Pending Clearance
                @if($pendingOrders->count() > 0)
                    <span class="bg-red-100 text-red-700 py-0.5 px-2 rounded-full text-[10px]">{{ $pendingOrders->count() }}</span>
                @endif
            </button>
        </div>

        <!-- Order History Tab -->
        <div x-show="activeTab === 'history'" x-transition>
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

        <!-- Pending Clearance Tab -->
        <div x-show="activeTab === 'pending'" x-transition style="display: none;">
            @if($pendingOrders->isEmpty())
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
                    @foreach($pendingOrders as $order)
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
            @endif
        </div>
    </div>

    <!-- Collect Credit Payment Modal -->
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
                            <option value="bank_transfer">🏦 Bank Transfer</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl shadow-lg shadow-red-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                        Confirm Collection
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    </div>
</x-app-layout>


