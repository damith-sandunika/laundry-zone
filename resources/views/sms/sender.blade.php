<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">SMS Messages & Gateway Sender</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">
                    Real-time outbound SMS dispatch logs, gateway status, and delivery tracking
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sms.payments') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm shadow-blue-500/20 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Top-Up SMS Credits
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Low / Zero Balance Alert -->
    @if($smsBalance <= 0)
        <div class="mb-6 p-5 rounded-2xl bg-rose-50 border border-rose-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-slide-up">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-black text-rose-900">SMS Gateway Paused (0 Credits Remaining)</h4>
                    <p class="text-xs font-semibold text-rose-700 mt-0.5">
                        Your SMS bundle has run out. Outbound notifications to customers are currently paused. Please purchase an SMS bundle to resume automated notifications.
                    </p>
                </div>
            </div>
            <a href="{{ route('sms.payments') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-black rounded-xl shadow-sm shadow-rose-600/30 whitespace-nowrap text-center">
                Top-Up Now
            </a>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- Balance Card -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest {{ $smsBalance > 0 ? 'text-blue-600' : 'text-rose-600' }}">Remaining Credits</span>
            <div class="text-2xl sm:text-3xl font-black {{ $smsBalance > 0 ? 'text-blue-700' : 'text-rose-600' }} mt-1">
                {{ number_format($smsBalance) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Rate: Rs. {{ number_format($smsRate, 2) }} / SMS</div>
        </div>

        <!-- Sent Messages -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Successfully Sent</span>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600 mt-1">
                {{ number_format($totalSent) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Delivered to client</div>
        </div>

        <!-- Failed Messages -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-rose-600">Failed / Paused</span>
            <div class="text-2xl sm:text-3xl font-black text-rose-600 mt-1">
                {{ number_format($totalFailed) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Delivery issues / 0 credit</div>
        </div>

        <!-- Total Dispatched -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Total Triggered</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                {{ number_format($totalMessages) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">All time activity</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="p-4 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Outbound SMS History</h3>
                <p class="text-[12px] font-semibold text-slate-500">Live records of SMS messages sent to customers</p>
            </div>
            <form method="GET" action="{{ route('sms.sender') }}" class="flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search phone or text..." class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Recipient</th>
                        <th class="px-4 py-3.5">Notification Type</th>
                        <th class="px-4 py-3.5">SMS Message Content</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Order</th>
                        <th class="px-6 py-3.5 text-right">Time Sent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-black text-slate-900">
                                {{ $log->phone }}
                            </td>
                            <td class="px-4 py-4 text-xs font-bold text-slate-700">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-[11px]">
                                    {{ ucfirst(str_replace('_', ' ', $log->type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600 max-w-md">
                                <p class="line-clamp-2 leading-relaxed">{{ $log->message }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @if($log->status === 'sent')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Sent
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $log->response }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Failed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-xs font-mono font-bold text-blue-600">
                                @if($log->order)
                                    <a href="{{ route('orders.show', $log->order->order_number) }}" class="hover:underline">
                                        #{{ $log->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-500 whitespace-nowrap">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No outbound SMS logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>

