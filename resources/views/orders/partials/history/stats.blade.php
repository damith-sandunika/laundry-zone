<!-- Summary Stats -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Order Value</span>
        <span
            class="text-lg font-black text-slate-800 mt-1">Rs.{{ number_format($stats['total_order_value'], 2) }}</span>
        <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">🚶 Walk-in</span>
                <span
                    class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['walk_in'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">🚚 Delivery</span>
                <span
                    class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['delivery'], 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">Pending / Due</span>
        <span class="text-lg font-black text-amber-600 mt-1">Rs.{{ number_format($stats['pending_due'], 2) }}</span>
        <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">Regular</span>
                <span
                    class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['regular_pending'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">Credit</span>
                <span
                    class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['credit_orders'], 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-3.5 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Collected Payments</span>
        <span
            class="text-lg font-black text-emerald-600 mt-1">Rs.{{ number_format($stats['collected_payments'], 2) }}</span>
        <div class="mt-2 flex flex-col gap-1 pt-2 border-t border-slate-50">
            <div class="flex justify-between items-center mb-0.5">
                <span
                    class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded uppercase">From
                    These Orders</span>
                <span
                    class="text-[10px] font-black text-emerald-600">Rs.{{ number_format($stats['from_these_orders'] ?? $stats['collected_payments'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">💵 Cash</span>
                <span class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['cash'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">💳 Card</span>
                <span class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['card'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-[11px] font-bold text-slate-500">🏦 Bank</span>
                <span class="text-[11px] font-black text-slate-700">Rs.{{ number_format($stats['bank'], 2) }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white p-3 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between gap-1.5">
        <div
            class="bg-indigo-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-indigo-100/50 flex-1">
            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Total Orders</span>
            <span class="text-lg font-black text-indigo-700">{{ $stats['order_qty'] }}</span>
        </div>
        <div
            class="bg-violet-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-violet-100/50 flex-1">
            <span class="text-[10px] font-black text-violet-500 uppercase tracking-widest">Total Qty</span>
            <span class="text-lg font-black text-violet-700">{{ $stats['item_qty'] + 0 }}</span>
        </div>
        <div
            class="bg-blue-50/70 rounded-lg px-2.5 py-1.5 flex items-center justify-between border border-blue-100/50 flex-1">
            <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Customers</span>
            <span class="text-lg font-black text-blue-700">{{ $stats['total_customer_count'] }}</span>
        </div>
    </div>
</div>
