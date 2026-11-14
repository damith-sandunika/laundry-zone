<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow relative overflow-hidden">
    <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-purple-100 to-transparent rounded-bl-3xl -z-0"></div>
    <div class="relative z-10 flex justify-between items-start mb-4">
        <div>
            <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-1 rounded-md mb-2 inline-block">
                <i class="fa-solid fa-truck-pickup"></i> Pickup Request
            </span>
            <h4 class="font-bold text-slate-800">#{{ $appointment->order->order_number ?? $appointment->id }}</h4>
        </div>
        <div class="text-right flex flex-col items-end gap-2">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
            </span>
            <a href="{{ route('customer.appointments.show', $appointment->order_number ?? $appointment->id) }}" class="text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 px-3 py-1.5 rounded-lg transition-colors inline-block">
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
                $pickupTask = $appointment->deliveryTasks()->where('task_type', 'pickup')->latest()->first();
                
                if (in_array($appointment->status, ['picked_up', 'at_branch', 'processing'])) {
                    $appProgress = '50%'; $appLevel = 3;
                } elseif (in_array($appointment->status, ['ready', 'out_for_delivery'])) {
                    $appProgress = '75%'; $appLevel = 4;
                } elseif ($appointment->status === 'delivered') {
                    $appProgress = '100%'; $appLevel = 5;
                } elseif ($pickupTask) {
                    if (in_array($pickupTask->status, ['assigned', 'accepted'])) { $appProgress = '15%'; $appLevel = 2; }
                    elseif (in_array($pickupTask->status, ['on_way', 'arrived'])) { $appProgress = '25%'; $appLevel = 2; }
                    elseif ($pickupTask->status === 'completed') { $appProgress = '50%'; $appLevel = 3; }
                } elseif ($appointment->status === 'rider_assigned') {
                    $appProgress = '15%'; $appLevel = 2;
                } elseif ($appointment->status === 'confirmed') {
                    $appProgress = '5%'; $appLevel = 1.5;
                }
            @endphp
            <div class="absolute left-0 top-3 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-1000 ease-out" style="width: {{ $appProgress }}"></div>
            
            <!-- Step 1: Requested / Confirmed -->
            <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $appLevel >= 1.5 ? 'bg-emerald-500 text-white' : 'bg-blue-600 text-white' }}">
                    @if($appLevel >= 1.5) <i class="fa-solid fa-check"></i> @else 1 @endif
                </div>
                <span class="text-[10px] font-bold text-center leading-tight text-slate-800">{{ $appLevel >= 1.5 ? 'Confirmed' : 'Requested' }}</span>
            </div>
            
            <!-- Step 2: Rider Arriving -->
            <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $appLevel > 2 ? 'bg-emerald-500 text-white' : ($appLevel == 2 ? 'bg-blue-500 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400') }}">
                    @if($appLevel > 2) <i class="fa-solid fa-check"></i> @else <i class="fa-solid fa-motorcycle {{ in_array($appointment->status, ['on_way', 'arrived']) ? 'animate-bounce' : '' }}"></i> @endif
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

