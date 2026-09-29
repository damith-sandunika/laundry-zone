{{-- Recent Orders --}}
            <div class="h-fit bg-white/70 backdrop-blur-md rounded-3xl border border-slate-200/60 shadow-sm overflow-hidden animate-slide-up" style="animation-delay: 200ms;" x-data="{ orderTab: '{{ $stats['active_orders'] ?? 0 > 0 ? (collect($recentOrders)->filter(fn($o) => ($o->due_date ?? null) && \Carbon\Carbon::parse($o->due_date)->isPast())->count() > 0 ? 'overdue' : 'today') : 'today' }}' }">
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">Active Orders</h3>
                        <p class="text-[12px] font-semibold text-slate-500">Currently processing</p>
                    </div>
                    
                    @php
                        $todayCount = 0;
                        $tomorrowCount = 0;
                        $nextCount = 0;
                        $overdueCount = 0;
                        foreach($recentOrders as $order) {
                            $dueDate = $order->due_date ? \Carbon\Carbon::parse($order->due_date) : null;
                            if (!$dueDate) { $nextCount++; continue; }
                            
                            if ($dueDate->isPast()) {
                                $overdueCount++;
                            } elseif ($dueDate->startOfDay()->lessThanOrEqualTo(today())) {
                                $todayCount++;
                            } elseif ($dueDate->startOfDay()->isTomorrow()) {
                                $tomorrowCount++;
                            } else {
                                $nextCount++;
                            }
                        }
                    @endphp
                    
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <div class="flex gap-1.5 flex-1 md:flex-none overflow-x-auto custom-scrollbar pb-1 md:pb-0">
                            @if($overdueCount > 0)
                                <button @click="orderTab = 'overdue'" :class="orderTab === 'overdue' ? 'bg-red-600 text-white shadow-md shadow-red-500/30' : 'bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-800 border border-red-100'" class="px-3 py-1.5 text-[11px] font-black rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-3.5 h-3.5 animate-pulse text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Overdue
                                    <span :class="orderTab === 'overdue' ? 'bg-white/20 text-white' : 'bg-white text-red-600 shadow-sm'" class="px-1.5 py-0.5 rounded text-[9px] font-black">{{ $overdueCount }}</span>
                                </button>
                            @endif
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
                            @php
                                $tabRenderCounts = ['overdue' => 0, 'today' => 0, 'tomorrow' => 0, 'next' => 0];
                            @endphp
                            @forelse($recentOrders as $order)
                                @php
                                    $sc = ['received' => ['text-slate-600', 'bg-slate-100'], 'washing' => ['text-blue-700', 'bg-blue-50'], 'drying' => ['text-violet-700', 'bg-violet-50'], 'ready' => ['text-emerald-700', 'bg-emerald-50'], 'delivered' => ['text-teal-700', 'bg-teal-50'], 'cancelled' => ['text-red-700', 'bg-red-50']];
                                    $c = $sc[$order->status] ?? ['text-slate-600', 'bg-slate-100'];
                                    
                                    $dueDate = $order->due_date ? \Carbon\Carbon::parse($order->due_date) : null;
                                    $rowTab = 'next';
                                    $isOverdue = false;
                                    
                                    if ($dueDate) {
                                        if ($dueDate->isPast()) {
                                            $rowTab = 'overdue';
                                            $isOverdue = true;
                                        } elseif ($dueDate->startOfDay()->lessThanOrEqualTo(today())) {
                                            $rowTab = 'today';
                                        } elseif ($dueDate->startOfDay()->isTomorrow()) {
                                            $rowTab = 'tomorrow';
                                        }
                                    }
                                    
                                    $tabRenderCounts[$rowTab]++;
                                @endphp
                                <tr x-show="orderTab === '{{ $rowTab }}' && {{ $tabRenderCounts[$rowTab] }} <= 5" class="hover:bg-slate-50/50 transition-colors group {{ $isOverdue ? 'bg-red-50/50' : '' }}">
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order->order_number) }}" class="text-[12px] font-black text-blue-600 hover:text-blue-800 transition-colors">{{ $order->order_number }}</a>
                                    </td>
                                    <td class="px-3 py-3 text-[12px] font-bold text-slate-700 break-words max-w-[150px]">
                                        {{ $order->customer->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @if($order->due_date)
                                            <div class="flex items-center gap-1.5">
                                                <div>
                                                    <div class="text-[11px] font-bold {{ $isOverdue ? 'text-red-600' : 'text-slate-800' }}">{{ \Carbon\Carbon::parse($order->due_date)->format('M d, Y') }}</div>
                                                    <div class="text-[10px] font-semibold {{ $isOverdue ? 'text-red-500' : 'text-slate-400' }}">{{ \Carbon\Carbon::parse($order->due_date)->format('h:i A') }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-[11px] font-semibold text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-widest {{ $c[0] }} {{ $c[1] }}">{{ $order->status }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order->order_number) }}" class="inline-block px-2 py-1 bg-white border border-slate-200 text-slate-600 hover:text-blue-700 hover:border-blue-200 hover:bg-blue-50 rounded-md text-[10px] font-bold transition-all">View</a>
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
                <div class="px-4 py-2 border-t border-slate-100 bg-slate-50/30 flex justify-center">
                    <a href="{{ route('reports.pending-orders') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-1">
                        See all pending orders
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
