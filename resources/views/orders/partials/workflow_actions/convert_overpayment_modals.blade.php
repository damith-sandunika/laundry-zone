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
