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
        allCategories: {{ Js::from($categories) }},
        minimumDeliveryCost: {{ (float)($minimumDeliveryCost ?? 0) }},
        pageDispatches: {{ Js::from($dispatches->pluck('id')) }},
        filteredDispatches: {{ Js::from($dispatches->items()) }},
        autoPrintLabel: {{ \App\Models\Setting::get('auto_print_label', '0') == '1' ? 'true' : 'false' }},
        
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
                    // Automatically print the bill for the delivery order
                    if (this.deliveryDispatch && this.deliveryDispatch.order && this.deliveryDispatch.order.order_number) {
                        window.open('/orders/' + this.deliveryDispatch.order.order_number + '/print', '_blank');
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 500); // slight delay to ensure popup works before reload
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
                    if (data.order_id && this.autoPrintLabel) {
                        window.open(`/orders/${data.order_id}/label`, '_blank');
                    }
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

        @include('workshop.partials.summary')
        @include('workshop.partials.tabs-filter')
        @include('workshop.partials.orders-table')
        @include('workshop.partials.modals')

</div>{{-- /main x-data --}}

</x-app-layout>


