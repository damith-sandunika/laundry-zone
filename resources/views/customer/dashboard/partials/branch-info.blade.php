            <!-- Branch Info -->
            <div class="bg-white rounded-lg sm:rounded-xl border border-slate-100 shadow-sm p-4 sm:p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-blue-100 to-transparent rounded-bl-[40px] -z-0"></div>
                <h3 class="font-bold text-slate-800 mb-4 relative z-10 text-sm sm:text-base"><i class="fa-solid fa-shop text-blue-500 mr-2"></i> Branch Details</h3>
                <div class="relative z-10">
                    <p class="font-bold text-slate-800 text-xs sm:text-sm">{{ $order->branch->name }}</p>
                    <p class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $order->branch->address }}</p>
                    @if($order->branch->phone)
                        <p class="text-[11px] sm:text-xs font-bold text-slate-500 mt-1"><i class="fa-solid fa-phone mr-1"></i> {{ $order->branch->phone }}</p>
                    @endif
                </div>
            </div>
