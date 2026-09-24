        <!-- ── Dispatch Delivery Modal ── -->
        <div x-cloak x-show="showDispatchDeliveryModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                @click.away="showDispatchDeliveryModal = false">
                <div
                    style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eff6ff;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#1d4ed8;margin:0;">Dispatch Delivery</h3>
                        <p style="font-size:12px;color:#3b82f6;margin:2px 0 0;font-weight:600;"
                            x-text="'Order #' + (deliverOrder?.order_number || '')"></p>
                    </div>
                    <button @click="showDispatchDeliveryModal = false"
                        style="background:none;border:none;cursor:pointer;color:#1d4ed8;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label
                            style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Delivery
                            Method</label>
                        <div class="grid grid-cols-2 gap-3"
                            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                            <button @click="deliveryMethod = 'rider'"
                                :class="deliveryMethod === 'rider' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600'"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center"
                                style="padding: 12px; border-width: 1px; border-radius: 12px; font-weight: 700; font-size: 14px; text-align: center; cursor:pointer;">
                                Rider
                            </button>
                            <button @click="deliveryMethod = 'pickme_flash'" :class="[
                                    deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300'
                                ]"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group"
                                style="padding: 12px; border-width: 1px; border-radius: 12px; font-weight: 700; font-size: 14px; text-align: center; cursor:pointer; position:relative;">
                                PickMe
                            </button>
                        </div>
                    </div>

                    <div x-show="deliveryMethod === 'rider'" style="display: none;">
                        <label
                            style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select
                            Rider</label>
                        <select x-model="deliveryRiderId"
                            style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="">-- Choose Rider --</option>
                            <template x-for="rider in riders" :key="rider.id">
                                <option :value="rider.id" x-text="rider.name"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="deliveryMethod === 'pickme_flash'" style="display: none;">
                        <label
                            style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">PickMe
                            Cost (Rs.)</label>
                        <input type="number" x-model="deliveryPickmeCost" placeholder="e.g. 500"
                            style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:600;">This will automatically
                            be added as a Branch Expense.</p>

                        <div style="margin-top:12px;">
                            <div x-show="deliverOrder && (deliverOrder.total - (deliverOrder.paid_amount || 0) > 0)"
                                style="margin-bottom:8px;padding:8px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:12px;font-weight:700;color:#c2410c;">
                                ⚠️ Balance to Collect: LKR <span
                                    x-text="deliverOrder ? (deliverOrder.total - (deliverOrder.paid_amount || 0)).toFixed(2) : '0.00'"></span>
                            </div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Payment
                                Method</label>
                            <select x-model="deliveryPaymentMethod"
                                style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit (Pending Payment)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="button" @click="showDispatchDeliveryModal = false"
                            style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="submitDeliveryDispatch()" :disabled="isDispatchingDelivery"
                            style="flex:1;padding:14px;background:#3b82f6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                            <span x-text="isDispatchingDelivery ? 'Dispatching...' : 'Dispatch Now'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
