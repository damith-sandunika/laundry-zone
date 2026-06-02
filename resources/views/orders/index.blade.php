<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 style="font-size:24px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 4px;">Order Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage and track customer laundry states</p>
            </div>
            <a href="{{ route('pos.index') }}" style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                New POS Order
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div x-data="{
        currentTab: 'all',
        selectedOrders: [],
        showBulkModal: false,
        bulkStatus: 'received',
        showReverseModal: false,
        reverseOrderId: null,
        reversePassword: '',
        reverseError: '',
        
        // Cubbet Modal (Done action)
        showCubbetModal: false,
        cubbetOrder: null,
        selectedCubby: '',
        
        // Delivery Modal
        showDeliverModal: false,
        deliverOrder: null,
        deliveryPaymentMethod: 'cash',

        ordersList: JSON.parse(atob('{{ base64_encode(json_encode($orders)) }}')),

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedOrders = this.filteredOrders.map(o => o.id);
            } else {
                this.selectedOrders = [];
            }
        },

        get filteredOrders() {
            if (this.currentTab === 'all') return this.ordersList;
            return this.ordersList.filter(o => o.status === this.currentTab);
        },

        get counts() {
            return {
                all: this.ordersList.length,
                received: this.ordersList.filter(o => o.status === 'received').length,
                done: this.ordersList.filter(o => o.status === 'done').length,
                delivered: this.ordersList.filter(o => o.status === 'delivered').length
            };
        },

        openReverse(orderId) {
            this.reverseOrderId = orderId;
            this.reversePassword = '';
            this.reverseError = '';
            this.showReverseModal = true;
        },

        async submitReverse() {
            this.reverseError = '';
            try {
                const res = await fetch(`/orders/${this.reverseOrderId}/reverse`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ password: this.reversePassword })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    this.reverseError = data.message || 'Authorization failed';
                }
            } catch (err) {
                this.reverseError = 'Connection error. Please try again.';
            }
        },

        async submitBulkUpdate() {
            try {
                const res = await fetch('{{ route('orders.bulkStatus') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        order_ids: this.selectedOrders,
                        status: this.bulkStatus
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Bulk update failed');
                }
            } catch (err) {
                alert('Connection error');
            }
        },

        openCubbetPopup(order) {
            this.cubbetOrder = order;
            this.selectedCubby = '';
            this.showCubbetModal = true;
        },
        
        async markAsDone() {
            if (!this.selectedCubby) { alert('Please select a cubbet'); return; }
            try {
                const res = await fetch(`/orders/${this.cubbetOrder.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ status: 'done', cubby_id: this.selectedCubby })
                });
                window.location.reload();
            } catch (err) { alert('Error updating status'); }
        },

        openDeliverPopup(order) {
            this.deliverOrder = order;
            this.deliveryPaymentMethod = 'cash';
            this.showDeliverModal = true;
        },

        async confirmDelivery() {
            try {
                // Submit delivery request
                const res = await fetch(`/orders/${this.deliverOrder.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ 
                        status: 'delivered', 
                        payment_method: this.deliveryPaymentMethod 
                    })
                });
                
                // Open print window automatically
                window.open(`/orders/${this.deliverOrder.id}/print`, '_blank');
                
                // Reload page to reflect changes
                window.location.reload();
            } catch (err) { alert('Error updating status'); }
        }

    }">

        <!-- STAT CARDS -->
        <div class="grid grid-cols-4 md:grid-cols-2 xl:grid-cols-4 gap-2 md:gap-4 mb-6">
            
            <!-- All Orders -->
            <div @click="currentTab = 'all'" 
                :class="currentTab === 'all' ? 'border-blue-500 bg-blue-50 md:shadow-[0_8px_20px_rgba(59,130,246,0.25)] md:scale-[1.02]' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col md:flex-row items-center justify-center md:justify-between p-2 md:px-6 md:py-4 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out">
                <div class="flex items-center gap-1 md:gap-4">
                    <div :class="currentTab === 'all' ? 'bg-blue-600 text-white shadow-md' : 'bg-blue-100 text-blue-600'" class="w-7 h-7 md:w-10 md:h-10 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <span class="hidden md:block text-sm font-bold text-gray-700 uppercase tracking-wider">All Orders</span>
                </div>
                <span class="text-lg md:text-3xl font-black mt-1 md:mt-0" :class="currentTab === 'all' ? 'text-blue-700' : 'text-gray-900'" x-text="counts.all"></span>
            </div>

            <!-- Received -->
            <div @click="currentTab = 'received'" 
                :class="currentTab === 'received' ? 'border-orange-500 bg-orange-50 md:shadow-[0_8px_20px_rgba(249,115,22,0.25)] md:scale-[1.02]' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col md:flex-row items-center justify-center md:justify-between p-2 md:px-6 md:py-4 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out">
                <div class="flex items-center gap-1 md:gap-4">
                    <div :class="currentTab === 'received' ? 'bg-orange-600 text-white shadow-md' : 'bg-orange-100 text-orange-600'" class="w-7 h-7 md:w-10 md:h-10 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <span class="hidden md:block text-sm font-bold text-gray-700 uppercase tracking-wider">Received</span>
                </div>
                <span class="text-lg md:text-3xl font-black mt-1 md:mt-0" :class="currentTab === 'received' ? 'text-orange-700' : 'text-gray-900'" x-text="counts.received"></span>
            </div>

            <!-- Ready / Done -->
            <div @click="currentTab = 'done'" 
                :class="currentTab === 'done' ? 'border-green-500 bg-green-50 md:shadow-[0_8px_20px_rgba(34,197,94,0.25)] md:scale-[1.02]' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col md:flex-row items-center justify-center md:justify-between p-2 md:px-6 md:py-4 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out">
                <div class="flex items-center gap-1 md:gap-4">
                    <div :class="currentTab === 'done' ? 'bg-green-600 text-white shadow-md' : 'bg-green-100 text-green-600'" class="w-7 h-7 md:w-10 md:h-10 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="hidden md:block text-sm font-bold text-gray-700 uppercase tracking-wider">Ready / Done</span>
                </div>
                <span class="text-lg md:text-3xl font-black mt-1 md:mt-0" :class="currentTab === 'done' ? 'text-green-700' : 'text-gray-900'" x-text="counts.done"></span>
            </div>

            <!-- Delivered -->
            <div @click="currentTab = 'delivered'" 
                :class="currentTab === 'delivered' ? 'border-gray-500 bg-gray-100 md:shadow-[0_8px_20px_rgba(100,116,139,0.25)] md:scale-[1.02]' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col md:flex-row items-center justify-center md:justify-between p-2 md:px-6 md:py-4 rounded-xl md:rounded-2xl border-2 cursor-pointer transition-all duration-300 ease-out">
                <div class="flex items-center gap-1 md:gap-4">
                    <div :class="currentTab === 'delivered' ? 'bg-gray-600 text-white shadow-md' : 'bg-gray-200 text-gray-600'" class="w-7 h-7 md:w-10 md:h-10 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <span class="hidden md:block text-sm font-bold text-gray-700 uppercase tracking-wider">Delivered</span>
                </div>
                <span class="text-lg md:text-3xl font-black mt-1 md:mt-0" :class="currentTab === 'delivered' ? 'text-gray-700' : 'text-gray-900'" x-text="counts.delivered"></span>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;display:flex;flex-direction:column;gap:0;">
            <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:24px;background:#f8fafc;">
                <div>
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Order List</h3>
                </div>
                <!-- Search and Bulk Action button -->
                <div style="display:flex;align-items:center;gap:12px;">
                    <form action="{{ route('orders.index') }}" method="GET" style="display:flex;gap:8px;margin:0;">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search ID or phone…"
                            style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="submit" style="padding:8px 14px;background:white;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:700;color:#334155;cursor:pointer;">Search</button>
                    </form>

                    <button x-show="selectedOrders.length > 0" @click="showBulkModal = true"
                        style="padding:8.5px 16px;background:#16a34a;color:white;font-size:13px;font-weight:800;border:none;border-radius:9px;cursor:pointer;display:flex;align-items:center;gap:6px;">
                        Bulk Update (<span x-text="selectedOrders.length"></span>)
                    </button>
                </div>
            </div>

        <!-- Table Container -->
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;color:#334155;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                        <th style="padding:14px 20px;width:40px;">
                            <input type="checkbox" @change="toggleSelectAll($event)" :checked="selectedOrders.length === filteredOrders.length && filteredOrders.length > 0" style="cursor:pointer;">
                        </th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order ID & Customer</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Date</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Pickup Date</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Cubbet (Location)</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">Price</th>
                        <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="order in filteredOrders" :key="order.id">
                        <tr :style="
                            order.status === 'received' ? 'background:#fffbeb;border-bottom:1px solid #fde68a;' :
                            order.status === 'done' ? 'background:#f0fdf4;border-bottom:1px solid #bbf7d0;' :
                            order.status === 'delivered' ? 'background:#f8fafc;border-bottom:1px solid #e2e8f0;' :
                            'background:white;border-bottom:1px solid #f1f5f9;'
                        " style="transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                            <td style="padding:12px 20px;">
                                <input type="checkbox" :value="order.id" x-model="selectedOrders" style="cursor:pointer;">
                            </td>
                            <td style="padding:12px 20px;">
                                <a :href="'/orders/' + order.id" style="font-size:13px;font-weight:800;color:#1d4ed8;text-decoration:none;display:block;" x-text="order.order_number"></a>
                                <div style="margin-top:2px;display:flex;flex-direction:column;gap:1px;">
                                    <span style="font-weight:700;color:#0f172a;" x-text="order.customer ? order.customer.name : 'Walk-in Customer'"></span>
                                    <span style="font-size:11px;color:#64748b;font-weight:600;" x-text="order.customer ? order.customer.phone : '0000000000'"></span>
                                </div>
                            </td>
                            <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                <span style="display:block;" x-text="new Date(order.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                <span style="font-size:11px;color:#94a3b8;font-weight:500;" x-text="new Date(order.created_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                            </td>
                            <td style="padding:12px 20px;font-weight:600;color:#475569;">
                                <template x-if="order.due_date">
                                    <div>
                                        <span style="display:block;" x-text="new Date(order.due_date).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})"></span>
                                        <span style="font-size:11px;color:#94a3b8;font-weight:500;" x-text="new Date(order.due_date).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'})"></span>
                                    </div>
                                </template>
                                <template x-if="!order.due_date">
                                    <span style="color:#cbd5e1;font-style:italic;font-size:11px;">No promised date</span>
                                </template>
                            </td>
                            <td style="padding:12px 20px;">
                                <span :style="
                                    order.status === 'received' ? 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;' :
                                    order.status === 'done' ? 'background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;' :
                                    order.status === 'delivered' ? 'background:#e2e8f0;color:#475569;border:1px solid #cbd5e1;' :
                                    'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;'
                                " style="font-size:11px;font-weight:800;padding:4px 9px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">
                                    <span x-text="order.status"></span>
                                </span>
                            </td>
                            <td style="padding:12px 20px;font-weight:700;">
                                <template x-if="order.cubby && order.cubby.cubby">
                                    <span style="color:#4f46e5;" x-text="order.cubby.cubby.name"></span>
                                </template>
                                <template x-if="!(order.cubby && order.cubby.cubby)">
                                    <span style="color:#94a3b8;font-style:italic;font-weight:500;">Not assigned</span>
                                </template>
                            </td>
                            <td style="padding:12px 20px;text-align:right;font-weight:800;color:#0f172a;font-size:13px;" x-text="'Rs.' + parseFloat(order.total).toFixed(2)"></td>
                            <td style="padding:12px 20px;text-align:center;">
                                <div style="display:inline-flex;gap:6px;align-items:center;">
                                    <a :href="'/orders/' + order.id" style="padding:5px 10px;background:white;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:8px;font-size:11.5px;font-weight:800;text-decoration:none;transition:all 0.15s;box-shadow:0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='white'">View</a>
                                    
                                    <template x-if="currentTab === 'received' && order.status === 'received'">
                                        <button @click="openCubbetPopup(order)" style="padding:5px 10px;background:#16a34a;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(22,163,74,0.3);transition:transform 0.1s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            Mark Done
                                        </button>
                                    </template>

                                    <template x-if="currentTab === 'done' && order.status === 'done'">
                                        <button @click="openDeliverPopup(order)" style="padding:5px 10px;background:#4f46e5;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(79,70,229,0.3);transition:transform 0.1s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                            Deliver
                                        </button>
                                    </template>

                                    <button @click="openReverse(order.id)" style="padding:5px 10px;background:white;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='white'">
                                        Reverse
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredOrders.length === 0">
                        <td colspan="8" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                            No orders found matching this status or query.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ── Bulk Status Update Modal ── -->
        <div x-cloak x-show="showBulkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" @click.away="showBulkModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Bulk Update Orders</h3>
                    <button @click="showBulkModal = false" style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                </div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Target Status</label>
                        <select x-model="bulkStatus" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                            <option value="received">Received</option>
                            <option value="done">Done</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button @click="showBulkModal = false" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                        <button @click="submitBulkUpdate" style="flex:1;padding:11px;background:#16a34a;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Apply Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Reverse Authorization Modal ── -->
        <div x-cloak x-show="showReverseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" @click.away="showReverseModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Reverse Bill Authorization</h3>
                    <button @click="showReverseModal = false" style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                </div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                    <p style="font-size:12.5px;color:#64748b;margin:0;font-weight:500;line-height:1.4;">Enter the admin or super admin password to reverse this bill and set its status back to received.</p>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Admin Password</label>
                        <input type="password" x-model="reversePassword" placeholder="••••••••"
                            style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;"
                            onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div x-show="reverseError" style="font-size:12px;color:#dc2626;font-weight:700;" x-text="reverseError"></div>
                    <div style="display:flex;gap:10px;">
                        <button @click="showReverseModal = false" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                        <button @click="submitReverse" style="flex:1;padding:11px;background:#ef4444;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Authorize Reverse</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Mark as Done (Cubbet Assign) Modal ── -->
        <div x-cloak x-show="showCubbetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showCubbetModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f0fdf4;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#16a34a;margin:0;">Mark Order as Done</h3>
                        <p style="font-size:12px;color:#15803d;margin:2px 0 0;font-weight:600;" x-text="'Order #' + cubbetOrder?.order_number"></p>
                    </div>
                    <button @click="showCubbetModal = false" style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div style="padding:16px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;">
                        <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Assign to a Cubbet</label>
                        <select x-model="selectedCubby" style="width:100%;padding:12px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px;font-weight:700;outline:none;background:white;color:#0f172a;">
                            <option value="" style="color:#94a3b8;">-- Select Empty Cubbet --</option>
                            @foreach($cubbies as $cubby)
                                <option value="{{ $cubby->id }}" style="color:#0f172a;">{{ $cubby->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px;color:#64748b;margin:8px 0 0;font-weight:600;">The system will send an automated WhatsApp message to <span style="color:#1d4ed8;font-weight:800;" x-text="cubbetOrder?.customer?.name"></span>.</p>
                    </div>

                    <div style="display:flex;gap:12px;">
                        <button type="button" @click="showCubbetModal = false" style="flex:1;padding:12px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="markAsDone()" style="flex:1;padding:12px;background:#16a34a;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);">✓ Confirm Done</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Delivery & Payment Modal ── -->
        <div x-cloak x-show="showDeliverModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showDeliverModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eef2ff;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#4f46e5;margin:0;">Deliver Order</h3>
                        <p style="font-size:12px;color:#4338ca;margin:2px 0 0;font-weight:600;" x-text="'Handing over Order #' + deliverOrder?.order_number"></p>
                    </div>
                    <button @click="showDeliverModal = false" style="background:none;border:none;cursor:pointer;color:#4f46e5;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:700;color:#64748b;">Total Bill:</span>
                            <span style="font-size:14px;font-weight:900;color:#0f172a;" x-text="'Rs.' + parseFloat(deliverOrder?.total || 0).toFixed(2)"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                            <span style="font-size:13px;font-weight:700;color:#64748b;">Paid Amount:</span>
                            <span style="font-size:14px;font-weight:900;color:#16a34a;" x-text="'Rs.' + parseFloat(deliverOrder?.paid_amount || 0).toFixed(2)"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding-top:8px;border-top:1.5px dashed #cbd5e1;">
                            <span style="font-size:14px;font-weight:800;color:#0f172a;">Balance Due:</span>
                            <span style="font-size:16px;font-weight:900;color:#dc2626;" x-text="'Rs.' + Math.max(0, parseFloat(deliverOrder?.total || 0) - parseFloat(deliverOrder?.paid_amount || 0)).toFixed(2)"></span>
                        </div>
                    </div>

                    <template x-if="deliverOrder && parseFloat(deliverOrder.total) > parseFloat(deliverOrder.paid_amount)">
                        <div style="padding:16px;background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;">
                            <label style="display:block;font-size:12px;font-weight:800;color:#b45309;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Payment Collection</label>
                            <div style="display:flex;gap:10px;">
                                <button type="button" @click="deliveryPaymentMethod = 'cash'" :style="deliveryPaymentMethod === 'cash' ? 'background:#16a34a;color:white;border-color:#16a34a;' : 'background:white;color:#334155;border-color:#cbd5e1;'" style="flex:1;padding:10px;font-size:13px;font-weight:800;border:2px solid;border-radius:10px;cursor:pointer;transition:all 0.15s;">💵 CASH</button>
                                <button type="button" @click="deliveryPaymentMethod = 'card'" :style="deliveryPaymentMethod === 'card' ? 'background:#2563eb;color:white;border-color:#2563eb;' : 'background:white;color:#334155;border-color:#cbd5e1;'" style="flex:1;padding:10px;font-size:13px;font-weight:800;border:2px solid;border-radius:10px;cursor:pointer;transition:all 0.15s;">💳 CARD</button>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="deliverOrder && parseFloat(deliverOrder.total) <= parseFloat(deliverOrder.paid_amount)">
                        <div style="padding:16px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;text-align:center;">
                            <span style="font-size:13px;font-weight:800;color:#16a34a;">Order is fully paid!</span>
                        </div>
                    </template>

                    <div style="display:flex;gap:12px;margin-top:4px;">
                        <button type="button" @click="showDeliverModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="confirmDelivery()" style="flex:1;padding:14px;background:#4f46e5;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(79,70,229,0.3);">🖨 Print & Deliver</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
