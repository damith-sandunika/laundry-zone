<!-- Modal: Create Package -->
<div x-show="createPkgModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="createPkgModal = false">
        <h3 class="text-base font-black text-slate-900 mb-4">Create New SMS Package</h3>
        
        <form action="{{ route('super_admin.sms_packages.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Package Name</label>
                <input type="text" name="name" required placeholder="e.g. Standard Pack" class="w-full text-xs font-semibold rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                    <input type="number" name="sms_count" required placeholder="1000" min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Total Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" required placeholder="1800.00" min="0" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                    <input type="number" step="0.01" name="rate_per_sms" placeholder="Optional (auto)" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Badge</label>
                    <input type="text" name="badge" placeholder="e.g. Popular" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Features (One per line)</label>
                <textarea name="features" rows="3" placeholder="Instant Delivery&#10;No Expiry&#10;Priority Routing" class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-bold text-slate-700">Highlight / Featured</span>
                </label>
                <div>
                    <input type="number" name="order_index" placeholder="Order (0)" class="w-24 text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="createPkgModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Create Package</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Package -->
<div x-show="editPkgModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="editPkgModal = false">
        <h3 class="text-base font-black text-slate-900 mb-4">Edit SMS Package</h3>
        
        <form :action="'{{ url('super-admin/sms-packages') }}/' + activePkg.id" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Package Name</label>
                <input type="text" name="name" :value="activePkg.name" required class="w-full text-xs font-semibold rounded-xl border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                    <input type="number" name="sms_count" :value="activePkg.sms_count" required min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Total Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" :value="activePkg.price" required min="0" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                    <input type="number" step="0.01" name="rate_per_sms" :value="activePkg.rate_per_sms" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Badge</label>
                    <input type="text" name="badge" :value="activePkg.badge" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Features (One per line)</label>
                <textarea name="features" rows="3" :value="(activePkg.features || []).join('\n')" class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" :checked="activePkg.is_featured" class="rounded text-blue-600 focus:ring-blue-500">
                    <span class="text-xs font-bold text-slate-700">Featured</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" :checked="activePkg.is_active" class="rounded text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs font-bold text-slate-700">Active</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editPkgModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Issue Top-Up Credit -->
<div x-show="topUpModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100" @click.away="topUpModal = false">
        <h3 class="text-base font-black text-slate-900 mb-4">Issue SMS Credits to Tenant</h3>
        
        <form action="{{ route('super_admin.sms_credits.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Select Tenant Company</label>
                <select name="tenant_id" required class="w-full text-xs font-semibold rounded-xl border-slate-200">
                    <option value="">-- Choose Company --</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->name }} (Balance: {{ $t->sms_balance }} SMS | Default Rate: Rs. {{ number_format($t->sms_rate, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">SMS Count</label>
                    <input type="number" name="sms_count" required placeholder="e.g. 1000" min="1" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rate per SMS (Rs.)</label>
                    <input type="number" step="0.01" name="rate_per_sms" placeholder="e.g. 2.00" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Amount Paid (Rs.)</label>
                    <input type="number" step="0.01" name="amount_paid" placeholder="Leave empty for auto" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full text-xs font-semibold rounded-xl border-slate-200">
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="Online Gateway">Online Gateway</option>
                        <option value="Complimentary">Complimentary / Free</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Payment Reference</label>
                <input type="text" name="payment_reference" placeholder="e.g. SLIP-998822" class="w-full text-xs font-semibold rounded-xl border-slate-200">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" placeholder="Bundle top-up details..." class="w-full text-xs font-semibold rounded-xl border-slate-200"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="topUpModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl">Credit SMS Volume</button>
            </div>
        </form>
    </div>
</div>

