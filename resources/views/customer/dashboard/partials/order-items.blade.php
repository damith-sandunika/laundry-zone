            <!-- Order Items -->
            <div class="bg-white rounded-lg sm:rounded-xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base"><i class="fa-solid fa-shirt text-blue-500 mr-2"></i> Order Items</h3>
                    @php
                        $displayItems = collect();
                        if ($order->items->count() > 0) {
                            foreach ($order->items as $item) {
                                $extrasArr = [];
                                $extrasTotal = 0;
                                foreach ($item->extras as $extra) {
                                    $extTotal = $extra->total_price ?? ($extra->unit_price * $extra->quantity) ?? 0;
                                    $extrasTotal += $extTotal;
                                    $extrasArr[] = (object)[
                                        'name' => $extra->extra->name ?? 'Extra',
                                        'quantity' => $extra->quantity,
                                        'price' => $extra->unit_price ?? $extra->price,
                                        'total' => $extTotal
                                    ];
                                }
                                
                                $base_total = $item->unit_price * $item->quantity;
                                $turnaround_charge = $item->total_price - $base_total;
                                
                                $base_total = ($item->unit_price * $item->quantity) + $turnaround_charge;
                                
                                $mainCategoryName = null;
                                $subCategoryName = null;
                                if ($item->serviceType && $item->serviceType->category) {
                                    $cat = $item->serviceType->category;
                                    if ($cat->parent) {
                                        $mainCategoryName = $cat->parent->name;
                                        $subCategoryName = $cat->name;
                                    } else {
                                        $mainCategoryName = $cat->name;
                                    }
                                }

                                $displayItems->push((object)[
                                    'name' => $item->serviceType->name ?? ($item->item_description ?? 'Service'),
                                    'main_category' => $mainCategoryName,
                                    'sub_category' => $subCategoryName,
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'base_total' => $base_total,
                                    'extras' => $extrasArr,
                                    'extras_total' => $extrasTotal,
                                    'total_price' => $item->total_price + $extrasTotal,
                                    'turnaround_name' => ($item->turnaroundType && !$item->turnaroundType->is_default) ? $item->turnaroundType->name : ((float)$item->custom_turnaround_percentage > 0 ? 'Express ('.(float)$item->custom_turnaround_percentage.'%)' : null),
                                    'turnaround_charge' => $turnaround_charge > 0 ? $turnaround_charge : 0,
                                ]);
                            }
                        } else if ($order->deliveryAppointment && $order->deliveryAppointment->cart_json) {
                            $cartArr = json_decode($order->deliveryAppointment->cart_json, true) ?? [];
                            foreach ($cartArr as $cartItem) {
                                $qty = (float)($cartItem['qty'] ?? 1);
                                $price = (float)($cartItem['base_price'] ?? 0);
                                
                                // Resolve turnaround
                                $ttName = null;
                                $ttCharge = 0;
                                $ttId = $cartItem['turnaround_type_id'] ?? null;
                                if ($ttId) {
                                    $tt = \App\Models\TurnaroundType::find($ttId);
                                    if ($tt && !$tt->is_default) {
                                        $ttName = $tt->name;
                                        $lineTotal = $price * $qty;
                                        if ($tt->markup_type === 'percentage') {
                                            $ttCharge = $lineTotal * ($tt->markup_value / 100);
                                        } else {
                                            $ttCharge = $tt->markup_value * $qty;
                                        }
                                    }
                                }
                                
                                // Resolve extras
                                $extrasArr = [];
                                $extrasTotal = 0;
                                foreach ($cartItem['selected_extras'] ?? [] as $se) {
                                    $exId = is_array($se) ? ($se['id'] ?? null) : $se;
                                    $exQty = is_array($se) ? ($se['qty'] ?? 1) : 1;
                                    $ex = \App\Models\Extra::find($exId);
                                    if ($ex) {
                                        $exTotal = $ex->price * $exQty;
                                        $extrasTotal += $exTotal;
                                        $extrasArr[] = (object)[
                                            'name' => $ex->name,
                                            'quantity' => $exQty,
                                            'price' => $ex->price,
                                            'total' => $exTotal
                                        ];
                                    }
                                }
                                
                                $base_total = ($price * $qty) + $ttCharge;
                                $total_price_with_extras = $base_total + $extrasTotal;

                                $mainCategoryName = null;
                                $subCategoryName = null;
                                $service = \App\Models\ServiceType::find($cartItem['id'] ?? null);
                                if ($service && $service->category) {
                                    $cat = $service->category;
                                    if ($cat->parent) {
                                        $mainCategoryName = $cat->parent->name;
                                        $subCategoryName = $cat->name;
                                    } else {
                                        $mainCategoryName = $cat->name;
                                    }
                                }

                                $displayItems->push((object)[
                                    'name' => $cartItem['name'] ?? 'Service',
                                    'main_category' => $mainCategoryName,
                                    'sub_category' => $subCategoryName,
                                    'quantity' => $qty,
                                    'unit_price' => $price,
                                    'base_total' => $base_total,
                                    'extras' => $extrasArr,
                                    'extras_total' => $extrasTotal,
                                    'total_price' => $total_price_with_extras,
                                    'turnaround_name' => $ttName,
                                    'turnaround_charge' => $ttCharge,
                                ]);
                            }
                        }
                    @endphp
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
                        
                        @php
                            $slips = \App\Models\PaymentTransaction::where('order_id', $order->id)->whereNotNull('receipt_path')->orderBy('created_at', 'desc')->get();
                            $hasPendingSlip = $slips->where('status', 'pending')->count() > 0;
                        @endphp
                        @if($slips->count() > 0)
                            <div class="mt-4 pt-4 border-t border-slate-200 space-y-3">
                                <span class="text-xs sm:text-sm font-bold text-slate-800 mb-1 block">Payment Slips Uploaded</span>
                                
                                @if($hasPendingSlip)
                                    <div class="bg-yellow-50 text-yellow-700 p-3 rounded-xl border border-yellow-200 text-xs font-bold flex items-center gap-2">
                                        <i class="fa-solid fa-hourglass-half text-yellow-500 text-lg"></i>
                                        <span>Your uploaded bank slip is currently being reviewed by our workshop admin. Please wait until it is approved. The paid amount will be updated shortly!</span>
                                    </div>
                                @endif

                                @foreach($slips as $slip)
                                    <div class="bg-white p-3 rounded-xl border {{ $slip->status === 'rejected' ? 'border-red-200' : ($slip->status === 'pending' ? 'border-yellow-200' : 'border-green-200') }} shadow-sm">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span class="text-sm font-bold text-slate-800">LKR {{ number_format($slip->amount, 2) }}</span>
                                                <span class="text-[10px] text-slate-400 font-bold block">{{ $slip->created_at->format('M d, Y - g:i A') }}</span>
                                            </div>
                                            <div class="flex flex-col items-end gap-2">
                                                @if($slip->status === 'pending')
                                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">Pending Review</span>
                                                @elseif($slip->status === 'completed')
                                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">Approved</span>
                                                @elseif($slip->status === 'rejected')
                                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide">Rejected</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($slip->status === 'rejected' && $slip->reject_reason)
                                            <div class="bg-red-50 text-red-800 p-2 rounded-lg text-xs font-bold mb-2 border border-red-100">
                                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Reason: {{ $slip->reject_reason }}
                                            </div>
                                        @endif
                                        
                                        <div class="mt-2 text-right">
                                            <a href="{{ \Illuminate\Support\Str::startsWith($slip->receipt_path, 'http') ? $slip->receipt_path : asset('storage/' . $slip->receipt_path) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-200 transition-colors">
                                                <i class="fa-solid fa-file-invoice mr-1.5"></i> View Slip
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
