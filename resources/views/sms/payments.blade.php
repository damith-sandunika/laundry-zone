<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 animate-slide-up">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">SMS Gateway Credits & Bundle Payments</h2>
                <p class="text-[13px] font-bold text-slate-500 mt-1">
                    Purchase message bundles, view transaction history, and manage remaining credits
                </p>
            </div>
            <div>
                <a href="{{ route('sms.sender') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Message Logs
                </a>
            </div>
        </div>
    </x-slot>

    <!-- Top Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <!-- Live Balance -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-600">Available SMS Credits</span>
            <div class="text-3xl font-black text-blue-700 mt-1">
                {{ number_format($smsBalance) }}
                <span class="text-xs font-bold text-slate-400">SMS</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Current unit rate: Rs. {{ number_format($smsRate, 2) }} / SMS</div>
        </div>

        <!-- Total SMS Purchased -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600">Total SMS Purchased</span>
            <div class="text-3xl font-black text-emerald-600 mt-1">
                {{ number_format($totalPurchased) }}
                <span class="text-xs font-bold text-slate-400">SMS</span>
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Historical bundle activations</div>
        </div>

        <!-- Total Amount Invested -->
        <div class="bg-white/80 backdrop-blur-md rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">Total Spent on SMS</span>
            <div class="text-3xl font-black text-slate-900 mt-1">
                Rs. {{ number_format($totalSpent, 0) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Settled payments for SMS bundles</div>
        </div>
    </div>

    <!-- Purchase SMS Bundle Plans -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Available SMS Top-Up Bundles</h3>
                <p class="text-[12px] font-semibold text-slate-500">Contact SaaS platform administration to activate any of the bundles below or make direct payment</p>
            </div>
            <div class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-200">
                Official Gateway Masking Included
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @forelse($packages as $pkg)
                <div class="rounded-2xl border-2 {{ $pkg->is_featured ? 'border-blue-500 bg-blue-50/20 shadow-md shadow-blue-500/10 relative' : 'border-slate-100 hover:border-blue-500/50 hover:shadow-lg hover:shadow-slate-100' }} p-5 flex flex-col justify-between transition-all duration-300">
                    @if($pkg->badge)
                        <span class="absolute -top-3 right-4 px-2.5 py-0.5 rounded-full {{ $pkg->is_featured ? 'bg-blue-600 text-white' : 'bg-slate-800 text-white' }} text-[10px] font-black uppercase tracking-wider">
                            {{ $pkg->badge }}
                        </span>
                    @endif

                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest {{ $pkg->is_featured ? 'text-blue-600' : 'text-slate-400' }}">{{ $pkg->name }}</span>
                        <h4 class="text-xl font-black text-slate-900 mt-1">{{ number_format($pkg->sms_count) }} SMS</h4>
                        <div class="text-2xl font-black text-blue-600 mt-2">Rs. {{ number_format($pkg->price, 0) }}</div>
                        <p class="text-xs text-slate-500 mt-1">@ Rs. {{ number_format($pkg->rate_per_sms, 2) }} per SMS</p>
                        
                        @if(!empty($pkg->features))
                            <ul class="text-xs text-slate-600 space-y-1.5 mt-4 pt-4 border-t {{ $pkg->is_featured ? 'border-blue-100' : 'border-slate-100' }}">
                                @foreach($pkg->features as $feat)
                                    <li class="flex items-center gap-2">✔ {{ $feat }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="mt-5 pt-3 border-t {{ $pkg->is_featured ? 'border-blue-100' : 'border-slate-100' }} text-center">
                        <span class="text-xs {{ $pkg->is_featured ? 'font-black text-blue-700' : 'font-bold text-slate-500' }}">
                            Contact Admin to Top-Up
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-8 text-center text-slate-400 text-sm">
                    No SMS packages currently published. Please contact platform support.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Purchase & Activation History -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-base font-black text-slate-900 tracking-tight">Credit Top-Up History</h3>
            <p class="text-[12px] font-semibold text-slate-500">Records of SMS credits allocated by SaaS Administration</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-[10px] font-extrabold uppercase tracking-widest text-slate-400 border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Date Added</th>
                        <th class="px-4 py-3.5">SMS Added</th>
                        <th class="px-4 py-3.5">Unit Rate</th>
                        <th class="px-4 py-3.5">Amount Paid</th>
                        <th class="px-4 py-3.5">Payment Method / Ref</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($credits as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 text-xs font-bold text-slate-900">
                                {{ $c->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-4 font-black text-emerald-600 text-xs">
                                + {{ number_format($c->sms_count) }} SMS
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-600">
                                Rs. {{ number_format($c->rate_per_sms, 2) }}
                            </td>
                            <td class="px-4 py-4 font-black text-slate-900 text-xs">
                                Rs. {{ number_format($c->amount_paid, 0) }}
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">
                                <span class="font-bold text-slate-700">{{ $c->payment_method }}</span>
                                @if($c->payment_reference)
                                    <div class="text-[10px] font-mono text-slate-400">{{ $c->payment_reference }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ ucfirst($c->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-slate-500">
                                {{ $c->notes ?? 'Bundle Top-up' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                No SMS credit top-up history found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

