@if(!$isDeleted && $isToday)
    <!-- Edit Payment Modal -->
    <div x-show="openPaymentEditModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openPaymentEditModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-pen text-blue-600"></i> Edit Payment Slip #{{ $tx->id }}
                </h3>
                <button type="button" @click="openPaymentEditModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" x-model="newPaymentAmount" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold text-slate-800 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select x-model="newPaymentMethod" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-semibold outline-none">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Edit <span class="text-red-500">*</span></label>
                    <input type="text" x-model="editReason" placeholder="e.g. Corrected cash amount entered" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-medium outline-none">
                </div>
                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 font-medium leading-tight">
                    <i class="fa-solid fa-shield-halved text-amber-600"></i> Changes are recorded in audit logs and will update drawer balance automatically.
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-2">
                <button type="button" @click="openPaymentEditModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" @click="updatePayment(false)" :disabled="!editReason || !newPaymentAmount || newPaymentAmount <= 0 || isUpdating" class="flex-1 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 shadow-sm flex items-center justify-center gap-2 transition-colors">
                    <span x-show="!isUpdating">Save Changes</span>
                    <i x-show="isUpdating" class="fa-solid fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Payment Confirmation Modal -->
    <div x-show="openPaymentDeleteModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openPaymentDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
            <div class="p-4 border-b border-rose-200 flex justify-between items-center bg-rose-50">
                <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Delete Payment Slip #{{ $tx->id }}
                </h3>
                <button type="button" @click="openPaymentDeleteModal = false" class="text-rose-400 hover:text-rose-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-4 space-y-3">
                <div class="text-xs text-slate-700 font-medium">
                    Are you sure you want to delete this payment of <strong class="text-slate-900 font-bold">Rs. {{ number_format($tx->amount, 2) }} ({{ ucfirst($tx->payment_method) }})</strong>?
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Deletion <span class="text-rose-500">*</span></label>
                    <textarea x-model="deleteReason" rows="2" placeholder="e.g. Duplicate entry by staff, customer cancelled" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500 font-medium outline-none resize-none"></textarea>
                </div>
                <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-[11px] text-rose-800 font-medium leading-tight">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> The slip will remain visible marked as <strong>DELETED</strong> for accounting audit, and the order balance will adjust.
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-2">
                <button type="button" @click="openPaymentDeleteModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="button" @click="updatePayment(true)" :disabled="!deleteReason || isUpdating" class="flex-1 py-2 text-xs font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 disabled:opacity-50 shadow-sm flex items-center justify-center gap-2 transition-colors">
                    <span x-show="!isUpdating">Confirm Delete</span>
                    <i x-show="isUpdating" class="fa-solid fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
@endif

