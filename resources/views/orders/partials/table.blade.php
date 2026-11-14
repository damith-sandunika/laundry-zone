            <!-- Table Container -->
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
                    <thead>
                        <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Order Number & Customer</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Order Create Date</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Delivery Date</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Status</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Cubbet (Location)</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">
                                Price</th>
                            @if(auth()->user()->role !== 'super_admin')
                                <th
                                    style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">
                                    Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in ordersList" :key="order.id">
                            <tr :style="
                            order.status === 'received' ? 'background:#fffbeb;border-bottom:1px solid #fde68a;' :
                            order.status === 'dispatched_to_workshop' ? 'background:#eff6ff;border-bottom:1px solid #bfdbfe;' :
                            order.status === 'done' ? 'background:#f0fdf4;border-bottom:1px solid #bbf7d0;' :
                            order.status === 'returned_from_workshop' ? 'background:#fdf4ff;border-bottom:1px solid #f5d0fe;' :
                            (order.status === 'delivered' || order.status === 'completed') ? 'background:#f8fafc;border-bottom:1px solid #e2e8f0;' :
                            'background:white;border-bottom:1px solid #f1f5f9;'
                        " style="transition:all 0.1s;" onmouseover="this.style.opacity='0.85'"
                                onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <a :href="'/orders/view/' + order.order_number"
                                        style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:none;display:block;"
                                        x-text="order.order_number"></a>
                                    <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                        <span style="font-weight:700;color:#0f172a;"
                                            x-text="order.customer ? order.customer.name : 'Walk-in Customer'"></span>
                                        <span style="font-size:11px;color:#64748b;font-weight:600;"
                                            x-text="order.customer ? order.customer.phone : '0000000000'"></span>
                                    </div>
                                </td>
                                <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                    <span style="display:block;"
                                        x-text="new Date(order.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                    <span style="font-size:11px;color:#94a3b8;font-weight:500;"
                                        x-text="new Date(order.created_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                                </td>
                                <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                    <template x-if="order.due_date">
                                        <div>
                                            <span style="display:block;"
                                                x-text="new Date(order.due_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                            <span style="font-size:11px;color:#94a3b8;font-weight:500;"
                                                x-text="new Date(order.due_date).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                                        </div>
                                    </template>
                                    <template x-if="!order.due_date">
                                        <span style="color:#cbd5e1;font-style:italic;font-size:11px;">No promised
                                            date</span>
                                    </template>
                                </td>
                                <td style="padding:12px 20px;">
                                    <span :style="
                                    order.status === 'received' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' :
                                    order.status === 'dispatched_to_workshop' ? 'background:#dbeafe;color:#1d4ed8;border:1px solid #bfdbfe;' :
                                    order.status === 'done' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' :
                                    order.status === 'returned_from_workshop' ? 'background:#fae8ff;color:#a21caf;border:1px solid #f5d0fe;' :
                                    (order.status === 'delivered' || order.status === 'completed') ? 'background:#e2e8f0;color:#475569;border:1px solid #cbd5e1;' :
                                    'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;'
                                " style="font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;display:inline-block;">
                                        <template x-if="order.status === 'dispatched_to_workshop'">
                                            <span
                                                x-text="'Sent to ' + (order.workshop ? order.workshop.name : 'Workshop')"></span>
                                        </template>
                                        <template x-if="order.status === 'returned_from_workshop'">
                                            <span
                                                x-text="'Arriving from ' + (order.workshop ? order.workshop.name : 'Workshop')"></span>
                                        </template>
                                        <template
                                            x-if="order.status !== 'dispatched_to_workshop' && order.status !== 'returned_from_workshop'">
                                            <span x-text="order.status"></span>
                                        </template>
                                    </span>
                                </td>
                                <td style="padding:12px 20px;font-weight:700;">
                                    <template x-if="order.cubby && order.cubby.cubby">
                                        <span style="color:#4f46e5;" x-text="order.cubby.cubby.name"></span>
                                    </template>
                                    <template x-if="!(order.cubby && order.cubby.cubby)">
                                        <span style="color:#94a3b8;font-style:italic;font-weight:500;">Not
                                            assigned</span>
                                    </template>
                                </td>
                                <td style="padding:12px 20px;text-align:right;">
                                    <div style="font-size:13px;font-weight:800;color:#0f172a;" x-text="parseFloat(order.total) === 0 ? 'Package' : 'Rs. ' + parseFloat(order.total).toFixed(2)"></div>
                                    <template x-if="parseFloat(order.total) > 0">
                                        <div style="margin-top:2px;font-size:11px;font-weight:700;display:flex;justify-content:flex-end;">
                                            <template x-if="parseFloat(order.total || 0) <= parseFloat(order.paid_amount || 0)">
                                                <span style="color:#16a34a;background:#dcfce7;padding:2px 6px;border-radius:4px;">Fully Paid</span>
                                            </template>
                                            <template x-if="parseFloat(order.total || 0) > parseFloat(order.paid_amount || 0)">
                                                <div style="display:flex;align-items:center;">
                                                    <span style="color:#16a34a;" x-text="'Rs. ' + parseFloat(order.paid_amount || 0).toFixed(2)"></span>
                                                    <span style="color:#94a3b8;margin:0 4px;">/</span>
                                                    <span style="color:#dc2626;" x-text="'Rs. ' + Math.max(0, parseFloat(order.total || 0) - parseFloat(order.paid_amount || 0)).toFixed(2)"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </td>
                                @if(auth()->user()->role !== 'super_admin')
                                    @include('orders.partials.table_actions')
                                @endif
                            </tr>
                        </template>
                        <tr x-show="ordersList.length === 0">
                            <td colspan="7"
                                style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No orders found matching this status or query.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($orders instanceof \Illuminate\Pagination\LengthAwarePaginator && $orders->hasPages())
                <div class="mt-4 px-4 bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
                    {{ $orders->links() }}
                </div>
            @endif

            <!-- ── Dispatch Order Modal ── -->