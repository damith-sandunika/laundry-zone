@if($order->correctionRequests->count() > 0)
    <div class="flex flex-col gap-3 mb-6">
        @foreach($order->correctionRequests->sortByDesc('created_at') as $cr)
            <div class="p-4 rounded-xl flex items-start justify-between gap-4 text-[13px] border-2 shadow-sm
                        {{ $cr->status === 'pending' ? 'bg-amber-50 text-amber-900 border-amber-200 shadow-amber-100/50' :
                ($cr->status === 'approved' ? 'bg-emerald-50 text-emerald-900 border-emerald-200 shadow-emerald-100/50' :
                    'bg-red-50 text-red-900 border-red-200 shadow-red-100/50') }}">
                <div class="flex-1 w-full">
                    <strong class="uppercase text-[11px] tracking-wider">{{ ucfirst($cr->status) }} Correction Request
                        <span class="opacity-75 font-semibold">({{ str_replace('_', ' ', $cr->type) }})</span></strong><br>
                    <span class="text-slate-600">Requested by <strong
                            class="text-slate-800">{{ $cr->requestedBy->name ?? 'Unknown' }}</strong> on
                        {{ $cr->created_at->format('M d, Y h:i A') }}</span><br>
                    <div class="mt-1.5 p-2.5 bg-white/60 rounded-lg border border-slate-200/50 italic text-slate-700">
                        "{{ $cr->reason }}"</div>

                    @if(is_array($cr->old_data) && is_array($cr->new_data))
                        @php
                            $oldTotal = $cr->old_data['total'] ?? $order->total;
                            $newTotal = $cr->new_data['calculated_total'] ?? $cr->old_data['total'] ?? $order->total;
                            $oldMethod = $cr->old_data['payment_method'] ?? $order->payment_method;
                            $newMethod = $cr->new_data['payment_method'] ?? $cr->old_data['payment_method'] ?? $order->payment_method;
                        @endphp
                        <div
                            class="mt-3 bg-white/70 backdrop-blur-sm p-3 rounded-xl text-[12px] border border-slate-200/60 shadow-sm w-full space-y-1.5">
                            @if((isset($cr->old_data['total']) || isset($cr->new_data['calculated_total'])) && $oldTotal != $newTotal)
                                <div class="flex justify-between items-center border-b border-slate-200/50 pb-1.5">
                                    <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Total Amount:</strong>
                                    <div class="flex items-center gap-2">
                                        <span class="text-red-500 line-through font-bold">Rs.{{ number_format($oldTotal, 2) }}</span>
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                        <strong class="text-emerald-600 font-black text-[13px]">Rs.{{ number_format($newTotal, 2) }}</strong>
                                    </div>
                                </div>
                            @endif

                            @if($cr->type === 'items_change' || $cr->type === 'order_change')
                                @include('orders.partials.corrections.items_diff', ['cr' => $cr, 'serviceTypesMap' => $serviceTypesMap ?? []])
                            @elseif($cr->type === 'convert_to_delivery')
                                <div class="mt-2 text-[11px]">
                                    <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Updates:</strong>
                                    <div class="mt-1 space-y-1.5 bg-white/50 rounded-lg p-2 border border-slate-100">
                                        <div class="flex items-start gap-1.5">
                                            <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Address</span>
                                            <div class="flex-1 font-bold text-slate-800">{{ $cr->new_data['delivery_address'] ?? 'N/A' }}</div>
                                        </div>
                                        <div class="flex items-start gap-1.5">
                                            <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Fee</span>
                                            <div class="flex-1 font-bold text-slate-800">Rs.{{ number_format($cr->new_data['delivery_fee'] ?? 0, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($cr->type === 'convert_to_pickup')
                                <div class="mt-2 text-[11px]">
                                    <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Updates:</strong>
                                    <div class="mt-1 space-y-1.5 bg-white/50 rounded-lg p-2 border border-slate-100">
                                        <div class="flex items-start gap-1.5">
                                            <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Pickup</span>
                                            <div class="flex-1 font-bold text-slate-800">Converted to Customer Pickup</div>
                                        </div>
                                        <div class="flex items-start gap-1.5">
                                            <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Fee Removed</span>
                                            <div class="flex-1 font-bold text-slate-800">Rs.{{ number_format($cr->old_data['delivery_fee'] ?? 0, 2) }} deducted</div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($cr->type === 'payment_change')
                                @include('orders.partials.corrections.payment_diff', ['cr' => $cr])
                            @elseif($cr->type === 'overpayment_handling')
                                @php
                                    $opAction = $cr->new_data['action'] ?? 'store_credit';
                                    $opAmount = $cr->new_data['amount'] ?? 0;
                                @endphp
                                <div class="mt-2 text-[11px]">
                                    <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Overpayment Action:</strong>
                                    <div class="mt-1 space-y-1.5 bg-white/50 rounded-lg p-2 border border-slate-100">
                                        <div class="flex items-center gap-1.5">
                                            <span class="bg-indigo-100 text-indigo-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0">
                                                {{ $opAction === 'refund' ? 'Cash Refund' : 'Store Credit' }}
                                            </span>
                                            <div class="flex-1 font-bold text-slate-800">
                                                Rs.{{ number_format($opAmount, 2) }}
                                                <span class="font-normal text-slate-500 text-[10px]">
                                                    ({{ $opAction === 'refund' ? 'Refunded in Cash to customer' : 'Added to Customer Store Credit' }})
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                    @if($cr->status !== 'pending' && $cr->approvedBy)
                        <div class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-wide">
                            {{ ucfirst($cr->status) }} by <strong class="text-slate-700">{{ $cr->approvedBy->name }}</strong> on
                            {{ $cr->resolved_at ? $cr->resolved_at->format('M d, Y h:i A') : 'Unknown' }}
                        </div>
                    @endif
                </div>

                @if($cr->status === 'pending' && in_array(auth()->user()->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']))
                    <div class="flex flex-col gap-2 shrink-0">
                        <form action="{{ route('corrections.approve', $cr->id) }}" method="POST"
                            onsubmit="return confirm('Approve this request? This will permanently alter the order details.')">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[11px] uppercase tracking-wide rounded-lg shadow-sm transition-colors flex items-center justify-center gap-1.5"><svg
                                    class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>Approve</button>
                        </form>
                        <form action="{{ route('corrections.reject', $cr->id) }}" method="POST"
                            onsubmit="return confirm('Reject this request?')">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-black text-[11px] uppercase tracking-wide rounded-lg transition-colors flex items-center justify-center gap-1.5"><svg
                                    class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>Reject</button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
