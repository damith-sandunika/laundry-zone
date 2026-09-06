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
        currentTab: '{{ $currentTab }}',
        selectedBranch: '{{ $selectedBranch ?? '' }}',
        selectedDispatches: [],
        showBulkModal: false,
        bulkStatus: '',
        allCategories: JSON.parse(atob('{{ base64_encode(json_encode($categories)) }}')),
        minimumDeliveryCost: {{ (float)($minimumDeliveryCost ?? 0) }},
        pageDispatches: JSON.parse(atob('{{ base64_encode(json_encode($dispatches->pluck('id'))) }}')),
        filteredDispatches: JSON.parse(atob('{{ base64_encode(json_encode($dispatches->items())) }}')),
        
        openFinalizePage(dispatch) {
            window.location.href = '/workshop/orders/' + dispatch.order.order_number + '/finalize-bill';
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedDispatches = this.pageDispatches;
            } else {
                this.selectedDispatches = [];
            }
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
        deliveryPaymentMethod: 'cash',
        isDispatchingDelivery: false,

        openDeliveryModal(dispatch) {
            this.deliveryDispatch = dispatch;
            this.deliveryMethod = 'rider';
            this.deliveryRiderId = '';
            this.deliveryPickmeCost = '';
            this.deliveryPaymentMethod = 'cash';
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
                        pickme_cost: this.deliveryPickmeCost,
                        payment_method: this.deliveryPaymentMethod
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to dispatch delivery.');
                }
            } catch (err) {
                console.error('AJAX Error:', err);
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
                console.error('AJAX Error:', err);
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
                console.error('AJAX Error:', e);
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
                if (data.success) {
                    if (action === 'start-processing' && data.order_number) {
                        window.open(`/orders/${data.order_number}/chit`, '_blank');
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update status.');
                }
            } catch (err) {
                console.error('AJAX Error:', err);
                alert('Connection error');
            }
        },

        // ── Handover Modal ──
        showHandoverModal: false,
        handoverDispatch: null,
        handoverPaymentMethod: 'cash',
        handoverAmountPaid: '',
        isHandoverSubmitting: false,
        handoverPrintReceipt: true,

        openHandoverModal(dispatch) {
            this.handoverDispatch = dispatch;
            this.handoverAmountPaid = dispatch.order ? Math.max(0, (parseFloat(dispatch.order.total||0) - parseFloat(dispatch.order.paid_amount||0))).toFixed(2) : '';
            this.handoverPaymentMethod = 'cash';
            this.handoverPrintReceipt = true;
            this.showHandoverModal = true;
        },

        async submitHandover() {
            if (!this.handoverDispatch) return;
            this.isHandoverSubmitting = true;
            try {
                const res = await fetch(`/workshop/dispatches/${this.handoverDispatch.id}/handover`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_method: this.handoverPaymentMethod,
                        amount_paid: this.handoverAmountPaid ? parseFloat(this.handoverAmountPaid) : 0
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (this.handoverPrintReceipt && data.order_id) {
                        window.open(`/orders/${data.order_number}/print`, '_blank');
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to hand over.');
                }
            } catch (err) {
                console.error('AJAX Error:', err);
                alert('Connection error');
            }
            this.isHandoverSubmitting = false;
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
                console.error('AJAX Error:', err);
                alert('Connection error');
            }
        },

        getReceipts(dispatch) {
            let order = dispatch.order;
            if (!order) return [];
            let receipts = [];
            
            if (order.payment_transactions) {
                order.payment_transactions.forEach(tx => {
                    if (tx.receipt_path) {
                        receipts.push({
                            id: tx.id,
                            url: tx.receipt_path.startsWith('http') ? tx.receipt_path : '/storage/' + tx.receipt_path,
                            amount: tx.amount,
                            status: tx.status,
                            created_at: new Date(tx.created_at).toLocaleString(),
                            reject_reason: tx.reject_reason,
                            is_legacy: false
                        });
                    }
                });
            }

            let flatUrls = receipts.map(r => r.url);

            if (order.receipt_path) {
                let rUrl = order.receipt_path.startsWith('http') ? order.receipt_path : '/storage/' + order.receipt_path;
                if (!flatUrls.includes(rUrl)) {
                    receipts.push({ id: null, url: rUrl, amount: order.total, status: 'legacy', created_at: '', is_legacy: true });
                }
            }
            if (order.delivery_appointment && order.delivery_appointment.receipt_path) {
                let daUrl = order.delivery_appointment.receipt_path.startsWith('http') ? order.delivery_appointment.receipt_path : '/storage/' + order.delivery_appointment.receipt_path;
                if (!flatUrls.includes(daUrl)) {
                    receipts.push({ id: null, url: daUrl, amount: order.total, status: 'legacy', created_at: '', is_legacy: true });
                }
            }
            return receipts;
        },

        showSlipModal: false,
        selectedSlip: null,
        selectedOrder: null,
        
        openSlipModal(slip, order) {
            this.selectedSlip = slip;
            this.selectedOrder = order;
            this.showSlipModal = true;
        },

        // ── Mark Done Modal ──
        showDoneModal: false,
        doneDispatch: null,
        doneCubbyId: '',
        donePaymentMethod: 'cash',
        doneAmountPaid: '',
        isDoneSubmitting: false,

        openDoneModal(dispatch) {
            this.doneDispatch = dispatch;
            this.doneCubbyId = '';
            this.doneAmountPaid = dispatch.order ? Math.max(0, (parseFloat(dispatch.order.total||0) - parseFloat(dispatch.order.paid_amount||0))).toFixed(2) : '';
            this.donePaymentMethod = 'cash';
            this.showDoneModal = true;
        },

        async submitDone() {
            if (!this.doneDispatch) return;
            this.isDoneSubmitting = true;
            try {
                const res = await fetch(`/workshop/dispatches/${this.doneDispatch.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cubby_id: this.doneCubbyId || null
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to mark done.');
                }
            } catch (err) {
                console.error('AJAX Error:', err);
                alert('Connection error');
            }
            this.isDoneSubmitting = false;
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
                    <span class="text-2xl font-black text-slate-800">{{ $arrivalProcessingCount + $returnedCount }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed Today</span>
                    <span class="text-2xl font-black text-slate-800">{{ $completedToday }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">In Processing</span>
                    <span class="text-2xl font-black text-orange-600">{{ $arrivalProcessingCount }}</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-center">
                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-1">Completed & Dispatched</span>
                    <span class="text-2xl font-black text-green-600">{{ $returnedCount }}</span>
                </div>
            </div>
        </div>

        <!-- OPERATIONS (TABS) -->
        <div class="mb-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Operations
            </h3>
            <div class="grid grid-cols-2 gap-2 md:gap-4 mb-4">
                <!-- Arrival & Processing -->
                <a href="?tab=arrival_processing&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'arrival_processing' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'arrival_processing' ? 'text-orange-700' : 'text-gray-900'">{{ $arrivalProcessingCount }}</span>
                    <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Arrival & Processing</span>
                </a>

                <!-- Dispatched Back & Completed -->
                <a href="?tab=returned_to_branch&branch={{ $selectedBranch ?? '' }}"
                    :class="currentTab === 'returned_to_branch' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white hover:bg-gray-50 hover:shadow-md'"
                    class="flex flex-col items-center justify-center p-2 md:px-6 md:py-5 rounded-xl border-2 cursor-pointer transition-all duration-300" style="text-decoration:none;">
                    <span class="text-xs md:text-sm font-black mt-1" :class="currentTab === 'returned_to_branch' ? 'text-purple-700' : 'text-gray-900'">{{ $returnedCount }}</span>
                    <span class="text-[10px] md:text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Completed & Dispatched</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 flex justify-end items-center">
            <select x-model="selectedBranch" @change="window.location.href = '?tab={{ $currentTab }}&branch=' + $event.target.value" class="px-4 py-2 pr-8 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 outline-none focus:border-blue-500">
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
                                                <template x-if="dispatch.order.branch_id == {{ auth()->user()->branch_id ?? 'null' }}">
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
                                                <template x-if="dispatch.order.branch_id != {{ auth()->user()->branch_id ?? 'null' }}">
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
                            <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;">
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



        <!-- Modals for workshop (if any) -->
        
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
                            <button @click="deliveryMethod = 'pickme_flash'" 
                                :class="[
                                    deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300'
                                ]"
                                class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group">
                                PickMe
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
                        
                        <div x-show="deliveryDispatch && deliveryDispatch.order && (deliveryDispatch.order.total - (deliveryDispatch.order.paid_amount || 0) > 0)" style="margin-top:12px;">
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Balance to Collect: LKR <span x-text="deliveryDispatch && deliveryDispatch.order ? (deliveryDispatch.order.total - (deliveryDispatch.order.paid_amount || 0)).toFixed(2) : '0.00'"></span></label>
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Balance Payment Method</label>
                            <select x-model="deliveryPaymentMethod" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
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



    <!-- ── Mark Done Modal ── -->
    <div x-cloak x-show="showDoneModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showDoneModal = false">
            <!-- Header -->
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">✅ Mark as Done</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (doneDispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showDoneModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">


                <!-- Cubby Selection -->
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Assign Storage Location (Cubby)</label>
                    <select x-model="doneCubbyId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;background:white;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">-- No cubby (skip) --</option>
                        @foreach($cubbies as $cubby)
                            <option value="{{ $cubby->id }}">
                                {{ $cubby->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showDoneModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitDone()" :disabled="isDoneSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isDoneSubmitting ? 'Processing...' : 'Confirm Done'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ── Handover Modal ── -->
    <div x-cloak x-show="showHandoverModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showHandoverModal = false">
            <!-- Header -->
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">🤝 Hand Over & Complete</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (handoverDispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showHandoverModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                <!-- Assigned Cubby (if any) -->
                <template x-if="handoverDispatch?.order?.cubby?.cubby">
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;background:#f59e0b;color:white;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;">
                            📦
                        </div>
                        <div>
                            <span style="font-size:11px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Assigned Storage Location</span>
                            <span style="font-size:16px;font-weight:900;color:#92400e;" x-text="handoverDispatch.order.cubby.cubby.name"></span>
                        </div>
                    </div>
                </template>

                <!-- Payment Summary -->
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#64748b;">Total Amount</span>
                        <span style="font-size:13px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(handoverDispatch?.order?.total || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#16a34a;">Already Paid</span>
                        <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'Rs. ' + parseFloat(handoverDispatch?.order?.paid_amount || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1.5px dashed #cbd5e1;">
                        <span style="font-size:14px;font-weight:900;color:#dc2626;">Balance Due</span>
                        <span style="font-size:15px;font-weight:900;color:#dc2626;" x-text="'Rs. ' + Math.max(0, parseFloat(handoverDispatch?.order?.total||0) - parseFloat(handoverDispatch?.order?.paid_amount||0)).toFixed(2)"></span>
                    </div>
                </div>

                <!-- Payment collection (only if balance > 0) -->
                <template x-if="handoverDispatch && (parseFloat(handoverDispatch?.order?.total||0) - parseFloat(handoverDispatch?.order?.paid_amount||0)) > 0">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Collect Payment Now</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Method</label>
                                <select x-model="handoverPaymentMethod" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                    <option value="cash">💵 Cash</option>
                                    <option value="bank">🏦 Bank Transfer</option>
                                    <option value="card">💳 Card</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Amount (Rs.)</label>
                                <input type="number" step="0.01" x-model="handoverAmountPaid" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                            </div>
                        </div>
                    </div>
                </template>
                
                <div style="display:flex;align-items:center;gap:10px;margin-top:5px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;">
                    <input type="checkbox" id="printReceiptHandover" x-model="handoverPrintReceipt" style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                    <label for="printReceiptHandover" style="font-size:13px;font-weight:700;color:#334155;cursor:pointer;user-select:none;">Print Receipt & Hand Over</label>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showHandoverModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitHandover()" :disabled="isHandoverSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isHandoverSubmitting ? 'Processing...' : 'Confirm Handover'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <x-modals.review-slip />

</div>{{-- /main x-data --}}

</x-app-layout>


