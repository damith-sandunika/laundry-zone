<td style="padding:12px 20px;text-align:center;">
    <div style="display:flex;flex-direction:column;gap:6px;align-items:center;">
        <template x-if="order.status === 'received' || getReceipts(order).length > 0">
            <div style="display:flex;flex-direction:column;gap:6px;align-items:center;background:#f8fafc;padding:10px;border-radius:10px;border:1px solid #e2e8f0;width:100%;">
                <div style="font-size:11px;font-weight:800;color:#334155;">
                    Paid: <span style="color:#16a34a;">LKR <span x-text="parseFloat(order.paid_amount || 0).toFixed(2)"></span></span>
                    /
                    <span style="color:#0f172a;">LKR <span x-text="parseFloat(order.total || 0).toFixed(2)"></span></span>
                </div>

                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;width:100%;">
                    <!-- Slips -->
                    <template x-for="(receipt, idx) in getReceipts(order)" :key="idx">
                        <button @click="openSlipModal(receipt, order)" :style="
                                receipt.status === 'pending' ? 'border-color:#fbbf24;background:#fffbeb;color:#b45309;' : 
                                receipt.status === 'rejected' ? 'border-color:#fca5a5;background:#fef2f2;color:#b91c1c;text-decoration:line-through;' : 
                                'border-color:#c7d2fe;background:white;color:#4f46e5;'
                            "
                            style="padding:5px 12px;border-width:1.5px;border-style:dashed;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.02);transition:all 0.15s;display:flex;align-items:center;gap:6px;"
                            onmouseover="this.style.transform='scale(1.05)'; this.style.borderStyle='solid'"
                            onmouseout="this.style.transform='scale(1)'; this.style.borderStyle='dashed'">
                            <i class="fa-solid fa-file-invoice"></i> <span x-text="'Slip ' + (idx + 1)"></span>
                            <template x-if="receipt.status === 'pending'"><i class="fa-solid fa-circle-exclamation" style="font-size:10px;"></i></template>
                            <template x-if="receipt.status === 'rejected'"><i class="fa-solid fa-circle-xmark" style="font-size:10px;"></i></template>
                            <template x-if="receipt.status === 'completed'"><i class="fa-solid fa-circle-check" style="font-size:10px;"></i></template>
                        </button>
                    </template>

                    <template x-if="order.status === 'received'">
                        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;">
                            <button @click="window.location.href = '/workshop/orders/' + order.order_number + '/finalize-bill'"
                                style="padding:5px 12px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                                onmouseover="this.style.transform='scale(1.05)'"
                                onmouseout="this.style.transform='scale(1)'">
                                Finalize
                            </button>

                            <button @click="bulkStatus = 'dispatched_to_workshop'; selectedOrders = [order.id]; showBulkModal = true;"
                                style="padding:5px 12px;background:linear-gradient(to right, #eab308, #d97706);color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                onmouseover="this.style.transform='scale(1.05)'"
                                onmouseout="this.style.transform='scale(1)'">
                                Dispatch
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="order.status !== 'received'">
            <div style="display:inline-flex;gap:6px;align-items:center;">
                <a :href="'/orders/view/' + order.order_number"
                    style="padding:5px 10px;background:white;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:8px;font-size:11.5px;font-weight:800;text-decoration:none;transition:all 0.15s;box-shadow:0 1px 2px rgba(0,0,0,0.05);"
                    onmouseover="this.style.background='#eff6ff'"
                    onmouseout="this.style.background='white'">View</a>

                <template x-if="currentTab === 'arriving' && order.status === 'returned_from_workshop'">
                    <div style="display:inline-flex;gap:6px;align-items:center;">
                        <button @click="openCubbetPopup(order)"
                            style="padding:5px 10px;background:#16a34a;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(22,163,74,0.3);transition:transform 0.1s;"
                            onmouseover="this.style.transform='scale(1.05)'"
                            onmouseout="this.style.transform='scale(1)'">
                            ✅ Confirm Arrival
                        </button>
                    </div>
                </template>

                <template x-if="currentTab === 'done' && order.status === 'done'">
                    <button @click="(order.delivery_appointment && order.delivery_appointment.delivery_preference !== 'pickup_from_branch') ? openDeliveryDispatchPopup(order) : openDeliverPopup(order)"
                        style="padding:5px 10px;background:#0ea5e9;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(14,165,233,0.3);transition:transform 0.1s;"
                        onmouseover="this.style.transform='scale(1.05)'"
                        onmouseout="this.style.transform='scale(1)'">
                        <span x-text="(order.delivery_appointment && order.delivery_appointment.delivery_preference !== 'pickup_from_branch') ? 'Dispatch Delivery' : 'Deliver'"></span>
                    </button>
                </template>
            </div>
        </template>
    </div>
</td>
