<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Credit Orders Report</h2>
                <p class="text-[10px] md:text-sm text-slate-500 font-medium mt-1">Pending payments grouped by customer
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('reports.credit-orders.pdf', request()->all()) }}" target="_blank"
                    class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-[10px] md:text-sm font-bold rounded-xl flex items-center gap-2 shadow-lg shadow-slate-800/20 transition-all">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Filters Section -->
    <div class="bg-white p-3 md:p-6 rounded-xl md:rounded-2xl border border-slate-200 shadow-sm mb-4 md:mb-6">
        <form action="{{ route('reports.credit-orders') }}" method="GET"
            class="flex flex-wrap gap-2 md:gap-4 items-end">
            @if($isSuperAdmin)
                <div class="w-full md:flex-1 md:min-w-[200px]">
                    <label
                        class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Branch</label>
                    <select name="branch_id"
                        class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
                        <option value="">🌍 All Branches (Company Wide)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                                🏪 {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex-1 md:min-w-[150px]">
                <label
                    class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Start
                    Date (Delivered)</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                    class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>

            <div class="flex-1 md:min-w-[150px]">
                <label
                    class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">End
                    Date (Delivered)</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                    class="w-full px-1 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors">
            </div>

            <div class="flex-1 md:min-w-[170px]">
                <label class="block text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-wider mb-1 md:mb-2">Customer Type</label>
                <select name="customer_type" class="w-full px-2 py-1.5 md:px-4 md:py-2.5 border-2 border-slate-200 rounded-lg md:rounded-xl text-[10px] md:text-sm font-semibold text-slate-900 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-0 transition-colors" onchange="this.form.submit()">
                    <option value="">All Customer Types</option>
                    <option value="walkin" {{ request('customer_type') === 'walkin' ? 'selected' : '' }}>Walk-in Customers</option>
                    <option value="corporate" {{ request('customer_type') === 'corporate' ? 'selected' : '' }}>Corporate Customers</option>
                    <option value="online" {{ request('customer_type') === 'online' ? 'selected' : '' }}>Online Customers</option>
                </select>
            </div>

            <div class="flex-none flex gap-2">
                <button type="submit"
                    class="px-3 py-2 md:px-6 md:py-3 bg-blue-600 hover:bg-blue-700 hover:-translate-y-0.5 text-white text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl flex items-center gap-1 md:gap-2 shadow-lg shadow-blue-600/25 transition-all h-[34px] md:h-[46px]">
                    <svg width="14" height="14" class="md:w-[18px] md:h-[18px]" fill="none" stroke="currentColor"
                        stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="hidden md:inline">Apply Filter</span>
                    <span class="md:hidden">Apply</span>
                </button>
                <a href="{{ route('reports.credit-orders') }}"
                    class="px-3 py-2 md:px-4 md:py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[10px] md:text-sm font-bold rounded-lg md:rounded-xl transition-colors h-[34px] md:h-[46px] flex items-center justify-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th
                            class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center w-12">
                            #</th>
                        <th
                            class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">
                            Customer</th>
                        <th
                            class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">
                            Pending Orders</th>
                        <th
                            class="px-2 py-2 md:px-6 md:py-4 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">
                            Total Pending Credit</th>
                        <th class="px-2 py-2 md:px-6 md:py-4 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="credit-table-body">
                    @forelse($customers as $index => $customer)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-bold text-slate-400">
                                {{ $index + 1 + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                            <td class="px-2 py-2 md:px-6 md:py-4 font-black text-slate-900">
                                {{ $customer->customer->name ?? 'Unknown Customer' }}
                                <div class="text-[9px] md:text-xs text-slate-500 font-medium mt-0.5">
                                    {{ $customer->customer->phone ?? '' }}</div>
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center font-black text-orange-600">
                                {{ $customer->pending_orders }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-right font-black text-red-600 text-sm md:text-base">
                                Rs.{{ number_format($customer->total_credit, 2) }}
                            </td>
                            <td class="px-2 py-2 md:px-6 md:py-4 text-center">
                                <button type="button"
                                    class="expand-btn p-2 bg-slate-100 hover:bg-blue-100 text-slate-500 hover:text-blue-600 rounded-full transition-all"
                                    data-customer-id="{{ $customer->customer_id }}">
                                    <svg class="w-4 h-4 md:w-5 md:h-5 transition-transform duration-300" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <tr class="hidden bg-slate-50/50" id="details-row-{{ $customer->customer_id }}">
                            <td colspan="5" class="p-0 border-t border-slate-100">
                                <div class="p-4 md:p-6" id="details-content-{{ $customer->customer_id }}">
                                    <div class="flex justify-center p-4">
                                        <svg class="animate-spin w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 text-[10px] md:text-sm font-semibold">
                                No credit orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.expand-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const customerId = this.dataset.customerId;
                    const row = document.getElementById(`details-row-${customerId}`);
                    const content = document.getElementById(`details-content-${customerId}`);
                    const icon = this.querySelector('svg');

                    if (row.classList.contains('hidden')) {
                        // Expand
                        row.classList.remove('hidden');
                        icon.style.transform = 'rotate(180deg)';

                        // Load data if not already loaded
                        if (!this.dataset.loaded) {
                            try {
                                const urlParams = new URLSearchParams({
                                    start_date: '{{ $startDate }}',
                                    end_date: '{{ $endDate }}',
                                    {!! $branchId ? "branch_id: '{$branchId}'," : "" !!}
                                    {!! !empty($customerType) ? "customer_type: '{$customerType}'," : "" !!}
                                });
                                const response = await fetch(`{{ url('reports/credit-orders/customer') }}/${customerId}?${urlParams.toString()}`);
                                const data = await response.json();
                                content.innerHTML = data.html;
                                this.dataset.loaded = 'true';
                            } catch (e) {
                                content.innerHTML = '<div class="text-red-500 text-center font-bold p-4">Failed to load orders. Please try again.</div>';
                            }
                        }
                    } else {
                        // Collapse
                        row.classList.add('hidden');
                        icon.style.transform = 'rotate(0deg)';
                    }
                });
            });
        });
    </script>

    <!-- Collect Credit Payment Modal -->
    <div x-data="{
        collectModalOpen: false,
        collectOrderId: null,
        collectOrderNumber: '',
        collectAmount: 0
    }" @open-collect-modal.window="
        collectOrderId = $event.detail.id;
        collectOrderNumber = $event.detail.number;
        collectAmount = $event.detail.amount;
        collectModalOpen = true;
    ">
        <div x-cloak x-show="collectModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col"
                @click.away="collectModalOpen = false">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-red-50">
                    <div>
                        <h3 class="text-sm md:text-base font-black text-red-600">Collect Credit Payment</h3>
                        <p class="text-[10px] md:text-sm font-bold text-red-700 mt-1"
                            x-text="'Order: ' + collectOrderNumber"></p>
                    </div>
                    <button @click="collectModalOpen = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors font-bold">&times;</button>
                </div>
                <div class="p-5 bg-white">
                    <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount
                                to Collect (Rs)</label>
                            <input type="number" step="0.01" name="amount" x-model="collectAmount" max="" required
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                        </div>
                        <div class="mb-5">
                            <label
                                class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment
                                Method</label>
                            <select name="payment_method" required
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl shadow-lg shadow-red-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                            Confirm Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>