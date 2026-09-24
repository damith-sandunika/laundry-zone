    <!-- Status Banner -->
    <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;
        {{ $order->status === 'received' ? 'background:#fffbeb;color:#d97706;border:1.5px solid #fde68a;' :
    ($order->status === 'done' ? 'background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;' :
        ($order->status === 'delivered' ? 'background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;' :
            'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;')) }}">

        <div style="display:flex;align-items:center;gap:10px;">
            <span>Order Status:</span>
            <span style="font-size:16px;font-weight:900;">
                <template x-if="dispatch">
                    <span x-text="dispatch.status.replace(/_/g, ' ')"></span>
                </template>
                <template x-if="!dispatch">
                    <span>{{ $order->status }}</span>
                </template>
            </span>
        </div>

        <!-- Workflow Actions -->
        <template x-if="dispatch">
            <div style="display:inline-flex;gap:6px;align-items:center;">
                <!-- Dispatched to Workshop -->
                <template x-if="dispatch.status === 'dispatched_to_workshop'">
                    <button @click="workshopAction('receive')"
                        style="padding:8px 16px;background:#eab308;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        Receive at Workshop
                    </button>
                </template>

                <!-- Received at Workshop (To Wash) -->
                <template x-if="dispatch.status === 'received_at_workshop'">
                    <div style="display:flex;gap:6px;">
                        <button @click="openFinalizePage()"
                            style="padding:8px 16px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            Finalize Bill
                        </button>
                        <button @click="workshopAction('start-processing')"
                            style="padding:8px 16px;background:#eab308;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-play"></i> Start Washing
                        </button>
                    </div>
                </template>

                <!-- Processing (Washing) -->
                <template x-if="dispatch.status === 'processing'">
                    <button @click="showDoneModal = true"
                        style="padding:8px 16px;background:#10b981;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.3);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-check"></i> Mark Done
                    </button>
                </template>

                <!-- Completed at Workshop -->
                <template x-if="dispatch.status === 'completed_at_workshop'">
                    <div style="display:flex;gap:6px;">
                        <template x-if="dispatch.order.branch_id == {{ auth()->user()->branch_id ?? 'null' }} || dispatch.order.branch_id == dispatch.workshop_branch_id || dispatch.order.is_delivery_order || {{ auth()->user()->role === 'super_admin' ? 'true' : 'false' }}">
                            <div style="display:flex;gap:6px;">
                                <template x-if="dispatch.order.is_delivery_order">
                                    <button @click="showDeliveryModal = true"
                                        style="padding:8px 16px;background:linear-gradient(to right,#3b82f6,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fa-solid fa-truck"></i> Assign Rider
                                    </button>
                                </template>
                                <template x-if="!dispatch.order.is_delivery_order">
                                    <button @click="showHandoverModal = true"
                                        style="padding:8px 16px;background:linear-gradient(to right,#10b981,#059669);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fa-solid fa-box-open"></i> Handover to Cust
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="dispatch.order.branch_id != {{ auth()->user()->branch_id ?? 'null' }} && dispatch.order.branch_id != dispatch.workshop_branch_id && !dispatch.order.is_delivery_order && !{{ auth()->user()->role === 'super_admin' ? 'true' : 'false' }}">
                            <button @click="workshopAction('return')"
                                style="padding:8px 16px;background:linear-gradient(to right,#8b5cf6,#6d28d9);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(139,92,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
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
        </template>
    </div>
