<div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;margin-top:16px;">
    <div class="mb-4 px-2">
        <h3 class="text-sm font-black text-slate-800 mb-4">Activity & Notes</h3>
        
        @php
            $pendingRequests = $order->correctionRequests()->where('status', 'pending')->get();
            $conversionLogs = $order->statusHistory->filter(function($log) {
                if (!$log->note) return false;
                $note = strtolower($log->note);
                return str_contains($note, 'convert') || 
                       str_contains($note, 'correction') || 
                       str_contains($note, 'overpayment action requested') || 
                       str_contains($note, 'refund') || 
                       str_contains($note, 'store credit');
            })->sortByDesc('created_at');
        @endphp

        <!-- Pending Requests Section -->
        @if($pendingRequests->count() > 0 && in_array(auth()->user()->role, ['admin', 'super_admin', 'branch_admin', 'workshop_admin']))
            <div class="space-y-3 mb-6">
                @foreach($pendingRequests as $req)
                    <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;border-radius:12px;padding:14px;">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 shadow-sm border border-amber-200">
                                ⏳
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-amber-900 text-sm mb-1">Approval Required</h4>
                                <p class="text-xs text-amber-800 font-semibold mb-1">{{ $req->reason }}</p>
                                @if($req->type === 'overpayment_handling')
                                    <p class="text-[11px] text-amber-700 bg-amber-200/50 p-2 rounded-lg border border-amber-200 font-bold mb-2">
                                        Action: {{ ucfirst($req->new_data['action'] ?? 'Unknown') }} | Amount: Rs. {{ number_format($req->new_data['amount'] ?? 0, 2) }}
                                    </p>
                                @endif
                                <p class="text-[10px] font-bold text-amber-600 mb-3">Requested by: {{ $req->requestedBy->name ?? 'System' }}</p>

                                <div class="flex gap-2">
                                    <form action="{{ route('corrections.approve', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-1.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-[11px] font-black rounded-lg shadow-sm shadow-emerald-500/30 transition-all flex items-center gap-1">
                                            ✅ Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('corrections.reject', $req->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-1.5 bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 text-[11px] font-black rounded-lg shadow-sm transition-all flex items-center gap-1">
                                            ❌ Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <hr class="border-slate-100 mb-6">
        @endif

        <!-- Past Activity Log Section -->
        @if($conversionLogs->count() > 0)
            <div class="space-y-4">
                @foreach($conversionLogs as $log)
                    <div class="flex items-start gap-3 pb-3 border-b border-slate-50 last:border-0 last:pb-0">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                            📝
                        </div>
                        <div class="flex-1">
                            <div class="flex items-baseline justify-between gap-2">
                                <span class="font-bold text-slate-700 text-sm capitalize">
                                    Note
                                </span>
                                <span class="text-[11px] font-semibold text-slate-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') }}
                                </span>
                            </div>
                            
                            @php
                                $rawNote = $log->note;
                                $mainNote = $rawNote;
                                $reqBy = null;
                                $appBy = null;
                                $reqDate = null;
                                $appDate = null;
                                
                                if (preg_match('/\[Requested by:\s*(.*?)\s+at\s+(.*?)\s*\|\s*Approved by:\s*(.*?)\s+at\s+(.*?)\]/', $rawNote, $matches)) {
                                    $reqBy = $matches[1];
                                    $reqDate = $matches[2];
                                    $appBy = $matches[3];
                                    $appDate = $matches[4];
                                    $mainNote = trim(str_replace($matches[0], '', $rawNote));
                                }
                                elseif (preg_match('/\[Requested by:\s*(.*?)\s*\|\s*Approved by:\s*(.*?)\]/', $rawNote, $matches)) {
                                    $reqBy = $matches[1];
                                    $reqDate = \Carbon\Carbon::parse($log->created_at)->format('h:i A');
                                    $appBy = $matches[2];
                                    $appDate = \Carbon\Carbon::parse($log->created_at)->format('h:i A');
                                    $mainNote = trim(str_replace($matches[0], '', $rawNote));
                                }
                            @endphp
                            
                            @if($mainNote)
                                <p class="text-sm text-slate-700 mt-1.5 font-bold leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-200 shadow-sm">
                                    {!! nl2br(e($mainNote)) !!}
                                </p>
                            @endif
                            
                            @if($reqBy && $appBy)
                                <div class="mt-2 flex flex-col gap-1.5 pl-2">
                                    <div class="flex items-center justify-between text-[11px] font-black">
                                        <div class="flex items-center gap-1.5 text-blue-600">
                                            <span class="bg-blue-100 text-blue-500 rounded-full w-4 h-4 flex items-center justify-center">↑</span> 
                                            <span>REQUESTED BY: <span class="text-blue-800">{{ $reqBy }}</span></span>
                                        </div>
                                        <span class="text-slate-500 font-semibold bg-slate-100 px-2 py-0.5 rounded">{{ $reqDate }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[11px] font-black">
                                        <div class="flex items-center gap-1.5 text-emerald-600">
                                            <span class="bg-emerald-100 text-emerald-500 rounded-full w-4 h-4 flex items-center justify-center">✓</span> 
                                            <span>APPROVED BY: <span class="text-emerald-800">{{ $appBy }}</span></span>
                                        </div>
                                        <span class="text-slate-500 font-semibold bg-slate-100 px-2 py-0.5 rounded">{{ $appDate }}</span>
                                    </div>
                                </div>
                            @elseif($log->changedBy)
                                <p class="text-[11px] text-slate-400 font-bold mt-2 pl-2">
                                    By: {{ $log->changedBy->name }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            @if($pendingRequests->count() == 0)
                <p class="text-sm text-slate-500 font-medium">No activity recorded yet.</p>
            @endif
        @endif
    </div>
</div>
