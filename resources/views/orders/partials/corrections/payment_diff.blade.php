@php
    $isDel = !empty($cr->new_data['is_delete']);
    $txId = $cr->new_data['transaction_id'] ?? ($cr->old_data['transaction_id'] ?? null);
    $targetTx = $txId ? \App\Models\PaymentTransaction::withTrashed()->find($txId) : null;
    
    $newAmt = isset($cr->new_data['amount']) ? (float)$cr->new_data['amount'] : null;
    $oldAmt = isset($cr->old_data['amount']) && $cr->old_data['amount'] !== null 
        ? (float)$cr->old_data['amount'] 
        : ($targetTx ? (float)($targetTx->original_amount ?? $targetTx->amount) : null);

    $oldPm = $cr->old_data['transaction_method'] ?? ($targetTx ? ($targetTx->original_payment_method ?? $targetTx->payment_method) : ($cr->old_data['payment_method'] ?? 'cash'));
    $newPm = $cr->new_data['payment_method'] ?? ($targetTx ? $targetTx->payment_method : $oldPm);
    $delReason = $cr->new_data['delete_reason'] ?? $cr->reason;
@endphp
<div class="mt-2 text-[11px]">
    <strong class="text-slate-500 uppercase tracking-wide text-[10px]">Payment Details:</strong>
    <div class="mt-1 space-y-1.5 bg-white/50 rounded-lg p-2 border border-slate-100">
        @if($isDel)
            <div class="flex items-center gap-1.5">
                <span class="bg-rose-100 text-rose-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0">Deleted</span>
                <div class="flex-1 font-bold text-rose-800">
                    Payment Slip {{ $txId ? '#' . $txId : '' }} Deleted
                    @if($oldAmt !== null)
                        <span class="text-rose-600 line-through font-bold">(Was Rs.{{ number_format($oldAmt, 2) }} - {{ ucfirst($oldPm) }})</span>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center gap-1.5">
                <span class="bg-blue-100 text-blue-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0">Amount</span>
                <div class="flex-1 flex items-center gap-1.5 flex-wrap">
                    @if($oldAmt !== null && $newAmt !== null && (float)$oldAmt !== (float)$newAmt)
                        <span class="text-slate-400 line-through font-bold">Rs.{{ number_format($oldAmt, 2) }}</span>
                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        <span class="font-black text-slate-800">Rs.{{ number_format($newAmt, 2) }}</span>
                        <span class="text-[9.5px] font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                            {{ $newAmt > $oldAmt ? '+' : '' }}Rs.{{ number_format($newAmt - $oldAmt, 2) }}
                        </span>
                    @elseif($newAmt !== null)
                        <span class="font-bold text-slate-800">Rs.{{ number_format($newAmt, 2) }}</span>
                    @elseif($oldAmt !== null)
                        <span class="font-bold text-slate-800">Rs.{{ number_format($oldAmt, 2) }}</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <span class="bg-purple-100 text-purple-700 text-[9px] font-black px-1.5 py-0.5 rounded uppercase shrink-0">Method</span>
                <div class="flex-1 flex items-center gap-1.5">
                    @if(strtolower($oldPm) !== strtolower($newPm))
                        <span class="text-slate-400 line-through font-bold">{{ ucfirst($oldPm) }}</span>
                        <svg class="w-2.5 h-2.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        <span class="font-bold text-emerald-700">{{ ucfirst($newPm) }}</span>
                    @else
                        <span class="font-semibold text-slate-700">{{ ucfirst($newPm) }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

