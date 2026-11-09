<!-- ══════════ BILLING & STATUS MODALS (LIGHT THEME) ══════════ -->

<!-- 1. Generate Invoice Modal -->
<div x-show="invoiceModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
    <div class="w-full max-w-lg bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 space-y-5" @click.away="invoiceModal = false">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Generate Tenant Subscription Invoice</h3>
            <button @click="invoiceModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form action="{{ route('super_admin.payments.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Select Company</label>
                <select name="tenant_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} (Rs. {{ number_format($t->total_monthly_price, 0) }}/mo)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Amount (LKR)</label>
                    <input type="number" step="100" name="amount" placeholder="Leave empty for plan rate"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Billing Month</label>
                    <input type="month" name="billing_month" value="{{ date('Y-m') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Method</label>
                <input type="text" name="payment_method" placeholder="Bank Transfer / Cash / Online"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional invoice notes..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="invoiceModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm">Create Invoice</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Update Status Modal -->
<div x-show="statusModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
    <div class="w-full max-w-sm bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 space-y-4" @click.away="statusModal = false">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Update Invoice Status</h3>
            <button @click="statusModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form :action="`/saas-admin/payments/${activeSub.id}/status`" method="POST" class="space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Invoice Status</label>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold">
                    <option value="paid">Paid (Settled)</option>
                    <option value="pending">Pending Payment</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Payment Method</label>
                <input type="text" name="payment_method" :value="activeSub.payment_method || 'Bank Transfer'"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="statusModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-sm">Save Status</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Add SMS Credits Modal -->
<div x-show="smsModal" style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
    <div class="w-full max-w-lg bg-white border border-slate-200 rounded-3xl shadow-2xl p-6 space-y-4" @click.away="smsModal = false">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Add SMS Credits to Company</h3>
                <p class="text-xs text-slate-400">Issue an SMS top-up bundle upon receiving payment</p>
            </div>
            <button @click="smsModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form action="{{ route('super_admin.sms_credits.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Select Company</label>
                <select name="tenant_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} (Current Balance: {{ number_format($t->sms_balance ?? 0) }} SMS)</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">SMS Count to Add</label>
                    <input type="number" step="100" min="1" name="sms_count" value="1000" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Rate per SMS (LKR)</label>
                    <input type="number" step="0.1" min="0" name="rate_per_sms" value="2.00" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Amount Paid (LKR)</label>
                    <input type="number" step="50" min="0" name="amount_paid" placeholder="Auto: Count × Rate"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Method</label>
                    <input type="text" name="payment_method" value="Bank Transfer"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Payment Reference / Note</label>
                <input type="text" name="payment_reference" placeholder="Deposit Slip Ref # / Bank Trans ID"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="smsModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm">Credit SMS Bundle</button>
            </div>
        </form>
    </div>
</div>
