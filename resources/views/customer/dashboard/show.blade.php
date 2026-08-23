<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customer.orders') }}" class="text-slate-400 hover:text-blue-600 transition p-2 bg-white rounded-lg shadow-sm border border-slate-100"><i class="fa-solid fa-arrow-left"></i></a>
                <div>
                    <h2 class="font-black text-2xl text-slate-800 leading-tight">Order #{{ $order->order_number }}</h2>
                    <p class="text-sm font-bold text-slate-500">Placed on {{ $order->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            @if($order->payment_status === 'unpaid')
                <a href="{{ route('customer.orders.pay', $order) }}" class="bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-slate-900 hover:shadow-lg transition-all text-sm flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Pay Now (LKR {{ number_format($order->total, 2) }})
                </a>
            @else
                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Paid
                </span>
            @endif
        </div>
    </x-slot>

    <!-- Horizontal Status Timeline -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 lg:p-8 mb-6 overflow-hidden">
        <h3 class="font-black text-slate-800 mb-8 flex items-center gap-2"><i class="fa-solid fa-bars-progress text-blue-500"></i> Order Progress</h3>
        
        @php
            if ($order->is_delivery_order) {
                $statuses = [
                    'pending'    => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'processing' => ['label' => 'Accepted', 'icon' => 'fa-clipboard-check', 'color' => 'blue'],
                    'washing'    => ['label' => 'Picked Up', 'icon' => 'fa-truck-pickup', 'color' => 'blue'],
                    'ready'      => ['label' => 'Job Done', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'delivered'  => ['label' => 'Delivered', 'icon' => 'fa-truck-fast', 'color' => 'purple'],
                    'completed'  => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            } else {
                $statuses = [
                    'pending'    => ['label' => 'Order Placed', 'icon' => 'fa-clipboard-list', 'color' => 'yellow'],
                    'ready'      => ['label' => 'Job Done', 'icon' => 'fa-box-open', 'color' => 'blue'],
                    'completed'  => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => 'green']
                ];
            }
            
            $actualStatus = $order->status;
            
            if ($order->is_delivery_order) {
                if (in_array($actualStatus, ['drying', 'ironing'])) {
                    $actualStatus = 'washing';
                }
            } else {
                if (in_array($actualStatus, ['processing', 'washing', 'drying', 'ironing'])) {
                    $actualStatus = 'pending';
                }
            }
            
            if ($actualStatus === 'delivered' && $order->payment_status === 'paid') {
                $actualStatus = 'completed';
            } elseif ($actualStatus === 'delivered' && $order->payment_status !== 'paid' && !$order->is_delivery_order) {
                $actualStatus = 'ready'; 
            }

            if ($order->status === 'cancelled') {
                $statuses = ['cancelled' => ['label' => 'Order Cancelled', 'icon' => 'fa-xmark', 'color' => 'red']];
                $actualStatus = 'cancelled';
            }
            
            $currentStatusIndex = array_search($actualStatus, array_keys($statuses));
            if ($currentStatusIndex === false) $currentStatusIndex = 0;
            
            $totalSteps = count($statuses);
            $currentIndex = 0;
        @endphp

        <div class="relative mt-4">
            <div class="absolute left-0 top-6 w-full h-1 bg-slate-100 rounded-full z-0 hidden sm:block"></div>
            
            <div class="absolute left-0 top-6 h-1 bg-blue-500 rounded-full z-0 transition-all duration-500 hidden sm:block" 
                 style="width: {{ $totalSteps > 1 ? ($currentStatusIndex / ($totalSteps - 1)) * 100 : 100 }}%"></div>

            <div class="flex flex-col sm:flex-row justify-between relative z-10 gap-6 sm:gap-0">
                @foreach($statuses as $key => $details)
                    @php
                        $isCompleted = $currentIndex <= $currentStatusIndex;
                        $isCurrent = $currentIndex === $currentStatusIndex;
                        
                        $colorClasses = [
                            'yellow' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-600', 'border' => 'border-yellow-200'],
                            'blue'   => ['bg' => 'bg-blue-500', 'text' => 'text-blue-600', 'border' => 'border-blue-200'],
                            'green'  => ['bg' => 'bg-green-500', 'text' => 'text-green-600', 'border' => 'border-green-200'],
                            'purple' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-200'],
                            'red'    => ['bg' => 'bg-red-500', 'text' => 'text-red-600', 'border' => 'border-red-200'],
                        ];
                        $color = $colorClasses[$details['color']];
                    @endphp
                    
                    <div class="flex flex-row sm:flex-col items-center sm:text-center gap-4 sm:gap-3 flex-1 relative">
                        @if($currentIndex < $totalSteps - 1)
                            <div class="absolute left-6 top-12 bottom-[-24px] w-0.5 bg-slate-100 sm:hidden z-0 {{ $isCompleted && $currentIndex < $currentStatusIndex ? '!bg-blue-500' : '' }}"></div>
                        @endif

                        <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-sm shrink-0 relative z-10 transition-colors duration-300
                            {{ $isCompleted ? $color['bg'] . ' text-white' : 'bg-slate-100 text-slate-400' }}
                            {{ $isCurrent ? 'ring-4 ring-blue-50/50 scale-110' : '' }}">
                            <i class="fa-solid {{ $details['icon'] }} text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black {{ $isCompleted ? 'text-slate-800' : 'text-slate-400' }}">{{ $details['label'] }}</h4>
                            @if($isCurrent)
                                <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600 mt-1 bg-blue-50 inline-block px-2 py-0.5 rounded-full hidden sm:inline-block">Current</p>
                            @endif
                        </div>
                    </div>
                    
                    @php $currentIndex++; @endphp
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Order Items -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-black text-slate-800"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i> Order Items</h3>
                    <span class="text-sm font-bold text-slate-500">{{ $order->items->count() }} Items</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        <div class="p-6 flex items-center justify-between hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-tshirt"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm">{{ $item->serviceType->name ?? 'Service' }}</h4>
                                    <p class="text-xs font-bold text-slate-500 mt-1">Qty: {{ $item->quantity }} &times; LKR {{ number_format($item->unit_price, 2) }}</p>
                                </div>
                            </div>
                            <div class="font-black text-slate-800">
                                LKR {{ number_format($item->subtotal, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bg-slate-50 p-6 border-t border-slate-100">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm font-bold text-slate-500">
                            <span>Subtotal</span>
                            <span>LKR {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount > 0)
                            <div class="flex justify-between text-sm font-bold text-green-600">
                                <span>Discount</span>
                                <span>- LKR {{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->additional > 0)
                            <div class="flex justify-between text-sm font-bold text-red-500">
                                <span>Additional Charges</span>
                                <span>+ LKR {{ number_format($order->additional, 2) }}</span>
                            </div>
                        @endif
                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-black text-slate-800">Total Amount</span>
                            <span class="font-black text-xl text-brand">LKR {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
                <div class="bg-yellow-50 rounded-3xl border border-yellow-100 p-6">
                    <h3 class="font-black text-yellow-800 text-sm mb-2"><i class="fa-solid fa-note-sticky mr-1"></i> Order Notes</h3>
                    <p class="text-sm text-yellow-700 font-medium">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column: Timeline & Branch -->
        <div class="space-y-6">
            <!-- Branch Info -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-[40px] -z-0"></div>
                <h3 class="font-black text-slate-800 mb-4 relative z-10"><i class="fa-solid fa-shop text-blue-500 mr-2"></i> Branch Details</h3>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-sm">{{ $order->branch->name }}</p>
                    <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $order->branch->address }}</p>
                    @if($order->branch->phone)
                        <p class="text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $order->branch->phone }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-customer-layout>
