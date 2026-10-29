    <!-- Modals -->

    <!-- ── Mark Done Modal ── -->
    <div x-cloak x-show="showDoneModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showDoneModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">✅ Mark as Done</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
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
    <!-- ── Branch Arrival Modal ── -->
    <div x-cloak x-show="showBranchArrivalModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showBranchArrivalModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">✅ Confirm Arrival at Branch</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showBranchArrivalModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                <!-- Cubby Selection -->
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Assign Storage Location (Cubby)</label>
                    <select x-model="branchArrivalCubbyId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;background:white;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
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
                    <button type="button" @click="showBranchArrivalModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitBranchArrival()" :disabled="isBranchArrivalSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isBranchArrivalSubmitting ? 'Processing...' : 'Confirm Arrival'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ── Handover Modal ── -->
    <div x-cloak x-show="showHandoverModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showHandoverModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">🤝 Hand Over & Complete</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showHandoverModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                <template x-if="dispatch?.order?.cubby?.cubby">
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;background:#f59e0b;color:white;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;">
                            📦
                        </div>
                        <div>
                            <span style="font-size:11px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Assigned Storage Location</span>
                            <span style="font-size:16px;font-weight:900;color:#92400e;" x-text="dispatch.order.cubby.cubby.name"></span>
                        </div>
                    </div>
                </template>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#64748b;">Total Amount</span>
                        <span style="font-size:13px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(dispatch?.order?.total || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#16a34a;">Already Paid</span>
                        <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'Rs. ' + parseFloat(dispatch?.order?.paid_amount || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1.5px dashed #cbd5e1;">
                        <span style="font-size:14px;font-weight:900;color:#dc2626;">Balance Due</span>
                        <span style="font-size:15px;font-weight:900;color:#dc2626;" x-text="'Rs. ' + Math.max(0, parseFloat(dispatch?.order?.total||0) - parseFloat(dispatch?.order?.paid_amount||0)).toFixed(2)"></span>
                    </div>
                </div>

                <template x-if="dispatch && (parseFloat(dispatch?.order?.total||0) - parseFloat(dispatch?.order?.paid_amount||0)) > 0">
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

                <template x-if="dispatch && (parseFloat(dispatch?.order?.paid_amount||0) - parseFloat(dispatch?.order?.total||0)) > 0">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Overpayment / Return Change</label>
                        <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:12px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <span style="font-size:13px;font-weight:800;color:#92400e;">Change to Return</span>
                                <p style="font-size:11px;color:#b45309;margin:2px 0 0;font-weight:600;">Refund will be processed from cash drawer.</p>
                            </div>
                            <span style="font-size:16px;font-weight:900;color:#ea580c;" x-text="'Rs. ' + (parseFloat(dispatch?.order?.paid_amount||0) - parseFloat(dispatch?.order?.total||0)).toFixed(2)"></span>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;margin-top:10px;">
                            <input type="checkbox" id="refundHandover" x-model="handoverRefundOverpayment" style="width:16px;height:16px;accent-color:#ea580c;cursor:pointer;">
                            <label for="refundHandover" style="font-size:12px;font-weight:700;color:#92400e;cursor:pointer;user-select:none;">Process refund automatically upon handover</label>
                        </div>
                    </div>
                </template>
                
                <div style="display:flex;align-items:center;gap:10px;margin-top:5px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;">
                    <input type="checkbox" id="printReceiptHandover2" x-model="handoverPrintReceipt" style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                    <label for="printReceiptHandover2" style="font-size:13px;font-weight:700;color:#334155;cursor:pointer;user-select:none;">Print Receipt & Hand Over</label>
                </div>

                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showHandoverModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitHandover()" :disabled="isHandoverSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isHandoverSubmitting ? 'Processing...' : 'Confirm Handover'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ── Convert to Pickup + Overpayment Modal ── -->
    <div x-data="convertPickupModal()" @open-convert-pickup-modal.window="openModal()" @open-overpayment-modal.window="overpaymentAmount = $event.detail; showOverpaymentModal = true;">

        <!-- Convert to Pickup Modal -->
        <div x-cloak x-show="showConvertPickupModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showConvertPickupModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fffbeb;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#d97706;margin:0;">Convert to Customer Pickup</h3>
                        <p style="font-size:12px;color:#b45309;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || (typeof order !== 'undefined' ? order.order_number : ''))"></p>
                    </div>
                    <button @click="showConvertPickupModal = false" style="background:none;border:none;cursor:pointer;color:#d97706;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <p style="font-size:13px;color:#475569;margin:0;font-weight:500;">The customer has decided to pick up the order from the branch instead of having it delivered.</p>
                    
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:700;color:#64748b;">Current Delivery Fee</span>
                            <span style="font-size:14px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(convertPickupDeliveryFee).toFixed(2)"></span>
                        </div>
                        
                        <div style="margin-top:12px;">
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Deduct Amount (Rs.)</label>
                            <input type="number" step="0.01" x-model="convertPickupDeductAmount" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#f59e0b'" onblur="this.style.borderColor='#e2e8f0'">
                            <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:500;">How much should be deducted from the total? (Defaults to full delivery fee)</p>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="button" @click="showConvertPickupModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="submitConvertPickup()" :disabled="isConvertingPickup" style="flex:1;padding:14px;background:linear-gradient(to right,#f59e0b,#d97706);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                            <span x-text="isConvertingPickup ? 'Converting...' : 'Confirm Conversion'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Overpayment Modal ── -->
        <div x-cloak x-show="showOverpaymentModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.3);width:100%;max-width:460px;overflow:hidden;" @click.away="showOverpaymentModal = false">
                <div style="padding:24px;background:linear-gradient(135deg,#fef3c7,#fffbeb);border-bottom:1px solid #fde68a;display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="font-size:28px;margin-bottom:8px;">⚠️</div>
                        <h3 style="font-size:18px;font-weight:900;color:#92400e;margin:0;">Overpayment Detected</h3>
                        <p style="font-size:13px;color:#b45309;margin:4px 0 0;font-weight:600;">
                            Customer has paid <strong>Rs. <span x-text="overpaymentAmount.toFixed(2)"></span></strong> more than the new total.
                        </p>
                    </div>
                    <button @click="showOverpaymentModal = false" style="background:none;border:none;cursor:pointer;color:#92400e;font-size:20px;opacity:0.6;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:12px;">
                    <p style="font-size:13px;color:#334155;font-weight:600;margin:0;">What would you like to do with the extra <strong>Rs. <span x-text="overpaymentAmount.toFixed(2)"></span></strong>?</p>

                    <!-- Option 1: Refund -->
                    <button type="button" @click="handleOverpayment('refund')" :disabled="isProcessingRefund"
                        style="width:100%;padding:16px;background:linear-gradient(135deg,#ef4444,#dc2626);color:white;border:none;border-radius:14px;cursor:pointer;text-align:left;display:flex;align-items:center;gap:12px;box-shadow:0 4px 12px rgba(220,38,38,0.25);">
                        <span style="font-size:24px;">💵</span>
                        <div>
                            <div style="font-size:14px;font-weight:900;">Refund to Customer</div>
                            <div style="font-size:11px;opacity:0.85;margin-top:2px;">Rs. <span x-text="overpaymentAmount.toFixed(2)"></span> cash returned. Cash drawer will be decremented.</div>
                        </div>
                    </button>



                    <p x-show="isProcessingRefund" style="text-align:center;font-size:12px;color:#6366f1;font-weight:700;margin:0;">Processing...</p>
                </div>
            </div>
        </div>

    </div>
