<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="ph-title">Order
                    Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage and track customer laundry
                    states</p>
            </div>
            <a href="{{ route('pos.index') }}"
                style="display:flex;align-items:center;gap:7px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 2px 10px rgba(29,78,216,0.3);transition:all 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                New POS Order
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div
            style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom:16px;padding:12px 18px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;font-size:13px;font-weight:700;color:#dc2626;">
            <div style="display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <script>
        window.__ordersPageData = {
            riders: @js($riders),
            categories: @js($categories),
            orders: @js($orders),
            minimumDeliveryCost: {{ (float)($minimumDeliveryCost ?? 0) }},
            routes: {
                bulkDispatch: '{{ route('orders.bulkDispatchWorkshop') }}',
                bulkStatus: '{{ route('orders.bulkStatus') }}',
            }
        };
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ordersPage', () => ({
                currentTab: 'received',
                selectedOrders: [],
                showBulkModal: false,
                bulkStatus: 'done',
                bulkCubbyId: '',
                bulkWorkshopId: '',
                showReverseModal: false,
                reverseOrderId: null,
                reversePassword: '',
                reverseError: '',
                showCubbetModal: false,
                cubbetOrder: null,
                selectedCubby: '',
                showDeliverModal: false,
                deliverOrder: null,
                deliveryPaymentMethod: 'cash',
                showDispatchDeliveryModal: false,
                deliveryMethod: 'rider',
                deliveryRiderId: '',
                deliveryPickmeCost: '',
                isDispatchingDelivery: false,
                riders: window.__ordersPageData.riders,
                showEditModal: false,
                editOrderData: null,
                editCart: [],
                editDiscount: 0,
                editAdvanceCashAmount: '',
                selectedServiceToAdd: '',
                allCategories: window.__ordersPageData.categories,
                minimumDeliveryCost: window.__ordersPageData.minimumDeliveryCost,
                ordersList: window.__ordersPageData.orders,
                showPaymentModal: false,
                paymentOrder: null,
                paymentAmountType: 'full',
                customPaymentAmount: '',
                isRequestingPayment: false,
                showSlipModal: false,
                selectedSlip: null,
                selectedOrder: null,

                get filteredOrders() {
                    if (this.currentTab === 'all') return this.ordersList;
                    if (this.currentTab === 'received') return this.ordersList.filter(o => o.status === 'received' || o.status === 'dispatched_to_workshop');
                    if (this.currentTab === 'arriving') return this.ordersList.filter(o => o.status === 'returned_from_workshop');
                    if (this.currentTab === 'done') return this.ordersList.filter(o => o.status === 'done');
                    if (this.currentTab === 'delivered') return this.ordersList.filter(o => o.status === 'delivered' || o.status === 'completed');
                    return this.ordersList.filter(o => o.status === this.currentTab);
                },

                get counts() {
                    return {
                        all: this.ordersList.length,
                        received: this.ordersList.filter(o => o.status === 'received' || o.status === 'dispatched_to_workshop').length,
                        processing: this.ordersList.filter(o => o.status === 'processing' || o.status === 'completed_at_workshop').length,
                        arriving: this.ordersList.filter(o => o.status === 'returned_from_workshop').length,
                        done: this.ordersList.filter(o => o.status === 'done').length,
                        delivered: this.ordersList.filter(o => o.status === 'delivered' || o.status === 'completed').length
                    };
                },

                openPaymentModal(order) { this.paymentOrder = order; this.paymentAmountType = 'full'; this.customPaymentAmount = ''; this.showPaymentModal = true; },
                openSlipModal(receipt, order) { this.selectedSlip = receipt; this.selectedOrder = order; this.showSlipModal = true; },
                openReverse(orderId) { this.reverseOrderId = orderId; this.reversePassword = ''; this.reverseError = ''; this.showReverseModal = true; },
                openCubbetPopup(order) { this.cubbetOrder = order; this.selectedCubby = ''; this.showCubbetModal = true; },
                openDeliverPopup(order) { this.deliverOrder = order; this.deliveryPaymentMethod = 'cash'; this.showDeliverModal = true; },
                openDeliveryDispatchPopup(order) { this.deliverOrder = order; this.deliveryMethod = 'rider'; this.deliveryRiderId = ''; this.deliveryPickmeCost = ''; this.deliveryPaymentMethod = 'cash'; this.isDispatchingDelivery = false; this.showDispatchDeliveryModal = true; },

                getReceipts(order) {
                    if (!order || !order.payment_transactions) return [];
                    return order.payment_transactions.filter(p => p.receipt_path).map(p => ({
                        id: p.id, amount: p.amount, url: p.receipt_path.startsWith('http') ? p.receipt_path : '/storage/' + p.receipt_path,
                        status: p.status, reject_reason: p.reject_reason,
                        created_at: p.created_at ? new Date(p.created_at).toLocaleString() : ''
                    }));
                },

                toggleSelectAll(e) {
                    this.selectedOrders = e.target.checked ? this.filteredOrders.map(o => o.id) : [];
                },

                async sendPaymentRequest() {
                    if (!this.paymentOrder) return;
                    this.isRequestingPayment = true;
                    try {
                        const res = await fetch('/orders/' + this.paymentOrder.id + '/request-payment', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                            body: JSON.stringify({ amount_type: this.paymentAmountType, custom_amount: this.customPaymentAmount })
                        });
                        const data = await res.json();
                        if (data.success) { alert('Payment Request Sent successfully via WhatsApp.'); this.showPaymentModal = false; }
                        else { alert(data.message || 'Failed to send payment request.'); }
                    } catch (err) { alert('Connection error'); }
                    this.isRequestingPayment = false;
                },

                async submitReverse() {
                    this.reverseError = '';
                    try {
                        const res = await fetch('/orders/' + this.reverseOrderId + '/reverse', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ password: this.reversePassword })
                        });
                        const data = await res.json();
                        if (data.success) { window.location.reload(); }
                        else { this.reverseError = data.message || 'Authorization failed'; }
                    } catch (err) { this.reverseError = 'Connection error. Please try again.'; }
                },

                async submitBulkUpdate() {
                    try {
                        if (this.bulkStatus === 'dispatched_to_workshop') {
                            if (!this.bulkWorkshopId) { alert('Please select a workshop'); return; }
                            await fetch(window.__ordersPageData.routes.bulkDispatch, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                                body: JSON.stringify({ order_ids: this.selectedOrders, workshop_branch_id: this.bulkWorkshopId, dispatch_notes: 'Bulk dispatched from branch' })
                            });
                            window.location.reload();
                            return;
                        }
                        const res = await fetch(window.__ordersPageData.routes.bulkStatus, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ order_ids: this.selectedOrders, status: this.bulkStatus, cubby_id: this.bulkStatus === 'done' ? this.bulkCubbyId : null })
                        });
                        const data = await res.json();
                        if (data.success) { window.location.reload(); }
                        else { alert('Bulk update failed'); }
                    } catch (err) { window.location.reload(); }
                },

                async markAsDone() {
                    if (!this.selectedCubby) { alert('Please select a cubbet'); return; }
                    try {
                        const res = await fetch('/orders/' + this.cubbetOrder.id + '/status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                            },
                            body: JSON.stringify({ status: 'done', cubby_id: this.selectedCubby })
                        });
                        const data = await res.json();
                        if (data.success) { window.location.reload(); }
                        else { alert('Error: ' + (data.message || 'Failed to update status')); }
                    } catch (err) { alert('Error updating status: ' + err); }
                },


                async submitDeliveryDispatch() {
                    if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
                    if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');
                    this.isDispatchingDelivery = true;
                    try {
                        if (this.deliverOrder.status === 'returned_from_workshop' && this.deliverOrder.workshop_dispatch_id) {
                            await fetch('/workshop/dispatches/' + this.deliverOrder.workshop_dispatch_id + '/received-at-branch', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                            });
                        }
                        const res = await fetch('/orders/' + this.deliverOrder.id + '/dispatch-delivery', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ delivery_method: this.deliveryMethod, rider_id: this.deliveryRiderId, pickme_cost: this.deliveryPickmeCost, payment_method: this.deliveryPaymentMethod })
                        });
                        const data = await res.json();
                        if (data.success) { window.location.reload(); }
                        else { alert(data.message || 'Error dispatching delivery'); this.isDispatchingDelivery = false; }
                    } catch (err) { alert('Connection error'); this.isDispatchingDelivery = false; }
                },

                async confirmDelivery(shouldPrint = true) {
                    try {
                        const res = await fetch('/orders/' + this.deliverOrder.id + '/status', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ status: 'delivered', payment_method: this.deliveryPaymentMethod })
                        });
                        const data = await res.json();
                        if (!data.success) { alert('Error: ' + (data.message || 'Failed to update status')); return; }
                        if (shouldPrint && data.print_bill_id) { window.open('/orders/' + data.print_bill_id + '/print', '_blank'); }
                        else if (shouldPrint) { window.open('/orders/' + this.deliverOrder.order_number + '/print', '_blank'); }
                        window.location.reload();
                    } catch (err) { alert('Error updating status'); }
                },

                openEdit(order) {
                    window.location.href = '/orders/' + order.id + '/edit';
                },
            }));
        });
    </script>

    <div x-data="ordersPage()">
        <!-- STAT CARDS -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 md:gap-4 mb-6">

            <!-- Pending Dispatch -->
            <div @click="currentTab = 'received'"
                :class="currentTab === 'received' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'received' ? 'text-orange-700' : 'text-gray-900'" x-text="counts.received"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">New</span>
            </div>

            <!-- At Workshop -->
            <div @click="currentTab = 'processing'"
                :class="currentTab === 'processing' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'processing' ? 'text-yellow-700' : 'text-gray-900'" x-text="counts.processing"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">At Workshop</span>
            </div>

            <!-- Arriving -->
            <div @click="currentTab = 'arriving'"
                :class="currentTab === 'arriving' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'arriving' ? 'text-blue-700' : 'text-gray-900'" x-text="counts.arriving"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Arriving</span>
            </div>

            <!-- Ready for Pickup -->
            <div @click="currentTab = 'done'"
                :class="currentTab === 'done' ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'done' ? 'text-green-700' : 'text-gray-900'" x-text="counts.done"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Ready</span>
            </div>

            <!-- Delivered -->
            <div @click="currentTab = 'delivered'"
                :class="currentTab === 'delivered' ? 'border-gray-500 bg-gray-100' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                class="flex flex-col items-center justify-center p-2 rounded-xl border-2 cursor-pointer transition-all duration-300">
                <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'delivered' ? 'text-gray-700' : 'text-gray-900'" x-text="counts.delivered"></span>
                <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Delivered</span>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div
            style="background:white;border-radius:16px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;display:flex;flex-direction:column;gap:0;">
            <div
                style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:24px;background:#f8fafc;">
                <div>
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Order List</h3>
                </div>
                <!-- Search and Bulk Action button -->
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    @if(auth()->user()->role === 'super_admin' && isset($allBranches) && $allBranches->count() > 0)
                        <form action="{{ route('orders.index') }}" method="GET" style="margin:0;display:flex;gap:8px;">
                            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                            <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;" title="Start Date">
                            <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;" title="End Date">
                            <select name="branch_id" onchange="this.form.submit()" style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;background:white;cursor:pointer;">
                                <option value="">All Branches</option>
                                @foreach($allBranches as $branch)
                                    <option value="{{ $branch->id }}" {{ (isset($filterBranchId) && $filterBranchId == $branch->id) ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <noscript><button type="submit">Filter</button></noscript>
                        </form>
                    @endif

                    <form action="{{ route('orders.index') }}" method="GET" style="display:flex;gap:8px;margin:0;">
                        @if(request('branch_id')) <input type="hidden" name="branch_id" value="{{ request('branch_id') }}"> @endif
                        @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search ID or phone…"
                            style="padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:600;outline:none;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <button type="submit"
                            style="padding:8px 14px;background:white;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;font-weight:700;color:#334155;cursor:pointer;">Search</button>
                    </form>

                    <button x-show="selectedOrders.length > 0 && currentTab !== 'delivered'"
                        @click="showBulkModal = true"
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
                                <input type="checkbox" @change="toggleSelectAll($event)"
                                    :checked="selectedOrders.length === filteredOrders.length && filteredOrders.length > 0"
                                    style="cursor:pointer;">
                            </th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Order Number & Customer</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Order Date</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Pickup Date</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Status</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">
                                Cubbet (Location)</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:right;">
                                Price</th>
                            <th
                                style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in filteredOrders" :key="order.id">
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
                                    <input type="checkbox" :value="order.id" x-model="selectedOrders"
                                        style="cursor:pointer;">
                                </td>
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
                                            <span x-text="'Sent to ' + (order.workshop ? order.workshop.name : 'Workshop')"></span>
                                        </template>
                                        <template x-if="order.status === 'returned_from_workshop'">
                                            <span x-text="'Arriving from ' + (order.workshop ? order.workshop.name : 'Workshop')"></span>
                                        </template>
                                        <template x-if="order.status !== 'dispatched_to_workshop' && order.status !== 'returned_from_workshop'">
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
                                <td style="padding:12px 20px;text-align:right;font-weight:800;color:#0f172a;font-size:13px;"
                                    x-text="parseFloat(order.total) === 0 ? 'Package' : 'Rs.' + parseFloat(order.total).toFixed(2)"></td>
                                <td style="padding:12px 20px;text-align:center;">
                                    <div style="display:flex;flex-direction:column;gap:6px;align-items:center;">
                                        <template x-if="order.status === 'received' || getReceipts(order).length > 0">
                                            <div style="display:flex;flex-direction:column;gap:6px;align-items:center;background:#f8fafc;padding:10px;border-radius:10px;border:1px solid #e2e8f0;width:100%;">
                                                <div style="font-size:11px;font-weight:800;color:#334155;">
                                                    Paid: <span style="color:#16a34a;">LKR <span x-text="parseFloat(order.paid_amount || 0).toFixed(2)"></span></span> / 
                                                    <span style="color:#0f172a;">LKR <span x-text="parseFloat(order.total || 0).toFixed(2)"></span></span>
                                                </div>
                                                
                                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;width:100%;">
                                                    <!-- Slips -->
                                                    <template x-for="(receipt, idx) in getReceipts(order)" :key="idx">
                                                        <button @click="openSlipModal(receipt, order)"
                                                            :style="
                                                                receipt.status === 'pending' ? 'border-color:#fbbf24;background:#fffbeb;color:#b45309;' : 
                                                                receipt.status === 'rejected' ? 'border-color:#fca5a5;background:#fef2f2;color:#b91c1c;text-decoration:line-through;' : 
                                                                'border-color:#c7d2fe;background:white;color:#4f46e5;'
                                                            "
                                                            style="padding:5px 12px;border-width:1.5px;border-style:dashed;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.02);transition:all 0.15s;display:flex;align-items:center;gap:6px;"
                                                            onmouseover="this.style.transform='scale(1.05)'; this.style.borderStyle='solid'" onmouseout="this.style.transform='scale(1)'; this.style.borderStyle='dashed'">
                                                            <i class="fa-solid fa-file-invoice"></i> <span x-text="'Slip ' + (idx + 1)"></span>
                                                            <template x-if="receipt.status === 'pending'"><i class="fa-solid fa-circle-exclamation" style="font-size:10px;"></i></template>
                                                            <template x-if="receipt.status === 'rejected'"><i class="fa-solid fa-circle-xmark" style="font-size:10px;"></i></template>
                                                            <template x-if="receipt.status === 'completed'"><i class="fa-solid fa-circle-check" style="font-size:10px;"></i></template>
                                                        </button>
                                                    </template>
                                                    
                                                    <template x-if="order.status === 'received'">
                                                        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;">
                                                            <button @click="openEdit(order)"
                                                                style="padding:5px 12px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                                Finalize
                                                            </button>

                                                            <button @click="bulkStatus = 'dispatched_to_workshop'; selectedOrders = [order.id]; showBulkModal = true;"
                                                                style="padding:5px 12px;background:linear-gradient(to right, #eab308, #d97706);color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
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

                                            <template x-if="order.status !== 'delivered' && order.status !== 'cancelled' && order.status !== 'dispatched_to_workshop'">
                                                <button @click="openEdit(order)"
                                                    style="padding:5px 8px;background:white;color:#d97706;border:1.5px solid #fde68a;border-radius:8px;font-size:13px;cursor:pointer;transition:all 0.15s;line-height:1;"
                                                    onmouseover="this.style.background='#fffbeb'"
                                                    onmouseout="this.style.background='white'" title="Edit Order">
                                                    ✏️
                                                </button>
                                            </template>

                                            <template x-if="currentTab === 'arriving' && order.status === 'returned_from_workshop'">  
                                                <div style="display:inline-flex;gap:6px;align-items:center;">
                                                    <template x-if="!order.is_delivery_order">
                                                        <button @click="openCubbetPopup(order)"
                                                            style="padding:5px 10px;background:#16a34a;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(22,163,74,0.3);transition:transform 0.1s;"
                                                            onmouseover="this.style.transform='scale(1.05)'"
                                                            onmouseout="this.style.transform='scale(1)'">
                                                            ✅ Confirm Arrival
                                                        </button>
                                                    </template>
                                                    
                                                    <template x-if="order.is_delivery_order">
                                                        <button @click="openDeliveryDispatchPopup(order)"
                                                            style="padding:5px 10px;background:#0ea5e9;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(14,165,233,0.3);transition:transform 0.1s;"
                                                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                            Confirm & Dispatch
                                                        </button>
                                                    </template>
                                                </div>
                                            </template>

                                            <template x-if="currentTab === 'done' && order.status === 'done'">
                                                <button @click="order.delivery_appointment ? openDeliveryDispatchPopup(order) : openDeliverPopup(order)"
                                                    style="padding:5px 10px;background:#0ea5e9;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(14,165,233,0.3);transition:transform 0.1s;"
                                                    onmouseover="this.style.transform='scale(1.05)'"
                                                    onmouseout="this.style.transform='scale(1)'">
                                                    <span x-text="order.delivery_appointment ? 'Dispatch Delivery' : 'Deliver'"></span>
                                                </button>
                                            </template>

                                            <button
                                                @click="window.open('/orders/' + order.order_number + '/chit', '_blank', 'width=300,height=400')"
                                                style="padding:5px 10px;background:white;color:#0f172a;border:1.5px solid #cbd5e1;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;transition:all 0.15s;"
                                                onmouseover="this.style.background='#f8fafc'"
                                                onmouseout="this.style.background='white'">
                                                🖨️
                                            </button>

                                            <button @click="openReverse(order.id)"
                                                style="padding:5px 7px;background:white;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;font-size:13px;cursor:pointer;transition:all 0.15s;line-height:1;"
                                                onmouseover="this.style.background='#fef2f2'"
                                                onmouseout="this.style.background='white'" title="Reverse Order">
                                                ↩
                                            </button>
                                        </div>
                                    </template>
                                    </div>
                            </tr>
                        </template>
                        <tr x-show="filteredOrders.length === 0">
                            <td colspan="8"
                                style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No orders found matching this status or query.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── Bulk Status Update Modal ── -->
            <div x-cloak x-show="showBulkModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
                    @click.away="showBulkModal = false">
                    <div
                        style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Bulk Update Orders</h3>
                        <button @click="showBulkModal = false"
                            style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                    </div>
                    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Target
                                Action</label>
                            <select x-model="bulkStatus"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                <option value="dispatched_to_workshop" x-show="currentTab === 'received'">🚚 Dispatch to Workshop</option>
                                <option value="done" x-show="currentTab === 'done'">✅ Confirm Arrival (Mark Done)</option>
                                <option value="delivered" x-show="currentTab === 'done'">📦 Mark Delivered</option>
                                <option value="cancelled">❌ Cancelled</option>
                            </select>
                        </div>

                        <div x-show="bulkStatus === 'dispatched_to_workshop'" x-cloak>
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select Workshop</label>
                            <select x-model="bulkWorkshopId" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                <option value="">-- Select Workshop --</option>
                                @foreach($workshops as $ws)
                                    <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="bulkStatus === 'done'" x-cloak>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Assign
                                to Cubbet (Optional)</label>
                            <select x-model="bulkCubbyId"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                <option value="">-- No Cubbet --</option>
                                @foreach($cubbies as $cubby)
                                    <option value="{{ $cubby->id }}">{{ $cubby->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button @click="showBulkModal = false"
                                style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                            <button @click="submitBulkUpdate"
                                style="flex:1;padding:11px;background:#16a34a;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Apply
                                Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Reverse Authorization Modal ── -->
            <div x-cloak x-show="showReverseModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
                    @click.away="showReverseModal = false">
                    <div
                        style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Reverse Bill Authorization
                        </h3>
                        <button @click="showReverseModal = false"
                            style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                    </div>
                    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                        <p style="font-size:12.5px;color:#64748b;margin:0;font-weight:500;line-height:1.4;">Enter the
                            admin or super admin password to reverse this bill and set its status back to received.</p>
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Admin
                                Password</label>
                            <input type="password" x-model="reversePassword" placeholder="••••••••"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;"
                                onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div x-show="reverseError" style="font-size:12px;color:#dc2626;font-weight:700;"
                            x-text="reverseError"></div>
                        <div style="display:flex;gap:10px;">
                            <button @click="showReverseModal = false"
                                style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                            <button @click="submitReverse"
                                style="flex:1;padding:11px;background:#ef4444;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Authorize
                                Reverse</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Mark as Done (Cubbet Assign) Modal ── -->
            <div x-cloak x-show="showCubbetModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                    @click.away="showCubbetModal = false">
                    <div
                        style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f0fdf4;">
                        <div>
                            <h3 style="font-size:18px;font-weight:900;color:#16a34a;margin:0;">Mark Order as Done</h3>
                            <p style="font-size:12px;color:#15803d;margin:2px 0 0;font-weight:600;"
                                x-text="'Order #' + cubbetOrder?.order_number"></p>
                        </div>
                        <button @click="showCubbetModal = false"
                            style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:20px;">✕</button>
                    </div>
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        <div style="padding:16px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;">
                            <label
                                style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Assign
                                to a Cubbet</label>
                            <select x-model="selectedCubby"
                                style="width:100%;padding:12px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px;font-weight:700;outline:none;background:white;color:#0f172a;">
                                <option value="" style="color:#94a3b8;">-- Select Empty Cubbet --</option>
                                @foreach($cubbies as $cubby)
                                    <option value="{{ $cubby->id }}" style="color:#0f172a;">{{ $cubby->name }}</option>
                                @endforeach
                            </select>
                            <p style="font-size:11px;color:#64748b;margin:8px 0 0;font-weight:600;">The system will send
                                an automated WhatsApp message to <span style="color:#1d4ed8;font-weight:800;"
                                    x-text="cubbetOrder?.customer?.name"></span>.</p>
                        </div>

                        <div style="display:flex;gap:12px;">
                            <button type="button" @click="showCubbetModal = false"
                                style="flex:1;padding:12px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">Cancel</button>
                            <button type="button" @click="markAsDone()"
                                style="flex:1;padding:12px;background:#16a34a;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);">✓
                                Confirm Done</button>
                        </div>
                    </div>
                </div>
            </div>

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

                        <template
                            x-if="deliverOrder && parseFloat(deliverOrder.total) > parseFloat(deliverOrder.paid_amount)">
                            <div style="padding:20px;background:#fffbeb;border:2px solid #fde68a;border-radius:24px;">
                                <label style="display:block;font-size:12px;font-weight:800;color:#b45309;margin-bottom:14px;text-transform:uppercase;letter-spacing:0.05em;">Payment Collection</label>
                                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
                                    <button type="button" @click="deliveryPaymentMethod = 'cash'"
                                        :style="deliveryPaymentMethod === 'cash'
                                            ? 'background:#f0fdf4;color:#15803d;border-color:#16a34a;border-width:3px;box-shadow:0 4px 14px rgba(22,163,74,0.2);'
                                            : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                        style="padding:16px 8px;font-size:13px;font-weight:800;border-style:solid;border-radius:18px;cursor:pointer;transition:all 0.15s;text-align:center;display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        <span style="font-size:22px;">💵</span>
                                        <span>Cash</span>
                                    </button>
                                    <button type="button" @click="deliveryPaymentMethod = 'card'"
                                        :style="deliveryPaymentMethod === 'card'
                                            ? 'background:#eff6ff;color:#1d4ed8;border-color:#2563eb;border-width:3px;box-shadow:0 4px 14px rgba(37,99,235,0.2);'
                                            : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                        style="padding:16px 8px;font-size:13px;font-weight:800;border-style:solid;border-radius:18px;cursor:pointer;transition:all 0.15s;text-align:center;display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        <span style="font-size:22px;">💳</span>
                                        <span>Card</span>
                                    </button>
                                    <button type="button" @click="deliveryPaymentMethod = 'credit'"
                                        :style="deliveryPaymentMethod === 'credit'
                                            ? 'background:#fff7ed;color:#c2410c;border-color:#f97316;border-width:3px;box-shadow:0 4px 14px rgba(249,115,22,0.2);'
                                            : 'background:white;color:#64748b;border-color:#e2e8f0;border-width:2px;'"
                                        style="padding:16px 8px;font-size:13px;font-weight:800;border-style:solid;border-radius:18px;cursor:pointer;transition:all 0.15s;text-align:center;display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        <span style="font-size:22px;">⏳</span>
                                        <span>Credit</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template
                            x-if="deliverOrder && parseFloat(deliverOrder.total) <= parseFloat(deliverOrder.paid_amount)">
                            <div
                                style="padding:16px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;text-align:center;">
                                <span style="font-size:13px;font-weight:800;color:#16a34a;">Order is fully paid!</span>
                            </div>
                        </template>

                        <div style="display:flex;gap:12px;margin-top:4px;">
                            <button type="button" @click="confirmDelivery(false)"
                                style="flex:1;padding:14px;background:white;color:#16a34a;font-size:14px;font-weight:800;border:2px solid #16a34a;border-radius:12px;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='white'">✓ Complete</button>
                            <button type="button" @click="confirmDelivery(true)"
                                style="flex:1;padding:14px;background:#4f46e5;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(79,70,229,0.3);">🖨
                                Print & Deliver</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Dispatch Delivery Modal ── -->
        <div x-cloak x-show="showDispatchDeliveryModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showDispatchDeliveryModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eff6ff;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#1d4ed8;margin:0;">Dispatch Delivery</h3>
                        <p style="font-size:12px;color:#3b82f6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (deliverOrder?.order_number || '')"></p>
                    </div>
                    <button @click="showDispatchDeliveryModal = false" style="background:none;border:none;cursor:pointer;color:#1d4ed8;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Delivery Method</label>
                        <div class="grid grid-cols-2 gap-3" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px;">
                            <button @click="deliveryMethod = 'rider'" :class="deliveryMethod === 'rider' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center" style="padding: 12px; border-width: 1px; border-radius: 12px; font-weight: 700; font-size: 14px; text-align: center; cursor:pointer;">
                                Rider
                            </button>
                            <button @click="deliveryMethod = 'pickme_flash'" 
                                :class="[
                                    deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300'
                                ]"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group" style="padding: 12px; border-width: 1px; border-radius: 12px; font-weight: 700; font-size: 14px; text-align: center; cursor:pointer; position:relative;">
                                PickMe
                            </button>
                        </div>
                    </div>

                    <div x-show="deliveryMethod === 'rider'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select Rider</label>
                        <select x-model="deliveryRiderId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="">-- Choose Rider --</option>
                            <template x-for="rider in riders" :key="rider.id">
                                <option :value="rider.id" x-text="rider.name"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="deliveryMethod === 'pickme_flash'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">PickMe Cost (Rs.)</label>
                        <input type="number" x-model="deliveryPickmeCost" placeholder="e.g. 500" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:600;">This will automatically be added as a Branch Expense.</p>
                        
                        <div x-show="deliverOrder && (deliverOrder.total - (deliverOrder.paid_amount || 0) > 0)" style="margin-top:12px;">
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Balance to Collect: LKR <span x-text="deliverOrder ? (deliverOrder.total - (deliverOrder.paid_amount || 0)).toFixed(2) : '0.00'"></span></label>
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Balance Payment Method</label>
                            <select x-model="deliveryPaymentMethod" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="button" @click="showDispatchDeliveryModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="submitDeliveryDispatch()" :disabled="isDispatchingDelivery" style="flex:1;padding:14px;background:#3b82f6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                            <span x-text="isDispatchingDelivery ? 'Dispatching...' : 'Dispatch Now'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <x-modals.request-payment />
        <x-modals.review-slip />

    </div>
</x-app-layout>