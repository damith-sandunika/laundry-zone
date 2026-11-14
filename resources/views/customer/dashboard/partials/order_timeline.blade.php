<div class="mt-4 pt-4 border-t border-slate-100 relative z-10">
    <div class="flex items-center justify-between relative">
        <div class="absolute left-0 top-3 w-full h-1 bg-slate-100 rounded-full"></div>
        <div class="absolute left-0 top-3 h-1 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full transition-all duration-1000 ease-out" style="width: {{ $orderProgress }}"></div>
        
        <!-- Step 1: Requested / Confirmed -->
        <div class="relative z-10 flex flex-col items-center gap-1.5 {{ $isDelivery ? 'w-1/5' : 'w-1/4' }}">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-transform duration-500 hover:scale-110 {{ $orderStatusLevel >= 1.5 ? 'bg-emerald-500 text-white' : 'bg-blue-600 text-white' }}">
                @if($orderStatusLevel >= 1.5) <i class="fa-solid fa-check"></i> @else 1 @endif
            </div>
            <span class="text-[10px] font-bold text-center leading-tight text-slate-800">{{ $orderStatusLevel >= 1.5 ? 'Confirmed' : 'Requested' }}</span>
        </div>
        
        @if($isDelivery)
        <!-- Step 2: Pick Up (Delivery Only) -->
        <div class="relative z-10 flex flex-col items-center gap-1.5 w-1/5">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold shadow-md transition-all duration-500 {{ $orderStatusLevel > 2 ? 'bg-emerald-500 text-white' : ($orderStatusLevel == 2 ? 'bg-blue-600 text-white hover:scale-110' : 'bg-white border-2 border-slate-200 text-slate-400') }}">
                @if($orderStatusLevel > 2) <i class="fa-solid fa-check"></i> @else <i class="fa-solid fa-motorcycle {{ $orderStatusLevel == 2 ? 'animate-bounce' : '' }}"></i> @endif
            </div>
            <span class="text-[10px] font-bold text-center leading-tight {{ $orderStatusLevel >= 2 ? 'text-slate-800' : 'text-slate-400' }}">{{ $orderStatusLevel > 2 ? 'Picked Up' : 'Rider Arriving' }}</span>
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
                {{ $isDelivery ? 'Delivering' : 'Ready to Pickup' }}
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

