        <!-- Pending Orders Modal -->
        <div x-cloak x-show="pendingModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[85vh]" @click.away="pendingModalOpen = false">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-amber-50">
                    <div>
                        <h3 class="text-[11px] md:text-base font-black text-amber-600">Pending Orders</h3>
                        <p class="text-[10px] md:text-sm font-bold text-amber-700 mt-1" x-text="'Service: ' + selectedServiceName"></p>
                    </div>
                    <button @click="pendingModalOpen = false" class="w-10 h-10 flex items-center justify-center rounded-full bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors font-bold text-[11px] md:text-base">✕</button>
                </div>
                <div class="overflow-y-auto bg-slate-50 flex-1">
                    <table class="w-full text-left border-collapse text-[10px] md:text-sm">
                        <thead class="bg-slate-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Order</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest">Customer</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Service Amount</th>
                                <th class="px-4 py-3 text-[9px] md:text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <template x-for="order in selectedServiceOrders" :key="order.id">
                                <tr class="bg-white hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2">
                                            <a :href="'/orders/view/' + order.order_number" target="_blank" class="font-bold text-blue-600 hover:underline" x-text="order.order_number"></a>
                                            <span x-show="order.payment_method === 'credit'" class="px-2 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded-md">CREDIT</span>
                                            <span x-show="order.payment_method === 'pay_on_pickup'" class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-md">PAY ON PICKUP</span>
                                        </div>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-400 mt-1" x-text="new Date(order.created_at).toLocaleDateString()"></div>
                                    </td>
                                    <td class="px-4 py-4 font-bold text-slate-900">
                                        <span x-text="order.customer ? order.customer.name : 'Walk-in'"></span>
                                        <div class="text-[9px] md:text-xs font-semibold text-slate-500 mt-1" x-text="order.customer ? order.customer.phone : ''"></div>
                                    </td>
                                    <td class="px-4 py-4 text-right font-black text-rose-500" x-text="'Rs.' + parseFloat(order.service_due || 0).toFixed(2)"></td>
                                    <td class="px-4 py-4 text-center">
                                        <a :href="'/orders/view/' + order.order_number" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-[9px] md:text-xs font-bold rounded-lg shadow-sm transition-colors inline-block">View</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="selectedServiceOrders.length === 0">
                                <td colspan="4" class="px-3 py-6 md:px-6 md:py-10 text-center text-slate-400 font-semibold text-[10px] md:text-sm">No pending orders found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Collect Credit Payment Modal -->
        <div x-cloak x-show="collectModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col" @click.away="collectModalOpen = false">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-orange-50">
                    <div>
                        <h3 class="text-sm md:text-base font-black text-orange-600">Collect Credit Payment</h3>
                        <p class="text-[10px] md:text-sm font-bold text-orange-700 mt-1" x-text="'Order: ' + collectOrderNumber"></p>
                    </div>
                    <button @click="collectModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 hover:bg-orange-200 transition-colors font-bold">✕</button>
                </div>
                <div class="p-5 bg-white">
                    <form x-bind:action="'/orders/' + collectOrderId + '/credit-payment'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Amount to Collect (Rs)</label>
                            <input type="number" step="0.01" name="amount" x-model="collectAmount" max="" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-0">
                        </div>
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Payment Method</label>
                            <select name="payment_method" required class="w-full px-3 py-2 border-2 border-slate-200 rounded-xl text-sm font-black text-slate-900 bg-slate-50 focus:bg-white focus:border-orange-500 focus:ring-0">
                                <option value="cash">💵 Cash</option>
                                <option value="card">💳 Card</option>
                                <option value="bank_transfer">🏦 Bank Transfer</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-black rounded-xl shadow-lg shadow-orange-500/30 transition-transform active:scale-95 text-sm uppercase tracking-wider">
                            Confirm Collection
                        </button>
                    </form>
                </div>
            </div>
        </div>
