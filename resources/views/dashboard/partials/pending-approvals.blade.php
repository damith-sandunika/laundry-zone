{{-- Pending Approvals --}}
        @if(isset($pendingCorrections) && $pendingCorrections->count() > 0 && in_array(auth()->user()->role, ['admin', 'branch_admin']))
            <div class="mb-6 bg-amber-50 rounded-3xl border border-amber-200 shadow-sm overflow-hidden animate-slide-up p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-amber-900 tracking-tight">Pending Approvals</h3>
                        <p class="text-[12px] font-bold text-amber-700">{{ $pendingCorrections->count() }} action(s) require your review</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    @foreach($pendingCorrections as $cr)
                    <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm flex items-center justify-between">
                        <div>
                            <div class="text-[13px] font-black text-slate-800">
                                Order <a href="{{ route('orders.show', $cr->order->order_number) }}" class="text-blue-600 hover:underline">#{{ $cr->order->order_number }}</a> 
                                - <span class="uppercase tracking-wider text-[11px] text-amber-600 font-bold bg-amber-100 px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', $cr->type) }} Request</span>
                            </div>
                            <div class="text-[11px] font-bold text-slate-500 mt-1">
                                Requested by {{ $cr->requestedBy->name ?? 'System' }} &bull; {{ $cr->created_at->diffForHumans() }}
                            </div>
                            <div class="text-[12px] font-medium text-slate-600 italic mt-2 border-l-2 border-amber-300 pl-2">
                                "{{ $cr->reason }}"
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('orders.show', $cr->order->order_number) }}" class="px-4 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 text-[12px] font-bold rounded-xl transition-colors">Review</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif
