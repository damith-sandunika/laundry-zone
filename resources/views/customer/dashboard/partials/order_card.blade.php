@php
    $deliveryAppointment = \App\Models\DeliveryAppointment::where('order_id', $order->id)->first();
    $isDelivery = $deliveryAppointment ? true : false;
@endphp
<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
    @if($isDelivery)
        <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-purple-100 to-transparent rounded-bl-3xl -z-0"></div>
    @endif
    <div class="flex justify-between items-start mb-4 relative z-10">
        <div>
            @if($isDelivery)
                <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-1 rounded-md mb-2 inline-block">
                    <i class="fa-solid fa-truck-pickup"></i> Pickup Request
                </span>
                <h4 class="font-bold text-slate-800 mb-1">{{ $order->order_number }}</h4>
                @if($deliveryAppointment->pickup_scheduled_at)
                    <div class="text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-md mb-2 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-day text-indigo-500"></i> 
                        {{ \Carbon\Carbon::parse($deliveryAppointment->pickup_scheduled_at)->format('M d, Y') }} 
                        @if($deliveryAppointment->pickupTimePeriod)
                            &middot; {{ \Carbon\Carbon::parse($deliveryAppointment->pickupTimePeriod->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($deliveryAppointment->pickupTimePeriod->end_time)->format('h:i A') }}
                        @endif
                    </div>
                @endif
                <div class="text-xs font-bold text-slate-500 mb-2">{{ $order->items->count() }} Items &middot; 
                    @if($order->paid_amount > 0)
                        <span class="text-blue-600">Bal: LKR {{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span> <span class="text-slate-400 font-medium text-[10px]">(Total: {{ number_format($order->total, 2) }})</span>
                    @else
                        <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span>
                    @endif
                </div>
            @else
                <span class="text-xs font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded-md mb-2 inline-block">
                    {{ $order->order_number }}
                </span>
                <h4 class="font-bold text-slate-800">{{ $order->items->count() }} Items &middot; 
                    @if($order->paid_amount > 0)
                        <span class="text-blue-600">Bal: LKR {{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span> <span class="text-slate-400 font-medium text-[10px]">(Total: {{ number_format($order->total, 2) }})</span>
                    @else
                        <span class="text-blue-600">LKR {{ number_format($order->total, 2) }}</span>
                    @endif
                </h4>
            @endif
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
            @if(!$order->payment_approved && $order->requested_advance > 0 || in_array($order->payment_status, ['unpaid', 'pending', 'partial']))
                <a href="{{ route('customer.orders.show', $order->order_number) }}" class="text-[10px] font-bold uppercase tracking-wider text-white bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg transition-colors shadow-sm animate-pulse">
                    Pay Now
                </a>
            @else
                <a href="{{ route('customer.orders.show', $order->order_number) }}" class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors shadow-sm">
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
        
        $hasStartedWashing = !in_array($order->status, ['pending', 'received', 'dispatched_to_workshop', 'received_at_workshop']);
        $isAwaitingPayment = !$order->payment_approved && ($order->requested_advance - $order->paid_amount) > 0 && !$hasStartedWashing;
        
        $logicalStatus = $order->status;
        
        if ($isDelivery) {
            $apptStatus = $deliveryAppointment ? $deliveryAppointment->status : null;
            
            if(in_array($logicalStatus, ['delivered', 'completed'])) { 
                $orderProgress = '100%'; $orderStatusLevel = 5; 
            }
            elseif(in_array($logicalStatus, ['returned_from_workshop', 'completed_at_workshop', 'returned_to_branch', 'ready', 'done']) || $apptStatus === 'out_for_delivery') { 
                $orderProgress = '75%'; $orderStatusLevel = 4; 
            }
            elseif(in_array($logicalStatus, ['processing', 'washing', 'drying', 'ironing', 'folding', 'edited'])) { 
                $orderProgress = '50%'; $orderStatusLevel = 3; 
            }
            elseif(in_array($logicalStatus, ['received', 'dispatched_to_workshop', 'received_at_workshop']) || in_array($apptStatus, ['picked_up', 'at_branch'])) { 
                $orderProgress = '25%'; $orderStatusLevel = 2.5;
            }
            elseif (in_array($logicalStatus, ['rider_assigned']) || in_array($apptStatus, ['rider_assigned', 'on_way', 'arrived'])) {
                $orderProgress = '15%'; $orderStatusLevel = 2;
            }
            elseif (in_array($logicalStatus, ['confirmed']) || in_array($apptStatus, ['confirmed'])) {
                $orderProgress = '5%'; $orderStatusLevel = 1.5;
            }
            elseif (in_array($logicalStatus, ['pending']) && in_array($apptStatus, ['pending', null])) {
                $orderProgress = '0%'; $orderStatusLevel = 1;
            } else {
                $orderProgress = '0%'; $orderStatusLevel = 1;
            }
        } else {
            if(in_array($logicalStatus, ['delivered', 'completed'])) { 
                $orderProgress = '100%'; $orderStatusLevel = 4; 
            }
            elseif(in_array($logicalStatus, ['returned_from_workshop', 'completed_at_workshop', 'returned_to_branch', 'ready', 'done'])) { 
                $orderProgress = '66%'; $orderStatusLevel = 3; 
            }
            elseif(in_array($logicalStatus, ['processing', 'washing', 'drying', 'ironing', 'folding', 'edited'])) { 
                $orderProgress = '33%'; $orderStatusLevel = 2; 
            }
            elseif(in_array($logicalStatus, ['received', 'dispatched_to_workshop', 'confirmed'])) { 
                $orderProgress = '10%'; $orderStatusLevel = 1.5;
            }
            elseif(in_array($logicalStatus, ['pending'])) { 
                $orderProgress = '0%'; $orderStatusLevel = 1;
            }
        }
    @endphp
    
    @if($isAwaitingPayment)
        <div class="mt-4 pt-5 border-t border-slate-100 relative z-10">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow p-5 text-center flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-left text-white">
                    <h4 class="font-bold text-lg mb-1"><i class="fa-solid fa-circle-exclamation text-yellow-300 animate-pulse mr-1"></i> Action Required</h4>
                    <p class="text-blue-100 text-sm font-medium">To start processing your laundry, please pay the requested advance of <strong class="text-white bg-blue-800/50 px-1.5 py-0.5 rounded">LKR {{ number_format($order->requested_advance - $order->paid_amount, 2) }}</strong></p>
                </div>
                <a href="{{ route('customer.orders.show', $order->order_number) }}" class="shrink-0 w-full sm:w-auto inline-block bg-yellow-400 hover:bg-yellow-300 text-yellow-900 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                    <i class="fa-solid fa-bolt mr-1"></i> PAY NOW
                </a>
            </div>
        </div>
    @else
        @include('customer.dashboard.partials.order_timeline', ['isDelivery' => $isDelivery, 'orderStatusLevel' => $orderStatusLevel, 'orderProgress' => $orderProgress])
    @endif
</div>
