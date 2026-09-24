        <!-- Modals for workshop (if any) -->
        
        <!-- ── Dispatch Delivery Modal ── -->
        <div x-cloak x-show="showDeliveryModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showDeliveryModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eff6ff;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#1d4ed8;margin:0;">Dispatch Delivery</h3>
                        <p style="font-size:12px;color:#3b82f6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (deliveryDispatch?.order?.order_number || '')"></p>
                    </div>
                    <button @click="showDeliveryModal = false" style="background:none;border:none;cursor:pointer;color:#1d4ed8;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Delivery Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="deliveryMethod = 'rider'" :class="deliveryMethod === 'rider' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                                Rider
                            </button>
                            <button @click="deliveryMethod = 'pickme_flash'" 
                                :class="[
                                    deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300'
                                ]"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group">
                                PickMe
                            </button>
                        </div>
                    </div>

                    <div x-show="deliveryMethod === 'rider'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select Rider</label>
                        <select x-model="deliveryRiderId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="">-- Choose Rider --</option>
                            @foreach($riders as $rider)
                                <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="deliveryMethod === 'pickme_flash'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">PickMe Cost (Rs.)</label>
                        <input type="number" x-model="deliveryPickmeCost" placeholder="e.g. 500" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:600;">This will automatically be added as a Workshop Expense.</p>
                        
                        <div style="margin-top:12px;">
                            <div x-show="deliveryDispatch && deliveryDispatch.order && (deliveryDispatch.order.total - (deliveryDispatch.order.paid_amount || 0) > 0)" style="margin-bottom:8px;padding:8px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:12px;font-weight:700;color:#c2410c;">
                                ⚠️ Balance to Collect: LKR <span x-text="deliveryDispatch && deliveryDispatch.order ? (deliveryDispatch.order.total - (deliveryDispatch.order.paid_amount || 0)).toFixed(2) : '0.00'"></span>
                            </div>
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Payment Method</label>
                            <select x-model="deliveryPaymentMethod" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit">Credit (Pending Payment)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="button" @click="showDeliveryModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="submitDelivery()" :disabled="isDispatchingDelivery" style="flex:1;padding:14px;background:#3b82f6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                            <span x-text="isDispatchingDelivery ? 'Dispatching...' : 'Dispatch Now'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>



    <!-- ── Mark Done Modal ── -->
    <div x-cloak x-show="showDoneModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showDoneModal = false">
            <!-- Header -->
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">✅ Mark as Done</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (doneDispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showDoneModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">


                <!-- Cubby Selection -->
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Assign Storage Location (Cubby)</label>
                    <select x-model="doneCubbyId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;background:white;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">-- No cubby (skip) --</option>
                        @foreach($cubbies as $cubby)
                            <option value="{{ $cubby->id }}">
                                {{ $cubby->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showDoneModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitDone()" :disabled="isDoneSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isDoneSubmitting ? 'Processing...' : 'Confirm Done'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ── Handover Modal ── -->
    <div x-cloak x-show="showHandoverModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showHandoverModal = false">
            <!-- Header -->
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">🤝 Hand Over & Complete</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (handoverDispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showHandoverModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                <!-- Assigned Cubby (if any) -->
                <template x-if="handoverDispatch?.order?.cubby?.cubby">
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;background:#f59e0b;color:white;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;">
                            📦
                        </div>
                        <div>
                            <span style="font-size:11px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Assigned Storage Location</span>
                            <span style="font-size:16px;font-weight:900;color:#92400e;" x-text="handoverDispatch.order.cubby.cubby.name"></span>
                        </div>
                    </div>
                </template>

                <!-- Payment Summary -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#64748b;">Total Amount</span>
                        <span style="font-size:13px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(handoverDispatch?.order?.total || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#16a34a;">Already Paid</span>
                        <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'Rs. ' + parseFloat(handoverDispatch?.order?.paid_amount || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1.5px dashed #cbd5e1;">
                        <span style="font-size:14px;font-weight:900;color:#dc2626;">Balance Due</span>
                        <span style="font-size:15px;font-weight:900;color:#dc2626;" x-text="'Rs. ' + Math.max(0, parseFloat(handoverDispatch?.order?.total||0) - parseFloat(handoverDispatch?.order?.paid_amount||0)).toFixed(2)"></span>
                    </div>
                </div>

                <!-- Payment collection (only if balance > 0) -->
                <template x-if="handoverDispatch && (parseFloat(handoverDispatch?.order?.total||0) - parseFloat(handoverDispatch?.order?.paid_amount||0)) > 0">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Collect Payment Now</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Method</label>
                                <select x-model="handoverPaymentMethod" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                    <option value="cash">💵 Cash</option>
                                    <option value="bank">🏦 Bank Transfer</option>
                                    <option value="card">💳 Card</option>
                                    <option value="credit">⏳ Credit</option>
                                </select>
                            </div>
                            <div x-show="handoverPaymentMethod !== 'credit'">
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Amount (Rs.)</label>
                                <input type="number" step="0.01" x-model="handoverAmountPaid" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                            </div>
                        </div>
                    </div>
                </template>
                
                <div style="display:flex;align-items:center;gap:10px;margin-top:5px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;">
                    <input type="checkbox" id="printReceiptHandover" x-model="handoverPrintReceipt" style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                    <label for="printReceiptHandover" style="font-size:13px;font-weight:700;color:#334155;cursor:pointer;user-select:none;">Print Receipt & Hand Over</label>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showHandoverModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitHandover()" :disabled="isHandoverSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isHandoverSubmitting ? 'Processing...' : 'Confirm Handover'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-modals.review-slip />
