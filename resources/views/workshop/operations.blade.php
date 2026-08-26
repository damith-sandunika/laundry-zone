<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-start gap-3">
            <div>
                <h2 class="ph-title">Workshop Dashboard</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Manage incoming orders, processing, and dispatches</p>
            </div>
            <a href="{{ route('workshop.history') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-lg hover:bg-slate-200">View History</a>
        </div>
    </x-slot>

    <div x-data="{ 
        currentTab: 'arrival_processing',
        selectedBranch: '',
        selectedDispatches: [],
        showBulkModal: false,
        bulkStatus: '',
        dispatchesList: JSON.parse(atob('{{ base64_encode(json_encode($dispatches)) }}')),
        
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedDispatches = this.filteredDispatches.map(d => d.id);
            } else {
                this.selectedDispatches = [];
            }
        },
        
        get filteredDispatches() {
            let list = this.dispatchesList;
            if (this.selectedBranch !== '') {
                list = list.filter(d => d.branch_id == this.selectedBranch);
            }
            if (this.currentTab === 'arrival_processing') {
                return list.filter(d => d.status === 'dispatched_to_workshop' || d.status === 'received_at_workshop' || d.status === 'processing');
            }
            return list.filter(d => d.status === this.currentTab);
        },

        get counts() {
            let list = this.dispatchesList;
            if (this.selectedBranch !== '') {
                list = list.filter(d => d.branch_id == this.selectedBranch);
            }
            return {
                arrival_processing: list.filter(d => d.status === 'dispatched_to_workshop' || d.status === 'received_at_workshop' || d.status === 'processing').length,
                done: list.filter(d => d.status === 'completed_at_workshop').length,
                returned: list.filter(d => d.status === 'returned_to_branch').length
            };
        },

        showPaymentModal: false,
        paymentDispatch: null,
        paymentAmountType: 'full',
        customPaymentAmount: '',
        isRequestingPayment: false,

        openPaymentModal(dispatch) {
            this.paymentDispatch = dispatch;
            this.paymentAmountType = 'full';
            this.customPaymentAmount = '';
            this.showPaymentModal = true;
        },

        showDeliveryModal: false,
        deliveryDispatch: null,
        deliveryMethod: 'rider',
        deliveryRiderId: '',
        deliveryPickmeCost: '',
        isDispatchingDelivery: false,

        openDeliveryModal(dispatch) {
            this.deliveryDispatch = dispatch;
            this.deliveryMethod = 'rider';
            this.deliveryRiderId = '';
            this.deliveryPickmeCost = '';
            this.showDeliveryModal = true;
        },

        async submitDelivery() {
            if (!this.deliveryDispatch) return;
            if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
            if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');

            this.isDispatchingDelivery = true;
            try {
                const res = await fetch(`/workshop/dispatches/${this.deliveryDispatch.id}/delivery`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        delivery_method: this.deliveryMethod,
                        rider_id: this.deliveryRiderId,
                        pickme_cost: this.deliveryPickmeCost
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to dispatch delivery.');
                }
            } catch (err) {
                alert('Connection error');
            }
            this.isDispatchingDelivery = false;
        },

        async sendPaymentRequest() {
            if (!this.paymentDispatch) return;
            this.isRequestingPayment = true;
            try {
                const res = await fetch(`/workshop/dispatches/${this.paymentDispatch.id}/request-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        amount_type: this.paymentAmountType,
                        custom_amount: this.customPaymentAmount
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Payment Request Sent successfully via WhatsApp.');
                    this.showPaymentModal = false;
                } else {
                    alert(data.message || 'Failed to send payment request.');
                }
            } catch (err) {
                alert('Connection error');
            }
            this.isRequestingPayment = false;
        },

        async approvePaymentAction(dispatchId) {
            if(!confirm('Are you sure you want to manually approve payment for this order?')) return;
            try {
                const res = await fetch(`/workshop/dispatches/${dispatchId}/approve-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) window.location.reload();
                else alert(data.message || 'Failed');
            } catch(e) {
                alert('Connection error');
            }
        },

        async workshopAction(dispatchId, action) {
            try {
                const res = await fetch(`/workshop/dispatches/${dispatchId}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (err) {
                alert('Connection error');
            }
        },
        
        async submitBulkUpdate() {
            if (!this.bulkStatus) { alert('Please select an action'); return; }
            if (this.selectedDispatches.length === 0) { alert('No orders selected'); return; }
            
            try {
                const res = await fetch('{{ route('workshop.bulkStatus') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        dispatch_ids: this.selectedDispatches,
                        status: this.bulkStatus
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error updating statuses');
                }
            } catch (err) {
                alert('Connection error');
            }
        },

        getReceipts(dispatch) {
            let order = dispatch.order;
            if (!order) return [];
            let receipts = [];
            if (order.receipt_path) receipts.push(order.receipt_path);
            if (order.delivery_appointment && order.delivery_appointment.receipt_path) {
                receipts.push(order.delivery_appointment.receipt_path);
            }
            if (order.payment_transactions) {
                order.payment_transactions.forEach(tx => {
                    if (tx.receipt_path) receipts.push(tx.receipt_path);
                });
            }
            return [...new Set(receipts)];
        }
    }">

        <!-- OVERALL SUMMARY -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Overall Summary
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Active</span>
                    <span class="text-2xl font-black text-slate-800" x-text="dispatchesList.length"></span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed Today</span>
                    <span class="text-2xl font-black text-slate-800">{{ $completedToday }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">In Processing</span>
                    <span class="text-2xl font-black text-orange-600" x-text="counts.arrival_processing"></span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Ready to Dispatch</span>
                    <span class="text-2xl font-black text-green-600" x-text="counts.done"></span>
                </div>
            </div>
        </div>

        <!-- OPERATIONS (TABS) -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Operations
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-4 mb-4">
                <!-- Arrival & Processing -->
                <div @click="currentTab = 'arrival_processing'"
                    :class="currentTab === 'arrival_processing' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'arrival_processing' ? 'text-orange-700' : 'text-gray-900'" x-text="counts.arrival_processing"></span>
                    <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Arrival & Processing</span>
                </div>

                <!-- Done -->
                <div @click="currentTab = 'completed_at_workshop'"
                    :class="currentTab === 'completed_at_workshop' ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'completed_at_workshop' ? 'text-green-700' : 'text-gray-900'" x-text="counts.done"></span>
                    <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Done</span>
                </div>

                <!-- Dispatched Back -->
                <div @click="currentTab = 'returned_to_branch'"
                    :class="currentTab === 'returned_to_branch' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'returned_to_branch' ? 'text-purple-700' : 'text-gray-900'" x-text="counts.returned"></span>
                    <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Dispatched</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <button x-show="selectedDispatches.length > 0" @click="showBulkModal = true"
                    class="px-4 py-2 bg-blue-600 text-white font-bold rounded-lg text-sm hover:bg-blue-700 transition-colors shadow-sm flex items-center gap-2">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Bulk Action (<span x-text="selectedDispatches.length"></span>)
                </button>
            </div>
            <select x-model="selectedBranch" class="px-4 py-2 pr-8 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-blue-500">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-md border border-gray-100 shadow-sm overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left" style="border-collapse:collapse;">
                    <thead style="background:#f8fafc;border-bottom:1.5px solid #e2e8f0;">
                        <tr>
                            <th style="padding:14px 20px;width:40px;">
                                <input type="checkbox" @change="toggleSelectAll($event)" :checked="selectedDispatches.length === filteredDispatches.length && filteredDispatches.length > 0" style="cursor:pointer;">
                            </th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Order Info</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Dispatch Time</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Branch</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;">Status</th>
                            <th style="padding:14px 20px;font-weight:800;color:#0f172a;text-transform:uppercase;font-size:11px;letter-spacing:0.05em;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="dispatch in filteredDispatches" :key="dispatch.id">
                            <tr style="background:white;border-bottom:1px solid #f1f5f9;transition:all 0.1s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                <td style="padding:12px 20px;">
                                    <input type="checkbox" :value="dispatch.id" x-model="selectedDispatches" style="cursor:pointer;">
                                </td>
                                <td style="padding:12px 20px;">
                                    <span style="font-size:13px;font-weight:800;color:#1d4ed8;display:block;" x-text="(dispatch.order.is_delivery_order ? '[D] ' : '') + '#' + dispatch.order.order_number"></span>
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
                                            <button @click="workshopAction(dispatch.id, 'start-processing')"
                                                style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Receive at Workshop
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'received_at_workshop'">
                                            <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:center;">
                                                <a :href="'/workshop/dispatches/' + dispatch.id + '/finalize-bill'"
                                                    style="padding:5px 10px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;text-decoration:none;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                                                    onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                    Finalize Bill
                                                </a>
                                                
                                                <template x-if="dispatch.order && dispatch.order.is_delivery_order && !dispatch.order.payment_approved">
                                                    <div style="display:flex;gap:6px;flex-direction:column;align-items:center;background:#f8fafc;padding:10px;border-radius:10px;border:1px solid #e2e8f0;width:100%;margin-top:4px;">
                                                        <div style="font-size:11px;font-weight:800;color:#334155;">
                                                            Paid: <span style="color:#16a34a;">LKR <span x-text="parseFloat(dispatch.order.paid_amount || 0).toFixed(2)"></span></span> / 
                                                            <span style="color:#0f172a;">LKR <span x-text="parseFloat(dispatch.order.total || 0).toFixed(2)"></span></span>
                                                        </div>
                                                        
                                                        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;width:100%;">
                                                            <template x-for="(receipt, idx) in getReceipts(dispatch)" :key="idx">
                                                                <a :href="'/storage/' + receipt" target="_blank"
                                                                    style="padding:5px 10px;background:white;color:#4f46e5;border:1.5px solid #c7d2fe;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer;text-decoration:none;box-shadow:0 1px 2px rgba(79,70,229,0.1);transition:all 0.15s;display:flex;align-items:center;gap:4px;"
                                                                    onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='white'">
                                                                    <i class="fa-solid fa-file-invoice"></i> Slip <span x-text="idx + 1"></span>
                                                                </a>
                                                            </template>

                                                            <template x-if="getReceipts(dispatch).length === 0">
                                                                <button @click="openPaymentModal(dispatch)"
                                                                    style="padding:5px 10px;background:#14b8a6;color:white;border:none;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(20,184,166,0.25);transition:transform 0.1s;display:flex;align-items:center;gap:4px;"
                                                                    onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                                                    <i class="fa-solid fa-bell"></i> Request
                                                                </button>
                                                            </template>

                                                            <button @click="approvePaymentAction(dispatch.id)"
                                                                style="padding:5px 10px;background:#10b981;color:white;border:none;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.25);transition:transform 0.1s;display:flex;align-items:center;gap:4px;"
                                                                onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                                                                <i class="fa-solid fa-check-circle"></i> Approve
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>

                                                <template x-if="!(dispatch.order && dispatch.order.is_delivery_order && !dispatch.order.payment_approved)">
                                                    <button @click="workshopAction(dispatch.id, 'start-processing')"
                                                        style="padding:5px 10px;background:#eab308;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        Start Washing
                                                    </button>
                                                </template>
                                            </div>
                                        </template>

                                        <template x-if="dispatch.status === 'processing'">
                                            <button @click="workshopAction(dispatch.id, 'complete')"
                                                style="padding:5px 10px;background:#10b981;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.3);transition:transform 0.1s;"
                                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                Mark Done
                                            </button>
                                        </template>

                                        <template x-if="dispatch.status === 'completed_at_workshop'">
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                                <template x-if="dispatch.order && dispatch.order.is_delivery_order">
                                                    <button @click="openDeliveryModal(dispatch)"
                                                        style="padding:5px 10px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        Dispatch Delivery
                                                    </button>
                                                </template>
                                                <template x-if="!(dispatch.order && dispatch.order.is_delivery_order)">
                                                    <button @click="workshopAction(dispatch.id, 'return')"
                                                        style="padding:5px 10px;background:#8b5cf6;color:white;border:none;border-radius:8px;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(139,92,246,0.3);transition:transform 0.1s;"
                                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                        Move to Dispatch
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
                            <td colspan="5" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
                                No records found in this category.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Bulk Status Update Modal ── -->
        <div x-cloak x-show="showBulkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
            <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;" @click.away="showBulkModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                    <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Bulk Action</h3>
                    <button @click="showBulkModal = false" style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                </div>
                <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Action to Perform</label>
                        <select x-model="bulkStatus" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                            <option value="">-- Select Action --</option>
                            <option value="processing" x-show="currentTab === 'arrival_processing'">👕 Receive & Start Washing</option>
                            <option value="completed_at_workshop" x-show="currentTab === 'arrival_processing'">✅ Mark Done</option>
                            <option value="returned_to_branch" x-show="currentTab === 'completed_at_workshop'">🚚 Move to Dispatch</option>
                        </select>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button @click="showBulkModal = false" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                        <button @click="submitBulkUpdate" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Apply Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Request Payment Modal ── -->
        <div x-cloak x-show="showPaymentModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showPaymentModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f0fdfa;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#0f766e;margin:0;">Request Payment</h3>
                        <p style="font-size:12px;color:#14b8a6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (paymentDispatch?.order?.order_number || '')"></p>
                    </div>
                    <button @click="showPaymentModal = false" style="background:none;border:none;cursor:pointer;color:#0f766e;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;text-align:center;">
                        <span style="font-size:13px;font-weight:700;color:#64748b;display:block;">Total Bill</span>
                        <span style="font-size:24px;font-weight:900;color:#0f172a;" x-text="'Rs. ' + parseFloat(paymentDispatch?.order?.total || 0).toFixed(2)"></span>
                    </div>

                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Payment Amount to Request</label>
                        <div class="grid grid-cols-3 gap-3">
                            <button @click="paymentAmountType = '50'" :class="paymentAmountType === '50' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                                50%
                                <div class="text-[10px] font-semibold text-slate-400 mt-1" x-text="'Rs.' + (parseFloat(paymentDispatch?.order?.total || 0) / 2).toFixed(0)"></div>
                            </button>
                            <button @click="paymentAmountType = 'full'" :class="paymentAmountType === 'full' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                                Full
                                <div class="text-[10px] font-semibold text-slate-400 mt-1" x-text="'Rs.' + parseFloat(paymentDispatch?.order?.total || 0).toFixed(0)"></div>
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

        <!-- ── Dispatch Delivery Modal ── -->
        <div x-cloak x-show="showDeliveryModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showDeliveryModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eff6ff;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#1d4ed8;margin:0;">Dispatch Delivery</h3>
                        <p style="font-size:12px;color:#3b82f6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (deliveryDispatch?.order?.order_number || '')"></p>
                    </div>
                    <button @click="showDeliveryModal = false" style="background:none;border:none;cursor:pointer;color:#1d4ed8;font-size:20px;">✕</button>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Delivery Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="deliveryMethod = 'rider'" :class="deliveryMethod === 'rider' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                                Rider
                            </button>
                            <button @click="deliveryDispatch?.order?.paid_amount >= deliveryDispatch?.order?.total ? deliveryMethod = 'pickme_flash' : null" 
                                :class="[
                                    deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300',
                                    deliveryDispatch?.order?.paid_amount < deliveryDispatch?.order?.total ? 'opacity-50 cursor-not-allowed bg-slate-50 border-slate-200' : ''
                                ]"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group">
                                PickMe
                                <template x-if="deliveryDispatch?.order?.paid_amount < deliveryDispatch?.order?.total">
                                    <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 pointer-events-none whitespace-nowrap transition-opacity z-10">
                                        Requires full payment
                                    </div>
                                </template>
                            </button>
                        </div>
                    </div>

                    <div x-show="deliveryMethod === 'rider'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select Rider</label>
                        <select x-model="deliveryRiderId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="">-- Choose Rider --</option>
                            @foreach($riders as $rider)
                                <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="deliveryMethod === 'pickme_flash'" style="display: none;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">PickMe Cost (Rs.)</label>
                        <input type="number" x-model="deliveryPickmeCost" placeholder="e.g. 500" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:600;">This will automatically be added as a Workshop Expense.</p>
                    </div>

                    <div style="display:flex;gap:12px;margin-top:8px;">
                        <button type="button" @click="showDeliveryModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                        <button type="button" @click="submitDelivery()" :disabled="isDispatchingDelivery" style="flex:1;padding:14px;background:#3b82f6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                            <span x-text="isDispatchingDelivery ? 'Dispatching...' : 'Dispatch Now'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
