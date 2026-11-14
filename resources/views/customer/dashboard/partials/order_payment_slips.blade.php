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

