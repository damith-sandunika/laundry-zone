<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">Dashboard</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">Today's updates and key metrics for <strong class="text-slate-700">{{ $branchName }}</strong></p>
            </div>
            @if($activeSession)
                <div class="flex flex-row flex-wrap items-center gap-2 sm:gap-3 w-full md:w-auto justify-end mt-3 md:mt-0">
                    <div class="flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 bg-green-50 border border-green-200 rounded-xl h-9 sm:h-10 shadow-sm flex-1 sm:flex-none">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-[10px] sm:text-xs font-bold text-green-700 tracking-wide uppercase whitespace-nowrap">Shift Active</span>
                    </div>

                    <div x-data="{ showCloseShiftModal: false }">
                        <button @click="showCloseShiftModal = true" class="flex items-center justify-center w-full px-3 sm:px-5 py-2 bg-white text-slate-600 text-[11px] sm:text-[13px] font-bold border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition-colors h-9 sm:h-10 shadow-sm whitespace-nowrap">
                            Close Shift
                        </button>

                        <!-- Close Shift Modal -->
                        <template x-teleport="body">
                            <div x-show="showCloseShiftModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
                                <div @click.away="showCloseShiftModal = false" x-show="showCloseShiftModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
                                    <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                        <h3 class="text-lg font-black text-slate-900">Close Today's Shift</h3>
                                        <button @click="showCloseShiftModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-6 flex flex-col gap-0 divide-y divide-slate-100">
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Opening Balance</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($activeSession->opening_cash ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Cash Sales</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['cash_collected'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Card Sales</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['card_payments_amount'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3">
                                            <span class="text-[13px] font-bold text-slate-500">Bank Transfers</span>
                                            <span class="text-[14px] font-black text-slate-800">Rs.{{ number_format($stats['bank_transfers'] ?? 0, 0) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-3 bg-blue-50/50 -mx-6 px-6">
                                            <span class="text-[14px] font-black text-blue-700">Total Sales</span>
                                            <span class="text-[16px] font-black text-blue-700">Rs.{{ number_format($stats['total_sale'] ?? 0, 0) }}</span>
                                        </div>

                                        <div class="flex justify-between items-center py-4 bg-emerald-50/50 -mx-6 px-6 border-b-0">
                                            <span class="text-[14px] font-black text-emerald-700 uppercase tracking-wide">Cash in Drawer</span>
                                            <span class="text-[20px] font-black text-emerald-700">Rs.{{ number_format($stats['cash_balance'] ?? 0, 0) }}</span>
                                        </div>
                                        
                                        <div class="mt-6 flex flex-col gap-3">
                                            <a href="{{ route('reports.closeShift', ['action' => 'print']) }}" target="_blank" class="w-full flex items-center justify-center gap-2 py-3 bg-slate-100 text-slate-700 text-[13px] font-extrabold rounded-xl hover:bg-slate-200 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Get Receipt
                                            </a>
                                            <form action="{{ route('shifts.close') }}" method="POST" onsubmit="return confirm('Are you sure you want to close this shift?')">
                                                @csrf
                                                <button type="submit" class="w-full py-3 bg-red-500 text-white text-[13px] font-extrabold rounded-xl hover:bg-red-600 transition-colors">
                                                    Close Branch
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl p-4 mb-6 animate-fade-in shadow-sm">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span class="text-[13px] font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl p-4 mb-6 animate-fade-in shadow-sm">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span class="text-[13px] font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    @if(!$activeSession)
        {{-- ── Shift Closed Card ── --}}
        <div class="flex justify-center py-10 px-4 animate-slide-up">
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-200 p-8 md:p-10 w-full max-w-4xl shadow-xl shadow-slate-200/50">
                
                <div class="text-center mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4 ring-8 ring-blue-50/50">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Shift is Closed</h3>
                    <p class="text-[14px] font-medium text-slate-500 max-w-lg mx-auto">Open the branch shift register with your starting cash and assign staff for today's operations.</p>
                </div>

                <form action="{{ route('shifts.open') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Left Column: Cash Details --}}
                        <div class="bg-slate-50/50 rounded-2xl border border-slate-100 p-6 flex flex-col justify-center">
                            <div class="mb-5 text-center">
                                <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-3">Opening Cash (Rs.)</label>
                                <div class="relative max-w-xs mx-auto">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 font-bold sm:text-sm">Rs.</span>
                                    </div>
                                    <input type="number" step="0.01" name="opening_cash" value="{{ $suggestedOpeningCash ?? 0 }}" placeholder="e.g. 5000" required class="block w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-xl text-xl font-black text-slate-900 text-center shadow-sm focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300">
                                </div>
                                <p class="text-[11px] text-slate-400 mt-3 font-semibold flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Auto-filled from previous shift's closing balance.
                                </p>
                            </div>
                        </div>

                        {{-- Right Column: Staff Assignment --}}
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                                <label class="block text-[11px] font-extrabold text-slate-800 uppercase tracking-widest">Assign Staff & Attendance</label>
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-md">{{ isset($branchStaff) ? $branchStaff->count() : 0 }} Staff Available</span>
                            </div>

                            @if(isset($branchStaff) && $branchStaff->count() > 0)
                                <div class="divide-y divide-slate-100 max-h-56 overflow-y-auto pr-2 custom-scrollbar">
                                    @foreach($branchStaff as $staff)
                                        <div class="py-3 border-b border-slate-100 last:border-0 flex items-center justify-between hover:bg-slate-50 transition-colors px-2 rounded-lg">
                                            <span class="text-[13px] font-bold text-slate-800">{{ $staff->name }}</span>
                                            <label class="flex items-center cursor-pointer">
                                                <input type="checkbox" name="assigned_staff[]" value="{{ $staff->id }}" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 transition-all">
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-8 text-center">
                                    <p class="text-[13px] font-semibold text-slate-400">No staff members found for this branch.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100 flex justify-center">
                        <button type="submit" class="w-full md:w-auto px-10 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-[14px] font-black tracking-wide rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Confirm & Open Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else

        {{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            @php
                $cards = [
                    ['label' => 'Active Orders', 'value' => $stats['active_orders'], 'sub' => 'Processing now', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                    ['label' => 'Pending Pickup', 'value' => $stats['pending_deliveries'], 'sub' => 'Awaiting dispatch', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Completed Today', 'value' => $stats['completed_today'], 'sub' => 'Finished orders', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50', 'icon' => 'M5 13l4 4L19 7'],
                    ['label' => 'Total Payments Collected', 'value' => 'Rs.' . number_format($stats['total_payments_collected'] ?? 0, 0), 'sub' => null, 'color' => 'text-violet-600', 'bg' => 'bg-violet-50', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp
            @foreach($cards as $index => $card)
                <div class="bg-white/70 backdrop-blur-md rounded-2xl border border-slate-200/60 p-5 flex justify-between items-center shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 animate-slide-up" style="animation-delay: {{ $index * 50 }}ms;">
                    <div>
                        <p class="text-[9px] sm:text-[10.5px] font-extrabold text-slate-500 uppercase tracking-widest mb-1 leading-tight sm:leading-normal whitespace-normal">{{ $card['label'] }}</p>
                        <p class="text-lg sm:text-3xl font-black text-slate-900 tracking-tight leading-none mb-1 sm:mb-2">{{ $card['value'] }}</p>
                        @if($card['label'] === 'Total Payments Collected')
                            <p class="inline-block px-1.5 sm:px-2.5 py-0.5 text-[8px] sm:text-[10px] font-bold text-violet-600 bg-violet-50 rounded-md uppercase tracking-wide leading-tight">Cash, Card & Bank</p>
                        @elseif($card['sub'])
                            <p class="inline-block px-1.5 sm:px-2.5 py-0.5 text-[8px] sm:text-[10px] font-bold {{ $card['color'] }} {{ $card['bg'] }} rounded-md uppercase tracking-wide leading-tight">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-xl {{ $card['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 {{ $card['color'] }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── MAIN GRID ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Recent Orders --}}
            <div class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-slide-up" style="animation-delay: 200ms;" x-data="{ orderTab: 'today' }">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Active Items</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Currently processing in workshop</p>
                    </div>
                    
                    @php
                        $todayCount = 0;
                        $tomorrowCount = 0;
                        $nextCount = 0;
                        foreach($recentOrders as $dispatchOrder) {
                            $dueDate = ($dispatchOrder->order && $dispatchOrder->order->due_date) ? \Carbon\Carbon::parse($dispatchOrder->order->due_date)->startOfDay() : null;
                            if (!$dueDate) { $nextCount++; continue; }
                            
                            if ($dueDate->lessThanOrEqualTo(today())) $todayCount++;
                            elseif ($dueDate->isTomorrow()) $tomorrowCount++;
                            else $nextCount++;
                        }
                    @endphp
                    
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="flex gap-1.5 flex-1 md:flex-none overflow-x-auto custom-scrollbar pb-1 md:pb-0">
                            <button @click="orderTab = 'today'" :class="orderTab === 'today' ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'" class="px-3 py-1.5 text-[11px] font-black rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                                Today
                                <span :class="orderTab === 'today' ? 'bg-white/20 text-white' : 'bg-white text-slate-600 shadow-sm'" class="px-1.5 py-0.5 rounded text-[9px] font-black">{{ $todayCount }}</span>
                            </button>
                            <button @click="orderTab = 'tomorrow'" :class="orderTab === 'tomorrow' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'" class="px-3 py-1.5 text-[11px] font-black rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                                Tomorrow
                                <span :class="orderTab === 'tomorrow' ? 'bg-white/20 text-white' : 'bg-white text-slate-600 shadow-sm'" class="px-1.5 py-0.5 rounded text-[9px] font-black">{{ $tomorrowCount }}</span>
                            </button>
                            <button @click="orderTab = 'next'" :class="orderTab === 'next' ? 'bg-violet-600 text-white shadow-md shadow-violet-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800'" class="px-3 py-1.5 text-[11px] font-black rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                                Next
                                <span :class="orderTab === 'next' ? 'bg-white/20 text-white' : 'bg-white text-slate-600 shadow-sm'" class="px-1.5 py-0.5 rounded text-[9px] font-black">{{ $nextCount }}</span>
                            </button>
                        </div>

                    </div>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[400px] custom-scrollbar relative">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="px-3 py-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Order</th>
                                <th class="px-3 py-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest w-full">Customer</th>
                                <th class="px-3 py-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest whitespace-nowrap">Due Date</th>
                                <th class="px-3 py-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest text-center whitespace-nowrap">Status</th>
                                <th class="px-3 py-3 text-right whitespace-nowrap"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50/50">
                            @forelse($recentOrders as $order)
                                @php
                                    $sc = ['received' => ['text-slate-600', 'bg-slate-100'], 'washing' => ['text-blue-700', 'bg-blue-50'], 'drying' => ['text-violet-700', 'bg-violet-50'], 'ready' => ['text-emerald-700', 'bg-emerald-50'], 'delivered' => ['text-teal-700', 'bg-teal-50'], 'cancelled' => ['text-red-700', 'bg-red-50']];
                                    $c = $sc[$order->status] ?? ['text-slate-600', 'bg-slate-100'];
                                    
                                    $dueDate = ($order->order && $order->order->due_date) ? \Carbon\Carbon::parse($order->order->due_date) : null;
                                    $rowTab = 'next';
                                    if ($dueDate && $dueDate->startOfDay()->lessThanOrEqualTo(today())) $rowTab = 'today';
                                    elseif ($dueDate && $dueDate->startOfDay()->isTomorrow()) $rowTab = 'tomorrow';
                                    
                                    // Make sure we use proper timezone comparison for overdue
                                    $isOverdue = false;
                                    if ($dueDate) {
                                        $isOverdue = $dueDate->isPast();
                                    }
                                @endphp
                                <tr x-show="orderTab === '{{ $rowTab }}'" class="hover:bg-slate-50/50 transition-colors group {{ $isOverdue ? 'bg-red-50/50' : '' }}">
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order->order->order_number ?? '') }}" class="text-[12px] font-black text-blue-600 hover:text-blue-800 transition-colors">{{ $order->order->order_number ?? 'N/A' }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-[12px] font-bold text-slate-700 break-words max-w-[150px]">
                                        {{ $order->order->customer->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @if($order->order && $order->order->due_date)
                                            <div class="flex items-center gap-1.5">
                                                <div>
                                                    <div class="text-[11px] font-bold {{ $isOverdue ? 'text-red-600' : 'text-slate-800' }}">{{ \Carbon\Carbon::parse($order->order->due_date)->format('M d, Y') }}</div>
                                                    <div class="text-[10px] font-semibold {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">{{ \Carbon\Carbon::parse($order->order->due_date)->format('h:i A') }}</div>
                                                </div>
                                                @if($isOverdue)
                                                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-red-100 animate-pulse text-red-600 shrink-0" title="Overdue">
                                                        <i class="fa-solid fa-clock text-[9px]"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[11px] font-semibold text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $c[0] }} {{ $c[1] }}">{{ $order->status }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        @if($order->order && $order->order->receipt_path)
                                            <a href="{{ \Illuminate\Support\Str::startsWith($order->order->receipt_path, 'http') ? $order->order->receipt_path : asset('storage/' . $order->order->receipt_path) }}" target="_blank" class="inline-block px-2 py-1 bg-white border border-slate-200 text-slate-600 hover:text-green-700 hover:border-green-200 hover:bg-green-50 rounded-md text-[10px] font-bold transition-all mb-1 lg:mb-0 lg:mr-1">Invoice</a>
                                        @endif
                                        <a href="{{ route('orders.show', $order->order->order_number ?? '') }}" class="inline-block px-2 py-1 bg-white border border-slate-200 text-slate-600 hover:text-blue-700 hover:border-blue-200 hover:bg-blue-50 rounded-md text-[10px] font-bold transition-all">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-[13px] font-semibold text-slate-400">No active orders</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Chart --}}
            <div class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm p-6 flex flex-col animate-slide-up" style="animation-delay: 250ms;">
                <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Order Performance</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Processing vs Completed</p>
                    </div>
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button data-chart-tab="today" class="chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Today</button>
                        <button data-chart-tab="weekly" class="chart-tab-btn active-tab px-4 py-1.5 text-xs font-bold rounded-lg transition-all bg-white text-slate-800 shadow-sm">Weekly</button>
                        <button data-chart-tab="monthly" class="chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Monthly</button>
                    </div>
                </div>
                <div class="relative flex-1 min-h-[220px]">
                    <canvas id="orderChart"></canvas>
                </div>
                <div class="flex justify-center gap-6 mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm bg-amber-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Processing</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                        <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">Completed</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── BOTTOM GRID ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

            {{-- Inventory Alerts --}}
            <div x-data="{ showInvModal: false }" class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-slide-up" style="animation-delay: 300ms;">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight">Inventory Alerts</h3>
                            <p class="text-[12px] font-semibold text-slate-500">Low stock warnings</p>
                        </div>
                    </div>
                    <button @click="showInvModal = true" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl transition-colors">
                        View Full Inventory
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-3">
                    @forelse($lowStockItems as $item)
                        <div class="flex justify-between items-center p-4 bg-red-50/50 border border-red-100 rounded-2xl hover:bg-red-50 transition-colors">
                            <div>
                                <p class="text-[13px] font-black text-slate-900 mb-1">{{ $item->name }}</p>
                                <p class="text-[10px] font-extrabold text-red-500 uppercase tracking-widest">Min: {{ $item->low_stock_threshold }}</p>
                            </div>
                            <div class="text-center bg-white border border-red-100 rounded-xl px-4 py-2 shadow-sm">
                                <p class="text-xl font-black text-red-600 leading-none">{{ $item->current_stock }}</p>
                                <p class="text-[9px] font-extrabold text-red-400 uppercase mt-1">{{ $item->unit }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center p-8 bg-green-50/50 rounded-2xl text-center border border-green-100">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <p class="text-sm font-black text-green-700 mb-1">Stock Levels Good</p>
                            <p class="text-xs font-semibold text-green-600/80">No items are currently running low.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Inventory Modal -->
                <template x-teleport="body">
                    <div x-show="showInvModal" class="fixed inset-0 z-[100] flex items-start justify-center pt-16 sm:pt-24 bg-slate-900/40 backdrop-blur-sm p-4" x-cloak>
                        <div @click.away="showInvModal = false" x-show="showInvModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden max-h-[85vh] flex flex-col">
                            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                                <h3 class="text-lg font-black text-slate-900">Current Inventory Status</h3>
                                <button @click="showInvModal = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="overflow-y-auto flex-1 p-0">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-slate-50 sticky top-0">
                                        <tr>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Item Name</th>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Stock Level</th>
                                            <th class="px-6 py-4 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest border-b border-slate-200">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($allInventory as $inv)
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="px-6 py-4 text-[13px] font-bold text-slate-900">{{ $inv->name }}</td>
                                                <td class="px-6 py-4">
                                                    <span class="text-sm font-black text-blue-700">{{ $inv->current_stock }}</span>
                                                    <span class="text-[11px] font-bold text-slate-500">{{ $inv->unit }}</span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($inv->current_stock <= $inv->low_stock_threshold)
                                                        <span class="inline-flex px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg text-[10px] font-extrabold uppercase tracking-wide">Low Stock</span>
                                                    @else
                                                        <span class="inline-flex px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 rounded-lg text-[10px] font-extrabold uppercase tracking-wide">Good</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Shift Register (Dark Card) --}}
            <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-xl shadow-slate-900/20 animate-slide-up" style="animation-delay: 350ms;">
                <div class="px-6 py-5 border-b border-slate-700/50 flex items-center justify-between gap-4 bg-slate-800/50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-white tracking-tight">Shift Register</h3>
                            <p class="text-[12px] font-medium text-slate-400">Current session metrics</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('depositModal').classList.remove('hidden')" class="px-3.5 py-2 bg-emerald-500/20 border border-emerald-500/30 hover:bg-emerald-500/30 text-emerald-400 text-xs font-bold rounded-xl transition-colors whitespace-nowrap">
                        Deposit to Bank
                    </button>
                </div>
                <div class="p-6 flex flex-col gap-0 divide-y divide-slate-800">
                    <div class="flex justify-between items-center py-3">
                        <span class="text-[12px] font-semibold text-slate-400">Opening Balance</span>
                        <span class="text-[14px] font-black text-white tracking-tight">Rs.{{ number_format($activeSession->opening_cash, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Cash Sales</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['cash_collected'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Card Sales</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['card_payments_amount'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Bank Transfers</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['bank_transfers'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-slate-700 mb-2">
                        <span class="text-[12px] font-bold text-slate-300">Total Payments Collected</span>
                        <span class="text-[14px] font-black text-white">Rs.{{ number_format($stats['total_payments_collected'] ?? 0, 0) }}</span>
                    </div>

                    <div class="py-2 mt-2">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Shift Revenue (Order Value)</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Total Payments Collected</span>
                        <span class="text-[14px] font-bold text-slate-300">Rs.{{ number_format($stats['total_payments_collected'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-slate-700/50 mb-2">
                        <span class="text-[12px] font-semibold text-slate-400">Less: Workshop Delivery Fees</span>
                        <span class="text-[14px] font-bold text-slate-400">- Rs.{{ number_format($stats['workshop_direct_delivery_fees'] ?? 0, 0) }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center py-2 bg-slate-800/30 -mx-6 px-6 mb-2">
                        <span class="text-[12px] font-bold text-blue-400">Direct Workshop Sales</span>
                        <span class="text-[14px] font-black text-blue-400">Rs.{{ number_format($stats['workshop_direct_commissionable_sales'] ?? 0, 0) }}</span>
                    </div>

                    <div class="py-1">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Branch Orders Breakdown</span>
                    </div>
                    @if(!empty($stats['branch_breakdown']))
                        @foreach($stats['branch_breakdown'] as $b)
                            <div class="bg-slate-800/40 rounded-xl p-3 my-1 border border-slate-700/50">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[12px] font-bold text-slate-300">{{ $b['name'] }}</span>
                                    <span class="text-[13px] font-bold text-slate-300">Total: Rs.{{ number_format($b['gross_sales'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[11px] font-semibold text-slate-400">Given Comm. ({{ $b['profit_percentage'] }}%)</span>
                                    <span class="text-[12px] font-bold text-red-400">- Rs.{{ number_format($b['branch_commission'], 0) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-1.5 border-t border-slate-700/50">
                                    <span class="text-[11px] font-bold text-emerald-500">Workshop Portion</span>
                                    <span class="text-[12px] font-bold text-emerald-400">Rs.{{ number_format($b['workshop_commission'], 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="py-3 text-center text-[12px] font-semibold text-slate-500 bg-slate-800/20 rounded-xl mb-2">
                            No branch orders processed in this shift.
                        </div>
                    @endif

                    <div class="flex justify-between items-center py-2 border-b border-slate-700/50">
                        <span class="text-[12px] font-semibold text-emerald-400">Total Workshop Commission (From Branches)</span>
                        <span class="text-[14px] font-bold text-emerald-400">Rs.{{ number_format($stats['workshop_commission_from_branches'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-t border-slate-700">
                        <span class="text-[13px] font-bold text-emerald-400">Total Sales Revenue</span>
                        <span class="text-[14px] font-black text-emerald-400">Rs.{{ number_format($stats['total_sales_revenue'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Workshop Delivery Fees</span>
                        <span class="text-[14px] font-bold text-slate-300">+ Rs.{{ number_format($stats['workshop_direct_delivery_fees'] ?? 0, 0) }}</span>
                    </div>


                    <div class="flex justify-between items-center py-3 bg-blue-900/20 -mx-6 px-6 border-y border-slate-800">
                        <span class="text-[13px] font-bold text-amber-400">Today Total Sales (Net Income)</span>
                        <span class="text-[16px] font-black text-amber-400">Rs.{{ number_format($stats['net_income'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-4 bg-emerald-900/20 -mx-6 px-6 border-b border-emerald-900/30">
                        <span class="text-[13px] font-black text-emerald-400 uppercase tracking-wide">Cash in Drawer</span>
                        <span class="text-[18px] font-black text-emerald-400">Rs.{{ number_format($stats['cash_balance'] ?? 0, 0) }}</span>
                    </div>

                    <!-- Pending Payments -->
                    <div class="py-2 mt-2 border-t border-slate-700 pt-3">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Pending Payments (Today's Orders)</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[12px] font-semibold text-slate-400">Our Workshop</span>
                        <span class="text-[13px] font-bold text-amber-400">Rs.{{ number_format($stats['our_pending_payments'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 mb-2">
                        <span class="text-[12px] font-semibold text-slate-400">Branches</span>
                        <span class="text-[13px] font-bold text-amber-400">Rs.{{ number_format($stats['branch_pending_payments'] ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-t border-slate-700">
                        <span class="text-[11px] font-semibold text-slate-500">Opened By</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ $activeSession->user?->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b-0">
                        <span class="text-[11px] font-semibold text-slate-500">Time Started</span>
                        <span class="text-[11px] font-bold text-slate-300">{{ \Carbon\Carbon::parse($activeSession->opened_at)->format('h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Staff Attendance & Assignment --}}
        <div class="bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden mb-6 animate-slide-up" style="animation-delay: 400ms;">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Staff Attendance & System Assignment</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Manage today's shift access and attendance</p>
                    </div>
                </div>
                <button type="submit" form="update-attendance-form" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Update Records
                </button>
            </div>
            <div class="p-0 overflow-x-auto">
                <form id="update-attendance-form" action="{{ route('shifts.attendance.update') }}" method="POST">
                    @csrf
                    <!-- Desktop Header -->
                    <div class="hidden md:flex items-center justify-between px-6 py-3 bg-slate-50 border-b border-slate-100">
                        <div class="w-1/4 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Staff Member</div>
                        <div class="flex-1 flex justify-end items-center gap-12 pr-4">
                            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest w-24 text-center">System Access</div>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100/60">
                        @foreach($branchStaff as $staff)
                            @php
                                $att = $activeAttendances->get($staff->id);
                                $status = $att ? $att->status : 'hold';
                                $isAssigned = $att ? $att->is_assigned : false;
                                $clockIn = $att ? $att->clock_in : null;
                                $clockOut = $att ? $att->clock_out : null;
                            @endphp
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-4 md:px-6 hover:bg-slate-50/50 transition-colors gap-4">
                                
                                <!-- Staff Info -->
                                <div class="flex items-center gap-3 md:w-1/4">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm uppercase shrink-0">
                                        {{ substr($staff->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="text-[14px] font-bold text-slate-800 block leading-tight">{{ $staff->name }}</span>
                                        <span class="text-[11px] font-semibold text-slate-400">ID: #{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <input type="hidden" name="assigned_staff[{{ $att ? $att->id : 'new_'.$staff->id }}][staff_id]" value="{{ $staff->id }}">
                                </div>

                                <!-- Controls Grid -->
                                <div class="grid grid-cols-1 md:flex md:flex-row md:items-center gap-4 md:gap-12 flex-1 md:justify-end">
                                    <!-- System Access -->
                                    <div class="flex flex-col gap-1.5 items-start md:items-center md:w-24">
                                        <label class="text-[10px] font-extrabold text-slate-400 uppercase md:hidden">System Access</label>
                                        <div class="flex items-center h-[34px]">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="assigned_staff[{{ $att ? $att->id : 'new_'.$staff->id }}][is_assigned]" value="1" class="w-5 h-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 transition-all system-access-box shadow-sm" {{ $isAssigned ? 'checked' : '' }}>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>

    @endif

    <!-- Bank Deposit Modal -->
    <div id="depositModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-900">Make Bank Deposit</h3>
                <button onclick="document.getElementById('depositModal').classList.add('hidden')" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('bank-deposits.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Available Cash to Deposit</label>
                    <p class="text-lg font-black text-emerald-600">Rs. {{ number_format($stats['cash_balance'] ?? 0, 2) }}</p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Deposit Amount (Rs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required max="{{ $stats['cash_balance'] ?? 0 }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Reference Number (Optional)</label>
                    <input type="text" name="reference_number" placeholder="e.g. SLIP-10294" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400">
                </div>

                <div class="mb-6">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 outline-none transition-all placeholder:text-slate-400"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 bg-emerald-500 text-white text-sm font-extrabold rounded-xl shadow-lg shadow-emerald-500/30 hover:bg-emerald-600 hover:shadow-emerald-500/50 hover:-translate-y-0.5 transition-all duration-300">
                    Confirm Deposit
                </button>
            </form>
        </div>
    </div>

    <x-slot name="scripts">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const chartData = {
                today: { labels: {!! json_encode($chartData['today']['labels']) !!}, pending: {!! json_encode($chartData['today']['pending']) !!}, delivered: {!! json_encode($chartData['today']['delivered']) !!} },
                weekly: { labels: {!! json_encode($chartData['weekly']['labels']) !!}, pending: {!! json_encode($chartData['weekly']['pending']) !!}, delivered: {!! json_encode($chartData['weekly']['delivered']) !!} },
                monthly: { labels: {!! json_encode($chartData['monthly']['labels']) !!}, pending: {!! json_encode($chartData['monthly']['pending']) !!}, delivered: {!! json_encode($chartData['monthly']['delivered']) !!} },
            };

            const ctx = document.getElementById('orderChart');
            let chart = null;

            function renderChart(tab) {
                const d = chartData[tab];
                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: d.labels,
                        datasets: [
                            { label: 'Processing', data: d.pending, backgroundColor: '#f59e0b', borderRadius: 6, barPercentage: 0.55 },
                            { label: 'Completed', data: d.delivered, backgroundColor: '#10b981', borderRadius: 6, barPercentage: 0.55 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 8 } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } } },
                            x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 12, weight: '700' } } }
                        }
                    }
                });
            }

            if (ctx) renderChart('weekly');

            const tabBtns = document.querySelectorAll('.chart-tab-btn');
            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabBtns.forEach(b => {
                        b.className = 'chart-tab-btn px-4 py-1.5 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700';
                    });
                    btn.className = 'chart-tab-btn active-tab px-4 py-1.5 text-xs font-bold rounded-lg transition-all bg-white text-slate-800 shadow-sm ring-1 ring-slate-200/50';
                    renderChart(btn.dataset.chartTab);
                });
            });
        </script>
    </x-slot>

</x-app-layout>

