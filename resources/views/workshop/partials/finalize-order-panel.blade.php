            <!-- ===================== Right: Order Panel ===================== -->
            <div
                class="flex-1 lg:flex-[0.8] bg-white rounded-[24px] border border-slate-200/60 shadow-sm flex flex-col overflow-hidden relative">

                <!-- Customer Section -->
                <div class="p-4 border-b-2 border-slate-100 bg-white z-20 flex flex-col gap-2">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-widest">Customer
                            Details</span>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl relative flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-black text-sm shadow-md shrink-0">
                            {{ substr($dispatch->order->customer->name ?? $order->customer->name ?? 'C', 0, 1) }}
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <span
                                class="text-[14px] font-black text-blue-900 block truncate">{{ $dispatch->order->customer->name ?? $order->customer->name ?? 'Unknown Customer' }}</span>
                            <span
                                class="text-[11px] font-bold text-blue-600 block">{{ $dispatch->order->customer->phone ?? $order->customer->phone ?? 'No Phone' }}</span>
                        </div>
                    </div>
                    @if((isset($dispatch) && $dispatch->order->notes) || (isset($order) && $order->notes))
                        <div
                            class="mt-1 p-2 bg-yellow-50 text-yellow-800 text-xs font-bold rounded-lg border border-yellow-200 flex gap-2">
                            <i class="fa-solid fa-note-sticky mt-0.5"></i> {{ $dispatch->order->notes ?? $order->notes }}
                        </div>
                    @endif
                </div>

                @include('workshop.partials.finalize-order-panel-cart')
                @include('workshop.partials.finalize-order-panel-summary')

            </div>
