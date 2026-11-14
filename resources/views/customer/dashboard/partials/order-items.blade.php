            <!-- Order Items -->
            <div class="bg-white rounded-lg sm:rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i> Order Items</h3>
                    @include('customer.dashboard.partials.order_items_calculator', ['order' => $order])
                    <span class="text-sm font-bold text-slate-500">{{ $displayItems->count() }} Items</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @if($displayItems->count() > 0)
                        @foreach($displayItems as $item)
                            <div class="p-4 sm:p-5 flex flex-col hover:bg-slate-50 transition-colors gap-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-tshirt"></i>
                                        </div>
                                        <div>
                                            <div class="flex flex-col">
                                                @if(isset($item->main_category) && $item->main_category)
                                                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mb-0.5">
                                                        {{ $item->main_category }} @if($item->sub_category) <span class="text-slate-300 mx-0.5">/</span> {{ $item->sub_category }} @endif
                                                    </span>
                                                @endif
                                                <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                    {{ $item->name }}
                                                    @if($item->turnaround_name)
                                                        <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 border border-orange-200">
                                                            ⚡ {{ $item->turnaround_name }}
                                                        </span>
                                                    @endif
                                                </h4>
                                            </div>
                                            <p class="text-xs font-bold text-slate-500 mt-1">Qty: {{ $item->quantity }} &times; LKR {{ number_format($item->unit_price, 2) }}</p>
                                        </div>
                                    </div>
                                    <div class="font-bold text-slate-800">
                                        LKR {{ number_format($item->base_total, 2) }}
                                    </div>
                                </div>
                                
                                @if(count($item->extras) > 0)
                                    <div class="ml-14 pl-4 border-l-2 border-indigo-100 space-y-2">
                                        @foreach($item->extras as $extra)
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex items-center gap-2 text-indigo-600 font-bold">
                                                    <i class="fa-solid fa-plus text-[9px]"></i> {{ $extra->name }} <span class="text-indigo-400">(&times; {{ $extra->quantity }})</span>
                                                </div>
                                                <div class="font-bold text-indigo-500">
                                                    + LKR {{ number_format($extra->total_price ?? $extra->total, 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="flex items-center justify-between text-sm pt-2 mt-2 border-t border-indigo-50/50">
                                            <div class="font-bold text-slate-700 text-xs uppercase tracking-wide">Item Subtotal</div>
                                            <div class="font-bold text-slate-800">LKR {{ number_format($item->total_price, 2) }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="p-6 text-center text-slate-400 font-bold text-sm">
                            No services found.
                        </div>
                    @endif
                </div>
                @php
                    // If order has no items yet (not finalized), compute from cart_json
                    $effSubtotal = $order->subtotal;
                    $effDiscount = $order->discount;
                    $effAdditional = $order->additional ?? 0;
                    $effDeliveryFee = $order->delivery_fee ?? 0;
                    $effTotal = $order->total;

                    if ($order->items->count() === 0 && $order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
                        $cartArr = json_decode($order->deliveryAppointment->cart_json, true) ?? [];
                        $calcSubtotal = 0;
                        foreach ($cartArr as $ci) {
                            $lineQty = (float)($ci['qty'] ?? 1);
                            $linePrice = (float)($ci['base_price'] ?? 0);
                            $lineTotal = $linePrice * $lineQty;
                            
                            // Add turnaround charge
                            $ttId = $ci['turnaround_type_id'] ?? null;
                            if ($ttId) {
                                $tt = \App\Models\TurnaroundType::find($ttId);
                                if ($tt && !$tt->is_default) {
                                    if ($tt->markup_type === 'percentage') {
                                        $lineTotal += $lineTotal * ($tt->markup_value / 100);
                                    } else {
                                        $lineTotal += $tt->markup_value * $lineQty;
                                    }
                                }
                            }
                            
                            // Add extras
                            foreach ($ci['selected_extras'] ?? [] as $se) {
                                $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                                $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                                $ex = \App\Models\Extra::find($exId);
                                if ($ex) $lineTotal += $ex->price * $exQty;
                            }
                            
                            $calcSubtotal += $lineTotal;
                        }
                        $effSubtotal = $calcSubtotal;
                        $effTotal = $calcSubtotal + $effDeliveryFee;
                    }
                @endphp
                <div class="bg-slate-50 p-4 sm:p-6 border-t border-slate-100">
                    <div class="space-y-3">
                        @if($effSubtotal > 0)
                        <div class="flex justify-between text-xs sm:text-sm font-bold text-slate-500">
                            <span>Subtotal</span>
                            <span>LKR {{ number_format($effSubtotal, 2) }}</span>
                        </div>
                        @endif
                        @if($effDiscount > 0)
                            <div class="flex justify-between text-xs sm:text-sm font-bold text-green-600">
                                <span>Discount</span>
                                <span>- LKR {{ number_format($effDiscount, 2) }}</span>
                            </div>
                        @endif
                        @if($effAdditional > 0)
                            <div class="flex justify-between text-xs sm:text-sm font-bold text-red-500">
                                <span>Additional Charges</span>
                                <span>+ LKR {{ number_format($effAdditional, 2) }}</span>
                            </div>
                        @endif
                        @if($effDeliveryFee >= 0)
                            <div class="flex justify-between text-xs sm:text-sm font-bold text-slate-700">
                                <span>Delivery Fee</span>
                                <span>{{ $effDeliveryFee > 0 ? '+ ' : '' }}LKR {{ number_format($effDeliveryFee, 2) }}</span>
                            </div>
                        @endif
                        <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                            <span class="font-bold text-sm sm:text-base text-slate-800">Total Amount</span>
                            <span class="font-bold text-base sm:text-lg text-slate-800">LKR {{ number_format($effTotal, 2) }}</span>
                        </div>
                        @if($order->paid_amount > 0)
                            <div class="flex justify-between text-xs sm:text-sm font-bold text-green-600 mt-2">
                                <span>Paid Amount</span>
                                <span>- LKR {{ number_format($order->paid_amount, 2) }}</span>
                            </div>
                        @endif
                        
                        @if($effTotal - $order->paid_amount > 0)
                            <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row justify-between sm:items-center mt-2 bg-blue-50 -mx-4 sm:-mx-6 px-4 sm:px-6 py-4 border-b border-blue-100 gap-3">
                                <div>
                                    <span class="block font-bold text-blue-900 text-sm sm:text-base">Balance to Pay</span>
                                    <span class="font-bold text-xl sm:text-2xl text-blue-700">LKR {{ number_format(max(0, $effTotal - $order->paid_amount), 2) }}</span>
                                </div>
                                @if(!$hasPendingSlipGlobally)
                                <button type="button" x-data @click="$dispatch('open-payment-modal')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-bold shadow-md transition-colors text-sm flex items-center justify-center gap-2 w-full sm:w-auto">
                                    <i class="fa-solid fa-credit-card"></i> Pay Now
                                </button>
                                @endif
                            </div>
                        @else
                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center mt-2 bg-green-50 -mx-4 sm:-mx-6 px-4 sm:px-6 py-3 border-b border-green-100">
                                <span class="font-bold text-green-900 text-sm sm:text-base">Payment Status</span>
                                <span class="font-bold text-base sm:text-lg text-green-700"><i class="fa-solid fa-check-circle mr-1"></i> Fully Paid</span>
                            </div>
                        @endif
                        
                        @include('customer.dashboard.partials.order_payment_slips', ['order' => $order])
                    </div>
                </div>
            </div>
