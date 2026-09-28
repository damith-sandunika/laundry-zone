        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                    <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                        <tr>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Dispatch Time</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Price</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="dispatch in filteredDispatches" :key="dispatch.id">
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <a :href="dispatch.order ? '/orders/view/' + dispatch.order.order_number : '#'" style="font-size:13px;font-weight:800;color:#1d4ed8;display:block;text-decoration:none;" x-text="(dispatch.order.is_delivery_order ? '[D] ' : '') + '#' + dispatch.order.order_number" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"></a>
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-weight:700;color:#0f172a;" x-text="dispatch.order.customer ? dispatch.order.customer.name : 'Unknown Customer'"></span>
                                    </div>
                                </td>
                                <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                    <span style="display:block;" x-text="new Date(dispatch.dispatched_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                    <span style="font-size:11px;color:#94a3b8;font-weight:500;" x-text="new Date(dispatch.dispatched_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                                </td>
                                <td style="padding:12px 20px;font-weight:700;color:#4f46e5;">
                                    <span x-text="dispatch.branch ? dispatch.branch.name : 'Unknown'"></span>
                                </td>
                                <td style="padding:12px 20px;">
                                    <div style="font-size:13px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(dispatch.order.total || 0).toFixed(2)"></div>
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-size:11px;font-weight:700;color:#16a34a;" x-text="'Paid: Rs. ' + parseFloat(dispatch.order.paid_amount || 0).toFixed(2)"></span>
                                        <span style="font-size:11px;font-weight:700;color:#dc2626;" x-text="'Pending: Rs. ' + Math.max(0, parseFloat(dispatch.order.total || 0) - parseFloat(dispatch.order.paid_amount || 0)).toFixed(2)"></span>
                                    </div>
                                </td>
                                <td style="padding:12px 20px;">
                                    <span :style="
                                        dispatch.status === 'dispatched_to_workshop' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' :
                                        dispatch.status === 'received_at_workshop' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' :
                                        dispatch.status === 'processing' ? 'background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;' :
                                        dispatch.status === 'completed_at_workshop' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' :
                                        dispatch.status === 'returned_to_branch' ? 'background:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;' :
                                        'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;'
                                    " style="font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">
                                        <span x-text="dispatch.status.replace(/_/g, ' ')"></span>
                                    </span>
                                </td>
                                <td style="padding:12px 20px;text-align:center;">
                                    <div style="display:inline-flex;gap:6px;align-items:center;justify-content:center;width:100%;">
                                        <!-- Actions based on status -->
                                        <template x-if="dispatch.status === 'dispatched_to_workshop'">
                                            <button @click="workshopAction(dispatch.id, 'receive')"
                                                style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Receive at Workshop
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'received_at_workshop'">
                                            <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:center;">
                                                <button @click="openFinalizePage(dispatch)"
                                                    style="padding:5px 10px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                                                    onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                    Finalize Bill
                                                </button>

                                                <button @click="workshopAction(dispatch.id, 'start-processing')"
                                                    style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                    onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                    Start Washing
                                                </button>
                                            </div>
                                        </template>

                                        <template x-if="dispatch.status === 'processing'">
                                            <button @click="openDoneModal(dispatch)"
                                                style="padding:5px 10px;background:#10b981;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Mark Done
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'completed_at_workshop'">
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                                <template x-if="dispatch.order.branch_id === dispatch.workshop_branch_id">
                                                    <div style="display:flex;gap:6px;">
                                                        <template x-if="dispatch.order.is_delivery_order">
                                                            <button @click="openDeliveryModal(dispatch)"
                                                                style="padding:5px 14px;background:linear-gradient(to right,#3b82f6,#2563eb);color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                                <i class="fa-solid fa-truck"></i> Assign to Delivery
                                                            </button>
                                                        </template>
                                                        <template x-if="!dispatch.order.is_delivery_order">
                                                            <button @click="openHandoverModal(dispatch)"
                                                                style="padding:5px 14px;background:linear-gradient(to right,#10b981,#059669);color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                                <i class="fa-solid fa-box-open"></i> Handover to Cust
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="dispatch.order.branch_id !== dispatch.workshop_branch_id">
                                                    <button @click="workshopAction(dispatch.id, 'return')"
                                                        style="padding:5px 14px;background:linear-gradient(to right,#8b5cf6,#6d28d9);color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(139,92,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Send to Branch
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        
                                        <template x-if="dispatch.status === 'returned_to_branch'">
                                            <span style="font-size:11px;color:#94a3b8;font-weight:600;">Awaiting Branch...</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredDispatches.length === 0">
                            <td colspan="7" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No records found in this category.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($dispatches->hasPages())
                <div style="padding:16px 24px;border-top:1px solid #f1f5f9;">
                    {{ $dispatches->appends(request()->query())->links() }}
                </div>
            @endif
        </div>