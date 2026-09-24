            <!-- ── Delivery & Payment Modal ── -->
            <div x-cloak x-show="showDeliverModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                    @click.away="showDeliverModal = false">
                    <div
                        style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eef2ff;">
                        <div>
                            <h3 style="font-size:18px;font-weight:900;color:#4f46e5;margin:0;">Deliver Order</h3>
                            <p style="font-size:12px;color:#4338ca;margin:2px 0 0;font-weight:600;"
                                x-text="'Handing over Order #' + deliverOrder?.order_number"></p>
                        </div>
                        <button @click="showDeliverModal = false"
                            style="background:none;border:none;cursor:pointer;color:#4f46e5;font-size:20px;">✕</button>
                    </div>
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">

                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#64748b;">Total Bill:</span>
                                <span style="font-size:14px;font-weight:900;color:#0f172a;"
                                    x-text="parseFloat(deliverOrder?.total || 0) === 0 ? 'Package' : 'Rs.' + parseFloat(deliverOrder?.total || 0).toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#64748b;">Paid Amount:</span>
                                <span style="font-size:14px;font-weight:900;color:#16a34a;"
                                    x-text="'Rs.' + parseFloat(deliverOrder?.paid_amount || 0).toFixed(2)"></span>
                            </div>
                            <div
                                style="display:flex;justify-content:space-between;padding-top:8px;border-top:1.5px dashed #cbd5e1;">
                                <span style="font-size:14px;font-weight:800;color:#0f172a;">Balance Due:</span>
                                <span style="font-size:16px;font-weight:900;color:#dc2626;"
                                    x-text="'Rs.' + Math.max(0, parseFloat(deliverOrder?.total || 0) - parseFloat(deliverOrder?.paid_amount || 0)).toFixed(2)"></span>
                            </div>
                        </div>

                        <div style="padding:20px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:24px;">
                            <label
                                style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.05em;">Payment
                                Method</label>
                            <div x-show="deliverOrder && parseFloat(deliverOrder.total) > parseFloat(deliverOrder.paid_amount)"
                                style="padding:8px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:12px;font-weight:700;color:#c2410c;margin-bottom:12px;">
                                ⚠️ Balance Due: Rs.<span
                                    x-text="Math.max(0, parseFloat(deliverOrder?.total || 0) - parseFloat(deliverOrder?.paid_amount || 0)).toFixed(2)"></span>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;">
                                <button type="button" @click="deliveryPaymentMethod = 'cash'"
                                    :style="deliveryPaymentMethod === 'cash' ? 'background:#f0fdf4;color:#15803d;border-color:#16a34a;border-width:3px;box-shadow:0 4px 14px rgba(22,163,74,0.2);' : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                    style="padding:12px 4px;font-size:12px;font-weight:800;border-style:solid;border-radius:14px;cursor:pointer;transition:all 0.15s;text-align:center;">
                                    💵 Cash
                                </button>
                                <button type="button" @click="deliveryPaymentMethod = 'card'"
                                    :style="deliveryPaymentMethod === 'card' ? 'background:#eff6ff;color:#1d4ed8;border-color:#2563eb;border-width:3px;' : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                    style="padding:12px 4px;font-size:12px;font-weight:800;border-style:solid;border-radius:14px;cursor:pointer;transition:all 0.15s;text-align:center;">
                                    💳 Card
                                </button>
                                <button type="button" @click="deliveryPaymentMethod = 'bank'"
                                    :style="deliveryPaymentMethod === 'bank' ? 'background:#f5f3ff;color:#6d28d9;border-color:#7c3aed;border-width:3px;' : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                    style="padding:12px 4px;font-size:12px;font-weight:800;border-style:solid;border-radius:14px;cursor:pointer;transition:all 0.15s;text-align:center;">
                                    🏦 Bank
                                </button>
                                <button type="button" @click="deliveryPaymentMethod = 'credit'"
                                    :style="deliveryPaymentMethod === 'credit' ? 'background:#fff7ed;color:#c2410c;border-color:#f97316;border-width:3px;box-shadow:0 4px 14px rgba(249,115,22,0.2);' : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                    style="padding:12px 4px;font-size:12px;font-weight:800;border-style:solid;border-radius:14px;cursor:pointer;transition:all 0.15s;text-align:center;">
                                    ⏳ Credit
                                </button>
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:4px;">
                            <button type="button" @click="confirmDelivery(false)"
                                style="flex:1;padding:14px;background:white;color:#16a34a;font-size:14px;font-weight:800;border:2px solid #16a34a;border-radius:12px;cursor:pointer;transition:all 0.15s;"
                                onmouseover="this.style.background='#f0fdf4'"
                                onmouseout="this.style.background='white'">✓ Complete</button>
                            <button type="button" @click="confirmDelivery(true)"
                                style="flex:1;padding:14px;background:#4f46e5;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(79,70,229,0.3);">🖨
                                Print & Deliver</button>
                        </div>
                    </div>
                </div>
            </div>
