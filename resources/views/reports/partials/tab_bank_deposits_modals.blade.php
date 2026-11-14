{{-- ══════════════════════════════════════════════════════
     BANK DEPOSIT EDIT MODAL
══════════════════════════════════════════════════════ --}}
@if(auth()->user()->hasAdminAccess() || auth()->user()->isWorkshopAdmin())
<div
    x-data="{ show: false, deposit: { id: null, amount: '', reference_number: '', notes: '' }, editNotes: '' }"
    @open-deposit-edit.window="deposit = $event.detail; editNotes = ''; show = true"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" x-transition>
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-amber-50">
            <div>
                <h3 class="text-sm font-black text-slate-800">Edit Bank Deposit</h3>
                <p class="text-[10px] font-semibold text-amber-600 mt-0.5">This will be marked as edited with your name and timestamp.</p>
            </div>
            <button @click="show = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form
            :action="'/bank-deposits/' + deposit.id"
            method="POST"
            class="p-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="0.01" x-model="deposit.amount"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white" required>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Reference Number</label>
                <input type="text" name="reference_number" x-model="deposit.reference_number"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white">
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Notes</label>
                <textarea name="notes" rows="2" x-model="deposit.notes"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white resize-none"></textarea>
            </div>
            <div>
                <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Edit Reason / Note <span class="text-red-500">*</span></label>
                <input type="text" name="edit_notes" x-model="editNotes" placeholder="e.g. Correction of data entry error" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-amber-400/30 focus:border-amber-400 outline-none bg-white">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" @click="show = false"
                    class="flex-1 py-2.5 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit"
                    class="flex-1 py-2.5 text-xs font-black text-white bg-amber-500 rounded-xl hover:bg-amber-600 shadow-sm transition-all">
                    <i class="fa-solid fa-pen mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     BANK DEPOSIT DELETE CONFIRM MODAL
══════════════════════════════════════════════════════ --}}
<div
    x-data="{ show: false, deposit: { id: null, amount: '' }, reason: '' }"
    @open-deposit-delete.window="deposit = $event.detail; reason = ''; show = true"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="show = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden" x-transition>
        <div class="p-5 border-b border-slate-100 bg-red-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-trash"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Delete Deposit</h3>
                    <p class="text-[10px] font-semibold text-red-500 mt-0.5">Cash balance will be restored. Record remains visible as deleted.</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-center mb-4">
                <p class="text-[9px] font-black text-red-400 uppercase tracking-wider">Deposit Amount</p>
                <p class="text-xl font-black text-red-600">Rs. <span x-text="parseFloat(deposit.amount).toFixed(2)"></span></p>
            </div>
            <form :action="'/bank-deposits/' + deposit.id" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1">Reason for Deletion <span class="text-red-500">*</span></label>
                    <input type="text" name="delete_reason" x-model="reason" placeholder="e.g. Duplicate entry" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 focus:ring-2 focus:ring-red-400/30 focus:border-red-400 outline-none bg-white">
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="show = false"
                        class="flex-1 py-2.5 text-xs font-black text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                    <button type="submit"
                        class="flex-1 py-2.5 text-xs font-black text-white bg-red-500 rounded-xl hover:bg-red-600 shadow-sm transition-all">
                        <i class="fa-solid fa-trash mr-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
