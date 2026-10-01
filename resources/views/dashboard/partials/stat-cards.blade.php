{{-- ── STAT CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            @php
                $cards = [
                    ['label' => 'New Orders', 'value' => $stats['active_orders'], 'sub' => 'Processing now', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'link' => route('orders.index', ['tab' => 'received'])],
                    ['label' => 'Processing', 'value' => $stats['pending_deliveries'], 'sub' => 'Awaiting customer', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'link' => route('orders.index', ['tab' => 'processing'])],
                    ['label' => 'Completed', 'value' => $stats['completed_today'], 'sub' => 'Delivered orders', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50', 'icon' => 'M5 13l4 4L19 7', 'link' => route('orders.index', ['tab' => 'done'])],
                    ['label' => 'Total Payments Collected', 'value' => 'Rs.' . number_format($stats['total_payments_collected'] ?? 0, 0), 'sub' => null, 'color' => 'text-violet-600', 'bg' => 'bg-violet-50', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'link' => null],
                ];
            @endphp
            @foreach($cards as $index => $card)
                @if($card['link'])
                <a href="{{ $card['link'] }}" class="bg-white/70 backdrop-blur-md rounded-2xl border border-slate-200/60 p-5 flex justify-between items-center shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 animate-slide-up block" style="animation-delay: {{ $index * 50 }}ms;">
                @else
                <div class="bg-white/70 backdrop-blur-md rounded-2xl border border-slate-200/60 p-5 flex justify-between items-center shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 animate-slide-up" style="animation-delay: {{ $index * 50 }}ms;">
                @endif
                    <div>
                        <p class="text-[9px] sm:text-[10.5px] font-extrabold text-slate-500 uppercase tracking-widest mb-1 leading-tight sm:leading-normal whitespace-normal">{{ $card['label'] }}</p>
                        <p class="text-lg sm:text-3xl font-black text-slate-900 tracking-tight leading-none mb-1 sm:mb-2">{{ $card['value'] }}</p>
                        @if($card['label'] === 'Total Payments Collected')
                            <p class="inline-block px-1.5 sm:px-2.5 py-0.5 text-[8px] sm:text-[10px] font-bold text-violet-600 bg-violet-50 rounded-md uppercase tracking-wide leading-tight">Cash, Card & Bank</p>
                        @elseif($card['sub'])
                            <p class="inline-block px-1.5 sm:px-2.5 py-0.5 text-[8px] sm:text-[10px] font-bold {{ $card['color'] }} {{ $card['bg'] }} rounded-md uppercase tracking-wide leading-tight">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                    <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-xl {{ $card['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 {{ $card['color'] }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                @if($card['link'])
                </a>
                @else
                </div>
                @endif
            @endforeach
        </div>
