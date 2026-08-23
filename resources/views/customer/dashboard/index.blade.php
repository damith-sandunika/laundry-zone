<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Welcome, {{ $customer->name }}!</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">Manage your laundry orders and track deliveries.</p>
            </div>
            <a href="{{ route('customer.booking') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> New Booking
            </a>
        </div>
    </x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Orders</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $activeCount }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalOrders }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center gap-4 col-span-2 md:col-span-1">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Spent</p>
                <h3 class="text-2xl font-black text-slate-800">LKR {{ number_format($totalSpent, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Active Orders -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">Active Orders</h3>
            @if($activeCount > 0)
                <a href="{{ route('customer.orders') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View All</a>
            @endif
        </div>

        @if($activeCount > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Appointments (Pending Pickups) -->
                @foreach($appointments as $appointment)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-purple-100 to-transparent rounded-bl-3xl -z-0"></div>
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-1 rounded-md mb-2 inline-block">
                                    <i class="fa-solid fa-truck-pickup"></i> Pickup Request
                                </span>
                                <h4 class="font-bold text-slate-800">Booking #{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}</h4>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                    {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                                </span>
                                <a href="{{ route('customer.appointments.show', $appointment->id) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                    View Details
                                </a>
                            </div>
                        </div>

                        <!-- Timeline for Pickup -->
                        <div class="mt-6 pt-4 border-t border-slate-100 relative z-10">
                            <div class="flex items-center justify-between relative">
                                <div class="absolute left-0 top-3 w-full h-1 bg-slate-100 rounded-full"></div>
                                @php
                                    $appProgress = '0%';
                                    $appLevel = 1;
                                    if(in_array($appointment->status, ['assigned', 'accepted'])) { $appProgress = '12.5%'; $appLevel = 2; }
                                    elseif(in_array($appointment->status, ['on_way', 'arrived'])) { $appProgress = '25%'; $appLevel = 2; }
                                @endphp
                                <div class="absolute left-0 top-3 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-1000 ease-out" style="width: {{ $appProgress }}"></div>
                                
                                <!-- Step 1: Confirmed -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow-md bg-blue-500 transition-transform duration-500 hover:scale-110"><i class="fa-solid fa-check"></i></div>
                                    <span class="text-[10px] font-bold text-slate-800 text-center leading-tight">Confirmed</span>
                                </div>
                                
                                <!-- Step 2: Rider Arriving -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $appLevel >= 2 ? 'bg-blue-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                        @if($appLevel >= 2) <i class="fa-solid fa-motorcycle {{ in_array($appointment->status, ['on_way', 'arrived']) ? 'animate-bounce' : '' }}"></i> @else 2 @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-center leading-tight {{ $appLevel >= 2 ? 'text-blue-600' : 'text-slate-400' }}">Rider Arriving</span>
                                </div>
                                
                                <!-- Step 3: Working -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md bg-white border-2 border-slate-200 text-slate-400">3</div>
                                    <span class="text-[10px] font-bold text-center leading-tight text-slate-400">Working</span>
                                </div>
                                
                                <!-- Step 4: Delivering -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md bg-white border-2 border-slate-200 text-slate-400">4</div>
                                    <span class="text-[10px] font-bold text-center leading-tight text-slate-400">Delivering</span>
                                </div>
                                
                                <!-- Step 5: Completed -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md bg-white border-2 border-slate-200 text-slate-400">5</div>
                                    <span class="text-[10px] font-bold text-center leading-tight text-slate-400">Completed</span>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach

                <!-- Active Processing Orders -->
                @foreach($activeOrders as $order)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        <div class="flex justify-between items-start mb-4 relative z-10">
                            <div>
                                <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded-md mb-2 inline-block">
                                    {{ $order->order_number }}
                                </span>
                                <h4 class="font-bold text-slate-800">{{ $order->items->count() }} Items &middot; <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span></h4>
                            </div>
                            <div class="text-right flex flex-col items-end gap-2">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($order->status === 'received') bg-blue-100 text-blue-700
                                    @elseif(in_array($order->status, ['processing', 'dispatched_to_workshop', 'returned_from_workshop'])) bg-purple-100 text-purple-700
                                    @elseif($order->status === 'done') bg-green-100 text-green-700
                                    @endif
                                ">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                                @if($order->payment_status === 'unpaid')
                                    <a href="{{ route('customer.orders.pay', $order->id) }}" class="text-[10px] font-bold uppercase tracking-wider text-white bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg transition-colors shadow-sm animate-pulse">
                                        Pay Now
                                    </a>
                                @else
                                    <a href="{{ route('customer.orders.show', $order) }}" class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                        View Details
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Timeline for Orders -->
                        @php
                            $isDelivery = \App\Models\DeliveryAppointment::where('order_id', $order->id)->exists();
                            
                            $orderProgress = '0%';
                            $orderStatusLevel = 1;
                            
                            if ($isDelivery) {
                                // 5-step timeline for delivery: Confirmed -> Rider Pick Up -> Working -> Delivering -> Completed
                                if($order->status === 'received') { 
                                    $orderProgress = '25%'; $orderStatusLevel = 2; // Order created means rider has already arrived and picked up
                                }
                                elseif(in_array($order->status, ['dispatched_to_workshop', 'processing', 'returned_from_workshop'])) { 
                                    $orderProgress = '50%'; $orderStatusLevel = 3; 
                                }
                                elseif($order->status === 'done') { 
                                    $orderProgress = '75%'; $orderStatusLevel = 4; 
                                }
                                elseif($order->status === 'delivered') { 
                                    $orderProgress = '100%'; $orderStatusLevel = 5; 
                                }
                            } else {
                                // 4-step timeline for non-delivery: Confirmed -> Working -> Ready -> Completed
                                if($order->status === 'received') { $orderProgress = '0%'; $orderStatusLevel = 1; }
                                elseif(in_array($order->status, ['dispatched_to_workshop', 'processing', 'returned_from_workshop'])) { $orderProgress = '33%'; $orderStatusLevel = 2; }
                                elseif($order->status === 'done') { $orderProgress = '66%'; $orderStatusLevel = 3; }
                                elseif($order->status === 'delivered') { $orderProgress = '100%'; $orderStatusLevel = 4; }
                            }
                        @endphp
                        
                        <div class="mt-4 pt-4 border-t border-slate-100 relative z-10">
                            <div class="flex items-center justify-between relative">
                                <div class="absolute left-0 top-3 w-full h-1 bg-slate-100 rounded-full"></div>
                                <div class="absolute left-0 top-3 h-1 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-1000 ease-out" style="width: {{ $orderProgress }}"></div>
                                
                                <!-- Step 1: Confirmed -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 {{ $isDelivery ? 'w-1/5' : 'w-1/4' }}">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold text-white shadow-md bg-emerald-500 transition-transform duration-500 hover:scale-110"><i class="fa-solid fa-check"></i></div>
                                    <span class="text-[10px] font-bold text-slate-800 leading-tight">Confirmed</span>
                                </div>
                                
                                @if($isDelivery)
                                <!-- Step 2: Pick Up (Delivery Only) -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $orderStatusLevel >= 2 ? 'bg-emerald-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                        @if($orderStatusLevel >= 2) <i class="fa-solid fa-motorcycle"></i> @else 2 @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-center leading-tight {{ $orderStatusLevel >= 2 ? 'text-slate-800' : 'text-slate-400' }}">Rider Arriving</span>
                                </div>
                                @endif
                                
                                <!-- Step 3/2: Working -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 {{ $isDelivery ? 'w-1/5' : 'w-1/4' }}">
                                    @php $workingLevel = $isDelivery ? 3 : 2; @endphp
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $orderStatusLevel >= $workingLevel ? 'bg-emerald-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                        @if($orderStatusLevel >= $workingLevel) <i class="fa-solid fa-jug-detergent {{ $orderStatusLevel == $workingLevel ? 'fa-spin-pulse' : '' }}"></i> @else {{ $workingLevel }} @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-center leading-tight {{ $orderStatusLevel >= $workingLevel ? 'text-slate-800' : 'text-slate-400' }}">Working</span>
                                </div>
                                
                                <!-- Step 4/3: Ready/Delivering -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 {{ $isDelivery ? 'w-1/5' : 'w-1/4' }}">
                                    @php $deliveringLevel = $isDelivery ? 4 : 3; @endphp
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $orderStatusLevel >= $deliveringLevel ? 'bg-emerald-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                        @if($orderStatusLevel >= $deliveringLevel) 
                                            <i class="fa-solid {{ $isDelivery ? 'fa-motorcycle' : 'fa-box' }} {{ $orderStatusLevel == $deliveringLevel ? 'fa-bounce' : '' }}"></i> 
                                        @else {{ $deliveringLevel }} @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-center leading-tight {{ $orderStatusLevel >= $deliveringLevel ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ $isDelivery ? 'Delivering' : 'Ready' }}
                                    </span>
                                </div>
                                
                                <!-- Step 5/4: Completed -->
                                <div class="relative z-10 flex flex-col items-center gap-1.5 {{ $isDelivery ? 'w-1/5' : 'w-1/4' }}">
                                    @php $completedLevel = $isDelivery ? 5 : 4; @endphp
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $orderStatusLevel >= $completedLevel ? 'bg-emerald-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                        @if($orderStatusLevel >= $completedLevel) <i class="fa-solid fa-flag-checkered"></i> @else {{ $completedLevel }} @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-center leading-tight {{ $orderStatusLevel >= $completedLevel ? 'text-slate-800' : 'text-slate-400' }}">Completed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-slate-50 rounded-2xl border border-slate-200 border-dashed p-8 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-3 shadow-sm">
                    <i class="fa-solid fa-shirt text-2xl"></i>
                </div>
                <h4 class="font-bold text-slate-700 mb-1">No active orders</h4>
                <p class="text-sm text-slate-500 mb-4">You don't have any ongoing laundry orders at the moment.</p>
                <a href="{{ route('customer.booking') }}" class="inline-block bg-white text-blue-600 border border-blue-200 hover:border-blue-300 hover:bg-blue-50 px-4 py-2 rounded-xl text-sm font-bold transition-all">
                    Book a Pickup
                </a>
            </div>
        @endif
    </div>

</x-app-layout>
