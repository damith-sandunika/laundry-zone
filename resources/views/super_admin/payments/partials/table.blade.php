<!-- ══════════ PAYMENTS INVOICE TABLE (LIGHT THEME) ══════════ -->
<div class="overflow-x-auto">
    <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
            <tr>
                <th class="px-5 py-3.5">Invoice #</th>
                <th class="px-4 py-3.5">Company</th>
                <th class="px-4 py-3.5">Plan & Fees</th>
                <th class="px-4 py-3.5">Total Amount</th>
                <th class="px-4 py-3.5">Period</th>
                <th class="px-4 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
            @foreach($subscriptions as $sub)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="px-5 py-4 font-mono text-xs font-bold text-slate-900">
                        {{ $sub->invoice_number }}
                        <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $sub->payment_method ?? 'Direct' }}</div>
                    </td>
                    <td class="px-4 py-4">
                        <a href="{{ route('super_admin.companies.show', $sub->tenant) }}" class="font-bold text-slate-900 hover:text-blue-600 block">
                            {{ $sub->tenant->name ?? 'Company' }}
                        </a>
                        <div class="text-xs font-mono text-slate-400">{{ $sub->tenant->subdomain ?? '' }}</div>
                    </td>
                    <td class="px-4 py-4 text-xs">
                        <div class="font-bold text-slate-800">{{ $sub->plan_name }} (Rs. {{ number_format($sub->base_amount, 0) }})</div>
                    </td>
                    <td class="px-4 py-4 font-black text-sm text-emerald-600">
                        Rs. {{ number_format($sub->total_amount, 0) }}
                    </td>
                    <td class="px-4 py-4 text-xs text-slate-500">
                        {{ $sub->billing_period_start?->format('M Y') ?? 'Monthly' }}
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $sub->status_badge['bg'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sub->status_badge['dot'] }}"></span>
                            <span>{{ $sub->status_badge['label'] }}</span>
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right space-x-2">
                        <button @click="activeSub = {{ json_encode($sub) }}; statusModal = true"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Status
                        </button>
                        <a href="{{ route('super_admin.payments.invoice', $sub) }}" target="_blank"
                            class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition">
                            Invoice
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
    {{ $subscriptions->links() }}
</div>
