    <!-- Collect Credit Payment Modal -->
    <div x-data="{
        collectModalOpen: false,
        collectOrderId: null,
        collectOrderNumber: '',
        collectAmount: 0
    }" @open-collect-modal.window="
        collectOrderId = $event.detail.id;
        collectOrderNumber = $event.detail.number;
        collectAmount = $event.detail.amount;
        collectModalOpen = true;
    ">
        <div x-cloak x-show="collectModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col"
                @click.away="collectModalOpen = false">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-red-50">
                    <div>
                        <h3 class="text-sm md:text-base font-black text-red-600">Collect Credit Payment</h3>
                        <p class="text-[10px] md:text-sm font-bold text-red-700 mt-1"
                            x-text="'Order: ' + collectOrderNumber"></p>
                    </div>
                    <button @click="collectModalOpen = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors font-bold">&times;</button>
                </div>
                <div class="p-5 bg-white">
                    <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label
                                class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount
                                to Collect (Rs)</label>
                            <input type="number" step="0.01" name="amount" x-model="collectAmount" max="" required
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                        </div>
                        <div class="mb-5">
                            <label
                                class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment
                                Method</label>
                            <select name="payment_method" required
                                class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-red-500 focus:ring-0">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-black rounded-xl shadow-lg shadow-red-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                            Confirm Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
