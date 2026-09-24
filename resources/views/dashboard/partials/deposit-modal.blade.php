    <!-- Bank Deposit Modal -->
    <div id="depositModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-lg font-black text-slate-900">Make Bank Deposit</h3>
                <button onclick="document.getElementById('depositModal').classList.add('hidden')" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('bank-deposits.store') }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-500 uppercase tracking-widest mb-1">Available Cash to Deposit</label>
                    <p class="text-lg font-black text-emerald-600">Rs. {{ number_format($stats['cash_balance'] ?? 0, 2) }}</p>
                </div>
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Deposit Amount (Rs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="amount" required max="{{ $stats['cash_balance'] ?? 0 }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400">
                </div>
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Bank / Reference No.</label>
                    <input type="text" name="reference_number" placeholder="e.g. BOC-12345" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400">
                </div>
                <div class="mb-6">
                    <label class="block text-[11px] font-extrabold text-slate-600 uppercase tracking-widest mb-2">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400"></textarea>
                </div>
                <button type="submit" class="w-full py-3 bg-emerald-600 text-white text-[13px] font-extrabold rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-500/30">
                    Confirm Deposit
                </button>
            </form>
        </div>
    </div>
