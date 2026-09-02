<x-customer-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-3">
            <div>
                <h2 class="font-black text-2xl text-slate-800 tracking-tight">Order History</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">View all your past and current laundry orders.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </x-slot>

    @if(count($unifiedOrders) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($unifiedOrders as $orderObj)
                @if($orderObj->type === 'appointment')
                    <!-- Pickup Request Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-purple-100 to-transparent rounded-bl-3xl -z-0"></div>
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-1 rounded-md mb-2 inline-block">
                                    <i class="fa-solid fa-truck-pickup"></i> Pickup Request
                                </span>
                                <h4 class="font-bold text-slate-800">{{ $orderObj->order_number }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    {{ $orderObj->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $orderObj->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ !in_array($orderObj->status, ['pending', 'cancelled']) ? 'bg-blue-100 text-blue-700' : '' }}
                                ">
                                    {{ $orderObj->status === 'pending' ? 'Requested' : ucfirst($orderObj->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="relative z-10 flex justify-between items-end">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $orderObj->created_at->format('M d, Y h:i A') }}</p>
                                <p class="text-xs text-slate-500">Requested On</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($orderObj->status !== 'cancelled')
                                    <button type="button" onclick="alert('Payment will be activated once the laundry is weighed and the final bill is generated.');" class="text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                        Pay Now
                                    </button>
                                @endif
                                <a href="{{ route('customer.appointments.show', $orderObj->order_number ?? $orderObj->id) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Regular/Delivery Order Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
                        @if($orderObj->is_delivery)
                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-3xl -z-0"></div>
                        @else
                            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-slate-100 to-transparent rounded-bl-3xl -z-0"></div>
                        @endif
                        
                        <div class="relative z-10 flex justify-between items-start mb-4">
                            <div>
                                <span class="text-xs font-bold {{ $orderObj->is_delivery ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }} px-2 py-1 rounded-md mb-2 inline-block">
                                    @if($orderObj->is_delivery)
                                        <i class="fa-solid fa-truck-fast"></i> Delivery Order
                                    @else
                                        <i class="fa-solid fa-store"></i> Walk-in Order
                                    @endif
                                </span>
                                <h4 class="font-bold text-slate-800">{{ $orderObj->order_number }}</h4>
                                <p class="text-xs font-bold text-slate-500 mt-0.5">{{ $orderObj->items_count }} Items</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    @if($orderObj->status === 'delivered' || $orderObj->status === 'completed') bg-green-100 text-green-700
                                    @elseif($orderObj->status === 'cancelled') bg-red-100 text-red-700
                                    @elseif($orderObj->status === 'ready') bg-blue-100 text-blue-700
                                    @else bg-yellow-100 text-yellow-700
                                    @endif
                                ">
                                    {{ ucwords(str_replace('_', ' ', $orderObj->status)) }}
                                </span>
                            </div>
                        </div>
                        <div class="relative z-10 flex justify-between items-end">
                            <div>
                                <p class="text-sm font-black text-slate-800">LKR {{ number_format($orderObj->total, 2) }}</p>
                                <p class="text-xs text-slate-500 font-bold">{{ $orderObj->created_at->format('M d, Y') }}</p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @php
                                    $hasPendingGlobally = \App\Models\PaymentTransaction::where('order_id', $orderObj->id)->where('status', 'pending')->exists();
                                @endphp
                                @if($orderObj->payment_status === 'unpaid' && !in_array($orderObj->status, ['delivered', 'cancelled']) && !$hasPendingGlobally)
                                    <a href="{{ route('customer.orders.show', $orderObj->order_number) }}" class="text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
                                        Pay Now
                                    </a>
                                @elseif($hasPendingGlobally)
                                    <span class="text-[10px] font-bold text-yellow-700 bg-yellow-50 px-2 py-1.5 rounded-lg flex items-center gap-1 border border-yellow-200">
                                        <i class="fa-solid fa-hourglass-half"></i> Pending
                                    </span>
                                @elseif($orderObj->payment_status === 'paid')
                                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1.5 rounded-lg flex items-center gap-1 border border-green-100">
                                        <i class="fa-solid fa-check"></i> Paid
                                    </span>
                                @endif
                                
                                <a href="{{ route('customer.orders.show', $orderObj->order_number) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-block">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <div class="bg-slate-50 rounded-2xl border border-slate-200 border-dashed p-12 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-slate-300 mx-auto mb-4 shadow-sm border border-slate-100">
                <i class="fa-solid fa-box-open text-3xl text-slate-400"></i>
            </div>
            <h4 class="font-black text-slate-700 text-lg mb-2">No orders found</h4>
            <p class="text-slate-500 mb-6 font-medium">You don't have any past or current laundry orders.</p>
            <a href="{{ route('customer.booking') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white shadow-md shadow-blue-200 px-6 py-3 rounded-xl font-bold transition-all">
                Book a Pickup Now
            </a>
        </div>
    @endif
</x-customer-layout>
