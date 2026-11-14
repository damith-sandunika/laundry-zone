<!-- Collect Credit Payment Modal (Single Order) -->
<div x-cloak x-show="collectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.away="collectModalOpen = false">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-red-50">
            <div>
                <h3 class="text-sm md:text-base font-black text-red-600">Collect Credit Payment</h3>
                <p class="text-[10px] md:text-sm font-bold text-red-700 mt-1" x-text="'Order: ' + collectOrderNumber"></p>
            </div>
            <button @click="collectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors font-bold">×</button>
        </div>
        <div class="p-5 bg-white">
            <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount to Collect (Rs)</label>
                    <input type="number" step="0.01" name="amount" x-model="collectAmount" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                </div>
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                    <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                        <option value="cash">💵 Cash</option>
                        <option value="card">💳 Card</option>
                        <option value="bank">🏦 Bank Transfer</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl shadow-lg shadow-red-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                    Confirm Collection
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Collect Credit Payment Modal (All Orders) -->
<div x-cloak x-show="bulkCollectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col" @click.away="bulkCollectModalOpen = false">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-indigo-50">
            <div>
                <h3 class="text-sm md:text-base font-black text-indigo-600">Settle Customer Payments</h3>
                <p class="text-[10px] md:text-sm font-bold text-indigo-700 mt-1">Payments will be applied to oldest orders first.</p>
            </div>
            <button @click="bulkCollectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 hover:bg-indigo-200 transition-colors font-bold">×</button>
        </div>
        
        <div class="p-5 bg-white max-h-[70vh] overflow-y-auto">
            <form action="{{ route('customers.bulkCreditPayment', $customer) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount (Rs)</label>
                        <input type="number" step="0.01" name="amount" x-model="bulkCollectAmount" required min="1" class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-lg font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                        <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-0 h-[46px]">
                            <option value="cash">💵 Cash</option>
                            <option value="card">💳 Card</option>
                            <option value="bank">🏦 Bank Transfer</option>
                        </select>
                    </div>
                </div>
                
                <!-- Real-time Preview Table -->
                <div class="mb-5">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-2">Settlement Preview</label>
                    
                    <div x-show="bulkSettlementPreview.length === 0" class="p-4 bg-slate-50 rounded-xl text-center text-xs font-semibold text-slate-500">
                        Enter an amount to see how it will be distributed.
                    </div>
                    
                    <div x-show="bulkSettlementPreview.length > 0" class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2 font-black text-slate-600">Order #</th>
                                    <th class="px-3 py-2 font-black text-slate-600 text-right">Due</th>
                                    <th class="px-3 py-2 font-black text-emerald-600 text-right">Applied</th>
                                    <th class="px-3 py-2 font-black text-slate-600 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="item in bulkSettlementPreview" :key="item.number">
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-3 py-2 font-mono font-bold text-blue-600" x-text="item.number"></td>
                                        <td class="px-3 py-2 font-semibold text-right text-slate-600" x-text="'Rs.' + Number(item.due).toFixed(2)"></td>
                                        <td class="px-3 py-2 font-black text-right text-emerald-600" x-text="'Rs.' + Number(item.paid).toFixed(2)"></td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                                                  :class="item.status === 'Fully Settled' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                                                  x-text="item.status"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <button type="submit" :disabled="bulkCollectAmount <= 0" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-xl shadow-lg shadow-indigo-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirm Bulk Collection
                </button>
            </form>
        </div>
    </div>
</div>

