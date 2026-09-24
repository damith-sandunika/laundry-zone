@if($order->correctionRequests->whereIn('type', ['items_change', 'order_change'])->count() > 0)
        <div class="flex flex-col gap-3 mb-6">
            @foreach($order->correctionRequests->whereIn('type', ['items_change', 'order_change'])->sortByDesc('created_at') as $cr)
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
                                            <span
                                                class="text-red-500 line-through font-bold">Rs.{{ number_format($oldTotal, 2) }}</span>
                                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3">
                                                </path>
                                            </svg>
                                            <strong
                                                class="text-emerald-600 font-black text-[13px]">Rs.{{ number_format($newTotal, 2) }}</strong>
                                        </div>
                                    </div>
                                @endif

                                @if($cr->type === 'items_change' || $cr->type === 'order_change')
                                    @php
                                        $oldItems = is_array($cr->old_data) ? ($cr->old_data['items'] ?? $cr->old_data['cart'] ?? []) : [];
                                        $newItems = is_array($cr->new_data) ? ($cr->new_data['cart'] ?? $cr->new_data['items'] ?? []) : [];
                                        
                                        $diff = [];
                                        $oldColl = collect($oldItems);
                                        $newColl = collect($newItems);
                                        
                                        // Find Added and Modified
                                        foreach ($newColl as $newI) {
                                            $stId = $newI['service_type_id'] ?? null;
                                            $name = $newI['name'] ?? null;
                                            
                                            $oldI = null;
                                            if ($stId) {
                                                $oldI = $oldColl->firstWhere('service_type_id', $stId);
                                            } else {
                                                $oldI = $oldColl->first(function($i) use ($name) {
                                                    return ($i['item_description'] ?? $i['name'] ?? null) === $name;
                                                });
                                            }
                                            
                                            $newQty = $newI['qty'] ?? $newI['quantity'] ?? 1;
                                            
                                            if (!$oldI) {
                                                $diff[] = ['type' => 'added', 'item' => $newI];
                                            } else {
                                                $oldQty = $oldI['quantity'] ?? $oldI['qty'] ?? 1;
                                                
                                                // Check for turnaround changes
                                                $oldTtId = $oldI['turnaround_type_id'] ?? null;
                                                $newTtId = $newI['turnaround_type_id'] ?? null;
                                                if ($oldTtId === 'custom') $oldTtId = null;
                                                if ($newTtId === 'custom') $newTtId = null;
                                                
                                                $oldTtCustom = (float)($oldI['custom_turnaround_percentage'] ?? 0);
                                                $newTtCustom = (float)($newI['custom_turnaround_percentage'] ?? 0);
                                                $ttChanged = ($oldTtId != $newTtId) || ($oldTtCustom != $newTtCustom);
                                                
                                                $oldExtras = $oldI['selected_extras'] ?? $oldI['extras'] ?? [];
                                                $newExtras = $newI['selected_extras'] ?? $newI['extras'] ?? [];
                                                
                                                $oldExMap = [];
                                                foreach ($oldExtras as $ex) {
                                                    $id = $ex['extra_id'] ?? $ex['id'] ?? null;
                                                    if ($id) $oldExMap[$id] = (float)($ex['quantity'] ?? $ex['qty'] ?? 1);
                                                }
                                                $newExMap = [];
                                                foreach ($newExtras as $ex) {
                                                    $id = $ex['extra_id'] ?? $ex['id'] ?? null;
                                                    if ($id) $newExMap[$id] = (float)($ex['quantity'] ?? $ex['qty'] ?? 1);
                                                }
                                                
                                                $extrasChanged = false;
                                                if (count($oldExMap) != count($newExMap)) {
                                                    $extrasChanged = true;
                                                } else {
                                                    foreach ($oldExMap as $id => $qty) {
                                                        if (!isset($newExMap[$id]) || $newExMap[$id] != $qty) {
                                                            $extrasChanged = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                
                                                if ($oldQty != $newQty || $ttChanged || $extrasChanged) {
                                                    $diff[] = [
                                                        'type' => 'changed', 
                                                        'item' => $newI, 
                                                        'old_qty' => $oldQty, 
                                                        'tt_changed' => $ttChanged, 
                                                        'extras_changed' => $extrasChanged,
                                                        'new_tt_id' => $newTtId,
                                                        'new_tt_custom' => $newTtCustom
                                                    ];
                                                }
                                            }
                                        }
                                        
                                        // Find Removed
                                        foreach ($oldColl as $oldI) {
                                            $stId = $oldI['service_type_id'] ?? null;
                                            $name = $oldI['item_description'] ?? $oldI['name'] ?? null;
                                            
                                            $newI = null;
                                            if ($stId) {
                                                $newI = $newColl->firstWhere('service_type_id', $stId);
                                            } else {
                                                $newI = $newColl->first(function($i) use ($name) {
                                                    return ($i['name'] ?? null) === $name;
                                                });
                                            }
                                            
                                            if (!$newI) {
                                                $diff[] = ['type' => 'removed', 'item' => $oldI];
                                            }
                                        }
                                        
                                        $oldFee = $cr->old_data['delivery_fee'] ?? null;
                                        $newFee = $cr->new_data['delivery_fee'] ?? null;
                                        $oldDisc = $cr->old_data['discount_percentage'] ?? null;
                                        $newDisc = $cr->new_data['discount_percentage'] ?? null;
                                    @endphp
                                    
                                    @if(count($diff) > 0 || ($oldFee !== null && $newFee !== null && $oldFee != $newFee) || ($oldDisc !== null && $newDisc !== null && $oldDisc != $newDisc))
                                        <div class="mt-2 text-[11px]">
                                            <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Updates:</strong>
                                            <div class="mt-1 space-y-1.5 bg-white/50 rounded-lg p-2 border border-slate-100">
                                                @foreach($diff as $change)
                                                    @php
                                                        $item = $change['item'];
                                                        $stId = $item['service_type_id'] ?? null;
                                                        $stInfo = $stId && isset($serviceTypesMap[$stId]) ? $serviceTypesMap[$stId] : null;
                                                        $itemName = $stInfo ? $stInfo['name'] : ($item['item_description'] ?? $item['name'] ?? 'Service');
                                                        $catName = $stInfo['category'] ?? null;
                                                        $subCatName = $stInfo['subcategory'] ?? null;
                                                        $catStr = $catName ? ($catName . ($subCatName ? ' › ' . $subCatName : '')) : null;
                                                    @endphp
                                                    <div class="flex items-start gap-1.5">
                                                        @if($change['type'] === 'added')
                                                            <span class="bg-emerald-100 text-emerald-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Add</span>
                                                            <div class="flex-1">
                                                                @if($catStr)
                                                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $catStr }}</div>
                                                                @endif
                                                                <div class="font-bold text-slate-800">{{ (float)($item['qty'] ?? $item['quantity'] ?? 1) }}x {{ $itemName }}</div>
                                                            </div>
                                                        @elseif($change['type'] === 'removed')
                                                            <span class="bg-red-100 text-red-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Del</span>
                                                            <div class="flex-1">
                                                                @if($catStr)
                                                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $catStr }}</div>
                                                                @endif
                                                                <div class="font-bold text-slate-400 line-through">{{ (float)($item['quantity'] ?? $item['qty'] ?? 1) }}x {{ $itemName }}</div>
                                                            </div>
                                                        @elseif($change['type'] === 'changed')
                                                            <span class="bg-amber-100 text-amber-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Mod</span>
                                                            <div class="flex-1">
                                                                @if($catStr)
                                                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wide">{{ $catStr }}</div>
                                                                @endif
                                                                @php $newQty = (float)($item['qty'] ?? $item['quantity'] ?? 1); @endphp
                                                                <div class="flex items-center gap-1.5">
                                                                    @if((float)$change['old_qty'] != $newQty)
                                                                    <span class="text-slate-400 line-through font-bold">{{ (float)$change['old_qty'] }}x</span>
                                                                    <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                                    @endif
                                                                    <span class="font-bold text-slate-800">{{ $newQty }}x {{ $itemName }}</span>
                                                                </div>
                                                                @if(!empty($change['tt_changed']) || !empty($change['extras_changed']))
                                                                @php
                                                                    $ttDesc = 'Turnaround changed';
                                                                    if (!empty($change['tt_changed'])) {
                                                                        if (!empty($change['new_tt_custom'])) {
                                                                            $ttDesc = 'Express (' . (float)$change['new_tt_custom'] . '%)';
                                                                        } elseif (!empty($change['new_tt_id'])) {
                                                                            $tt = \App\Models\TurnaroundType::find($change['new_tt_id']);
                                                                            if ($tt) {
                                                                                $ttDesc = 'Turnaround: ' . $tt->name;
                                                                            }
                                                                        } else {
                                                                            $ttDesc = 'Turnaround removed';
                                                                        }
                                                                    }
                                                                @endphp
                                                                <div class="text-[9px] font-semibold text-amber-600 mt-0.5">
                                                                    {{ implode(' • ', array_filter([
                                                                        !empty($change['tt_changed']) ? $ttDesc : null,
                                                                        !empty($change['extras_changed']) ? 'Extras changed' : null
                                                                    ])) }}
                                                                </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                
                                                @if($oldFee !== null && $newFee !== null && $oldFee != $newFee)
                                                    <div class="flex items-start gap-1.5 {{ count($diff) > 0 ? 'mt-1 border-t border-slate-200/50 pt-1' : '' }}">
                                                        <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Fee</span>
                                                        <div class="flex-1 flex items-center gap-1.5">
                                                            <span class="text-slate-500 font-bold text-[10px]">Delivery Fee:</span>
                                                            <span class="text-slate-400 line-through font-bold">Rs.{{ number_format($oldFee, 2) }}</span>
                                                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                            <span class="font-bold text-slate-800">Rs.{{ number_format($newFee, 2) }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if($oldDisc !== null && $newDisc !== null && $oldDisc != $newDisc)
                                                    <div class="flex items-start gap-1.5 {{ count($diff) > 0 || ($oldFee !== null && $newFee !== null && $oldFee != $newFee) ? 'mt-1 border-t border-slate-200/50 pt-1' : '' }}">
                                                        <span class="bg-purple-100 text-purple-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0 mt-0.5">Disc</span>
                                                        <div class="flex-1 flex items-center gap-1.5">
                                                            <span class="text-slate-500 font-bold text-[10px]">Discount:</span>
                                                            <span class="text-slate-400 line-through font-bold">{{ $oldDisc }}%</span>
                                                            <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                                            <span class="font-bold text-slate-800">{{ $newDisc }}%</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
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
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
