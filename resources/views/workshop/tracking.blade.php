<x-app-layout>
    <x-slot name="header">
        <div class="ph-title">📍 Workshop Tracking — Order #{{ $order->id }}</div>
        <div class="ph-sub">{{ $order->customer->name ?? '—' }}</div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-5">

        {{-- Order Summary --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Customer</div>
                <div class="font-bold text-slate-800">{{ $order->customer->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Order Status</div>
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-blue-100 text-blue-700">
                    {{ ucwords(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>

        @if($dispatch)
        {{-- Timeline --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-6">Workshop Journey</h3>
            <div class="relative">
                <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-slate-100"></div>
                <div class="space-y-6">
                    @foreach($dispatch->steps() as $index => $step)
                    @php
                        $statuses = ['dispatched_to_workshop','received_at_workshop','processing','completed_at_workshop','returned_to_branch','received_at_branch'];
                        $currentIndex = array_search($dispatch->status, $statuses);
                        $stepIndex = $index;
                        $isDone = $stepIndex <= $currentIndex;
                        $isCurrent = $stepIndex === $currentIndex;
                    @endphp
                    <div class="flex items-start gap-4 relative">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl z-10 flex-shrink-0 shadow-sm
                            {{ $isDone ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400' }}
                            {{ $isCurrent ? 'ring-4 ring-blue-200' : '' }}">
                            {{ $isDone ? '✓' : $step['icon'] }}
                        </div>
                        <div class="flex-1 pt-2">
                            <div class="text-sm font-black {{ $isDone ? 'text-slate-800' : 'text-slate-400' }}">
                                {{ $step['label'] }}
                            </div>
                            @if($step['time'])
                                <div class="text-xs text-slate-400 font-medium mt-0.5">
                                    {{ \Carbon\Carbon::parse($step['time'])->format('d M Y, H:i') }}
                                </div>
                            @elseif($isCurrent)
                                <div class="text-xs text-blue-500 font-bold mt-0.5 animate-pulse">In Progress...</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($dispatch->dispatch_notes || $dispatch->workshop_notes)
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-4">Notes</h3>
            @if($dispatch->dispatch_notes)
                <div class="mb-3">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Branch Notes (on dispatch)</div>
                    <div class="text-sm text-slate-600">{{ $dispatch->dispatch_notes }}</div>
                </div>
            @endif
            @if($dispatch->workshop_notes)
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Workshop Notes</div>
                    <div class="text-sm text-slate-600">{{ $dispatch->workshop_notes }}</div>
                </div>
            @endif
        </div>
        @endif

        {{-- Branch Admin: Mark received back --}}
        @if(auth()->user()->isAdmin() && $dispatch->status === 'returned_to_branch')
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-4">Confirm Order Received from Workshop</h3>
            <form method="POST" action="{{ route('workshop.receivedAtBranch', $dispatch) }}">
                @csrf
                <textarea name="workshop_notes" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3" rows="3" placeholder="Optional: Add workshop completion notes..."></textarea>
                <button type="submit" class="w-full py-3 bg-green-600 text-white font-black text-sm rounded-xl hover:bg-green-700 transition-colors">
                    ✅ Confirm Received at Branch
                </button>
            </form>
        </div>
        @endif

        @else
            <div class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
                <div class="text-4xl mb-3">📭</div>
                <div class="text-sm font-bold text-slate-500">This order has not been dispatched to workshop yet.</div>
            </div>
        @endif

        <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
            ← Back to Order
        </a>

    </div>
</x-app-layout>
