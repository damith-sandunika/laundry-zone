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
        @include('customers.partials.profile_header')

        <!-- Tabs Container -->
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
                        <button type="submit" class="px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 text-xs font-bold rounded-lg transition-colors shadow-sm">Filter</button>
                        @if(request('start_date') || request('end_date'))
                        <a href="{{ route('customers.show', ['customer' => $customer, 'tab' => 'sales']) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-500 text-xs font-bold rounded-lg transition-colors shadow-sm" title="Clear Filters">Clear</a>
                        @endif
                        <a href="{{ route('customers.salesReport.pdf', ['customer' => $customer, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'month' => request('month')]) }}" target="_blank" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-colors shadow-sm flex items-center gap-1.5 ml-2">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </form>
                @endif
            </div>

            @if($tab === 'history')
                @include('customers.partials.tab_history')
            @elseif($tab === 'pending')
                @include('customers.partials.tab_pending')
            @elseif($tab === 'sales')
                @include('customers.partials.tab_sales')
            @endif
        </div>

        @include('customers.partials.payment_modals')
    </div>
</x-app-layout>
