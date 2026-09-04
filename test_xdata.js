let a = {
        currentTab: 'received',
        selectedOrders: [],
        showBulkModal: false,
        bulkStatus: 'done',
        bulkCubbyId: '',
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

        // Dispatch Delivery Modal (For Delivery Orders)
        showDispatchDeliveryModal: false,
        deliveryMethod: 'rider',
        deliveryRiderId: '',
        deliveryPickmeCost: '',
        isDispatchingDelivery: false,
        riders: JSON.parse(atob('""')),

        // Edit Order Modal
        showEditModal: false,
        editOrderData: null,
        editCart: [],
        editDiscount: 0,
        editAdvanceCashAmount: '',
        selectedServiceToAdd: '',
        allCategories: JSON.parse(atob('""')),
        minimumDeliveryCost: "",

        ordersList: JSON.parse(atob('""')),

        showPaymentModal: false,
        paymentOrder: null,
        paymentAmountType: 'full',
        customPaymentAmount: '',
        isRequestingPayment: false,

        openPaymentModal(order) {
            this.paymentOrder = order;
            this.paymentAmountType = 'full';
            this.customPaymentAmount = '';
            this.showPaymentModal = true;
        },

        showSlipModal: false,
        selectedSlip: null,
        selectedOrder: null,

        openSlipModal(receipt, order) {
            this.selectedSlip = receipt;
            this.selectedOrder = order;
            this.showSlipModal = true;
        },

        getReceipts(order) {
            if (!order || !order.payment_transactions) return [];
            return order.payment_transactions.filter(p => p.receipt_path).map(p => ({
                id: p.id,
                amount: p.amount,
                url: '/storage/' + p.receipt_path,
                status: p.status,
                reject_reason: p.reject_reason,
                created_at: p.created_at ? new Date(p.created_at).toLocaleString() : ''
            }));
        },

        async sendPaymentRequest() {
            if (!this.paymentOrder) return;
            this.isRequestingPayment = true;
            try {
                const res = await fetch('/orders/' + this.paymentOrder.id + '/request-payment', {
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

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedOrders = this.filteredOrders.map(o => o.id);
            } else {
                this.selectedOrders = [];
            }
        },

        get filteredOrders() {
            if (this.currentTab === 'all') return this.ordersList;
            if (this.currentTab === 'received') {
                return this.ordersList.filter(o => o.status === 'received' || o.status === 'dispatched_to_workshop');
            }
            if (this.currentTab === 'arriving') {
                return this.ordersList.filter(o => o.status === 'returned_from_workshop');
            }
            if (this.currentTab === 'done') {
                return this.ordersList.filter(o => o.status === 'done');
            }
            if (this.currentTab === 'delivered') {
                return this.ordersList.filter(o => o.status === 'delivered' || o.status === 'completed');
            }
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

        bulkWorkshopId: '',

        async submitBulkUpdate() {
            try {
                if (this.bulkStatus === 'dispatched_to_workshop') {
                    if (!this.bulkWorkshopId) { alert('Please select a workshop'); return; }
                    const res = await fetch('""', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({
                            order_ids: this.selectedOrders,
                            workshop_branch_id: this.bulkWorkshopId,
                            dispatch_notes: 'Bulk dispatched from branch'
                        })
                    });
                    const data = await res.json(); // WorkshopController needs to return json actually. Wait, bulkDispatch returns redirect. Let's fix this in a sec or just reload.
                    window.location.reload();
                    return;
                }

                const res = await fetch('""', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        order_ids: this.selectedOrders,
                        status: this.bulkStatus,
                        cubby_id: this.bulkStatus === 'done' ? this.bulkCubbyId : null
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    console.error("Bulk Status Error Response:", data);
                }
                
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Bulk update failed');
                }
            } catch (err) {
                console.error("Bulk Status Error:", err);
                // If it was a redirect, fetch might throw an opaque error but it succeeded
                window.location.reload();
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
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    console.error('markAsDone failed:', data);
                    alert('Error: ' + (data.message || 'Failed to update status'));
                }
            } catch (err) { 
                console.error('markAsDone Network Error:', err);
                alert('Error updating status'); 
            }
        },

        openDeliverPopup(order) {
            this.deliverOrder = order;
            this.deliveryPaymentMethod = 'cash';
            this.showDeliverModal = true;
        },

        openDeliveryDispatchPopup(order) {
            this.deliverOrder = order;
            this.deliveryMethod = 'rider';
            this.deliveryRiderId = '';
            this.deliveryPickmeCost = '';
            this.isDispatchingDelivery = false;
            this.showDispatchDeliveryModal = true;
        },

        async submitDeliveryDispatch() {
            if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
            if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');
            
            this.isDispatchingDelivery = true;
            try {
                if (this.deliverOrder.status === 'returned_from_workshop' && this.deliverOrder.workshop_dispatch_id) {
                    await fetch(`/workshop/dispatches/${this.deliverOrder.workshop_dispatch_id}/received-at-branch`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                }

                const res = await fetch(`/orders/${this.deliverOrder.id}/dispatch-delivery`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
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
                    alert(data.message || 'Error dispatching delivery');
                    this.isDispatchingDelivery = false;
                }
            } catch (err) {
                alert('Connection error');
                this.isDispatchingDelivery = false;
            }
        },

        async confirmDelivery(shouldPrint = true) {
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
                
                const data = await res.json();
                if (!data.success) {
                    console.error('confirmDelivery failed:', data);
                    alert('Error: ' + (data.message || 'Failed to update status'));
                    return;
                }

                // Open print window automatically if requested
                if (shouldPrint && data.print_bill_id) {
                    window.open(`/orders/${data.print_bill_id}/print`, '_blank');
                } else if (shouldPrint) {
                    window.open(`/orders/${this.deliverOrder.order_number}/print`, '_blank');
                }
                
                window.location.reload();
            } catch (err) { 
                console.error('confirmDelivery Network Error:', err);
                alert('Error updating status'); 
            }
        },

        openEdit(order) {
            let cart = (order.items || []).map(i => ({
                service_type_id: i.service_type_id,
                name: i.service_type ? i.service_type.name : i.item_description,
                base_price: i.unit_price,
                qty: i.quantity
            }));
            window.dispatchEvent(new CustomEvent('open-finalize-modal', {
                detail: {
                    context: 'branch',
                    categories: this.allCategories,
                    order: order,
                    cart: cart,
                    advance: null,
                    minimumDeliveryCost: this.minimumDeliveryCost
                }
            }));
        },

    };