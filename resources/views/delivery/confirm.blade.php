<x-app-layout>
    <x-slot name="header">
        <div class="ph-title">🚚 Assign Rider — #{{ $appointment->order->order_number ?? $appointment->id }}</div>
        <div class="ph-sub">{{ $appointment->customer->name ?? 'Unknown Customer' }} • {{ $appointment->pickup_address }}</div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-5">

        {{-- Appointment Details --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-4">Appointment Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Customer</div>
                    <div class="font-bold text-slate-800">{{ $appointment->customer->name ?? 'Unknown Customer' }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Phone</div>
                    <div class="font-bold text-slate-800">{{ $appointment->contact_phone }}</div>
                </div>
                <div class="col-span-2">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Pickup Address</div>
                    <div class="font-semibold text-slate-700">📍 {{ $appointment->pickup_address }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Scheduled Pickup</div>
                    <div class="font-bold text-slate-800">{{ $appointment->pickup_scheduled_at?->format('d M Y, H:i') }}</div>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Status</div>
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-yellow-100 text-yellow-700">
                        {{ $appointment->statusLabel() }}
                    </span>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Payment</div>
                    <div class="font-bold text-slate-800">{{ $appointment->payment_method ?? 'Cash' }} <span class="text-xs text-slate-500">({{ str_replace('_', ' ', $appointment->payment_timing ?? 'pay_on_delivery') }})</span></div>
                </div>
                <div class="col-span-2">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Delivery Preference</div>
                    @if($appointment->delivery_preference === 'pickup_from_branch')
                        <div class="font-semibold text-purple-600">🏪 Customer will pick up from branch</div>
                    @else
                        <div class="font-semibold text-blue-600">🏠 Return Delivery: {{ $appointment->delivery_address ?? 'Same as pickup' }}</div>
                    @endif
                </div>
                @if($appointment->notes)
                <div class="col-span-2">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-1">Notes</div>
                    <div class="text-slate-600">{{ $appointment->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Assign Rider Form --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="text-sm font-black text-slate-800 mb-4">Assign Pickup Rider</h3>

            @if($riders->isEmpty())
                <div class="text-center py-8">
                    <div class="text-3xl mb-2">😕</div>
                    <div class="text-sm font-bold text-slate-500">No riders available for this branch.</div>
                    <p class="text-xs text-slate-400 mt-1">Add riders from Staff Management and assign them the 'Rider' role.</p>
                </div>
            @else
                <form method="POST" action="{{ route('delivery.assignRider', $appointment) }}">
                    @csrf
                    <div class="space-y-3 mb-5">
                        @foreach($riders as $rider)
                        <label class="flex items-center gap-3 p-4 border-2 border-slate-100 rounded-xl cursor-pointer hover:border-blue-300 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="rider_id" value="{{ $rider->id }}" class="text-blue-600" required>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-sm font-black text-white">
                                {{ strtoupper(substr($rider->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800">{{ $rider->name }}</div>
                                <div class="text-xs text-slate-400">{{ $rider->phone ?? $rider->email }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-black text-sm rounded-xl hover:shadow-lg hover:shadow-blue-500/30 transition-all">
                        🏍️ Assign Rider & Confirm
                    </button>
                </form>
            @endif
        </div>

        <a href="{{ route('delivery.index') }}" class="flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
            ← Back to Deliveries
        </a>

    </div>
</x-app-layout>

