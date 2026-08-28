<div x-cloak x-show="showSlipModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
    <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showSlipModal = false">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eef2ff;">
            <div>
                <h3 style="font-size:15px;font-weight:900;color:#3730a3;margin:0;">Review Slip</h3>
                <p style="font-size:11px;color:#4f46e5;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (selectedOrder?.order_number || '')"></p>
            </div>
            <button @click="showSlipModal = false" style="background:none;border:none;cursor:pointer;color:#3730a3;font-size:18px;line-height:1;">✕</button>
        </div>
        
        <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
            <!-- Image Scrollable Area -->
            <div style="width:100%;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;overflow-y:auto;overflow-x:hidden;max-height:220px;position:relative;">
                <img :src="selectedSlip?.url" alt="Payment Slip" style="width:100%;height:auto;display:block;">
            </div>
            <div style="text-align:right;">
                <a :href="selectedSlip?.url" target="_blank" style="font-size:11px;color:#4f46e5;font-weight:700;text-decoration:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab</a>
            </div>

            <!-- Payment Info -->
            <div style="background:white;border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <span style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Amount Paid</span>
                    <span style="font-size:18px;font-weight:900;color:#0f172a;" x-text="selectedSlip?.amount ? 'Rs. ' + parseFloat(selectedSlip.amount).toFixed(2) : 'N/A'"></span>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Time</span>
                    <span style="font-size:11px;color:#334155;font-weight:700;" x-text="selectedSlip?.created_at || ''"></span>
                </div>
            </div>

            <template x-if="selectedSlip?.status === 'rejected'">
                <div style="background:#fef2f2;border:1px solid #fecaca;padding:10px;border-radius:8px;">
                    <span style="font-size:10px;font-weight:800;color:#b91c1c;text-transform:uppercase;display:block;margin-bottom:2px;"><i class="fa-solid fa-triangle-exclamation"></i> Rejection Reason</span>
                    <span style="font-size:11px;color:#991b1b;font-weight:600;" x-text="selectedSlip?.reject_reason || 'No reason provided'"></span>
                </div>
            </template>

            <!-- Actions for Pending Slip -->
            <template x-if="selectedSlip && selectedSlip.status === 'pending'">
                <div x-data="{ mode: 'review' }">
                    <div x-show="mode === 'review'" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                        <form method="POST" :action="'/orders/' + selectedOrder.id + '/payments/' + selectedSlip.id + '/approve'" style="margin:0;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #16a34a, #15803d);color:white;font-size:13px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                        </form>
                        <button type="button" @click="mode = 'reject'" style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #dc2626, #b91c1c);color:white;font-size:13px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                                <i class="fa-solid fa-xmark"></i> Reject
                        </button>
                    </div>
                    
                    <form x-cloak x-show="mode === 'reject'" method="POST" :action="'/orders/' + selectedOrder.id + '/payments/' + selectedSlip.id + '/reject'" style="margin-top:8px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:700;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Reason for Rejection</label>
                            <input type="text" name="reject_reason" placeholder="e.g. Blurry image, amount mismatch..." required style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;outline:none;" onfocus="this.style.borderColor='#dc2626'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="button" @click="mode = 'review'" style="flex:1;padding:10px;background:white;color:#64748b;font-size:12.5px;font-weight:800;border:1.5px solid #cbd5e1;border-radius:8px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:10px;background:#dc2626;color:white;font-size:12.5px;font-weight:800;border:none;border-radius:8px;cursor:pointer;">Submit Rejection</button>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>
