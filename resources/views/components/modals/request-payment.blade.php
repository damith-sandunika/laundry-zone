<div x-cloak x-show="showPaymentModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
    <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showPaymentModal = false">
        <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f0fdfa;">
            <div>
                <h3 style="font-size:18px;font-weight:900;color:#0f766e;margin:0;">Request Payment</h3>
                <p style="font-size:12px;color:#14b8a6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (paymentOrder?.order_number || '')"></p>
            </div>
            <button @click="showPaymentModal = false" style="background:none;border:none;cursor:pointer;color:#0f766e;font-size:20px;">✕</button>
        </div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;text-align:center;">
                <span style="font-size:13px;font-weight:700;color:#64748b;display:block;">Total Bill</span>
                <span style="font-size:24px;font-weight:900;color:#0f172a;" x-text="'Rs. ' + parseFloat(paymentOrder?.total || 0).toFixed(2)"></span>
            </div>

            <div>
                <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Payment Amount to Request</label>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="paymentAmountType = '50'" :class="paymentAmountType === '50' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                        50%
                        <div class="text-[10px] font-semibold text-slate-400 mt-1" x-text="'Rs.' + (parseFloat(paymentOrder?.total || 0) / 2).toFixed(0)"></div>
                    </button>
                    <button @click="paymentAmountType = 'full'" :class="paymentAmountType === 'full' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                        Full
                        <div class="text-[10px] font-semibold text-slate-400 mt-1" x-text="'Rs.' + parseFloat(paymentOrder?.total || 0).toFixed(0)"></div>
                    </button>
                    <button @click="paymentAmountType = 'custom'" :class="paymentAmountType === 'custom' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                        Custom
                        <div class="text-[10px] font-semibold text-slate-400 mt-1">Amount</div>
                    </button>
                </div>
            </div>

            <div x-show="paymentAmountType === 'custom'" style="display: none;">
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Enter Custom Amount (Rs.)</label>
                <input type="number" x-model="customPaymentAmount" placeholder="e.g. 1500" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#14b8a6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="button" @click="showPaymentModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                <button type="button" @click="sendPaymentRequest()" :disabled="isRequestingPayment" style="flex:1;padding:14px;background:#14b8a6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(20,184,166,0.3);">
                    <span x-text="isRequestingPayment ? 'Sending...' : 'Send WhatsApp Link'"></span>
                </button>
            </div>
        </div>
    </div>
</div>


