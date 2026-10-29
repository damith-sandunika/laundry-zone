    <script>
        window.__ordersPageData = {
            categories: @js($categories),
            orders: @js($orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->items() : $orders),
            minimumDeliveryCost: {{ (float) ($minimumDeliveryCost ?? 0) }},
            routes: {
                bulkDispatch: '{{ route('orders.bulkDispatchWorkshop') }}',
                bulkStatus: '{{ route('orders.bulkStatus') }}',
            }
        };
    </script>
    <script>
        const initOrdersPage = () => {
            Alpine.data('ordersPage', () => ({
                currentTab: '{{ $tab ?? 'received' }}',
                selectedOrders: [],
                showBulkModal: false,
                bulkStatus: 'done',
                bulkCubbyId: '',
                bulkWorkshopId: '',

                showCubbetModal: false,
                cubbetOrder: null,
                selectedCubby: '',
                showReverseModal: false,
                reverseOrderId: null,
                reversePassword: '',
                reverseError: '',
                showEditModal: false,
                editOrderData: null,
                editCart: [],
                editDiscount: 0,
                editDeliveryFee: 0,
                editReason: '',
                editIsSaving: false,
                editErrorMsg: '',
                editSuccessMsg: '',
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
                    return this.ordersList;
                },

                get counts() {
                    return @js($counts);
                },

                openPaymentModal(order) { this.paymentOrder = order; this.paymentAmountType = 'full'; this.customPaymentAmount = ''; this.showPaymentModal = true; },
                openSlipModal(receipt, order) { this.selectedSlip = receipt; this.selectedOrder = order; this.showSlipModal = true; },

                openCubbetPopup(order) { this.cubbetOrder = order; this.selectedCubby = ''; this.showCubbetModal = true; },

                getReceipts(order) {
                    if (!order || !order.payment_transactions) return [];
                    return order.payment_transactions.filter(p => p.receipt_path).map(p => ({
                        id: p.id, amount: p.amount, url: p.receipt_path.startsWith('http') ? p.receipt_path : '/storage/' + p.receipt_path,
                        status: p.status, reject_reason: p.reject_reason,
                        created_at: p.created_at ? new Date(p.created_at).toLocaleString() : ''
                    }));
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



                async submitDispatch() {
                    if (!this.bulkWorkshopId) { alert('Please select a workshop'); return; }
                    try {
                        await fetch(window.__ordersPageData.routes.bulkDispatch, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ order_ids: this.selectedOrders, workshop_branch_id: this.bulkWorkshopId, dispatch_notes: 'Dispatched from branch' })
                        });
                        window.location.reload();
                    } catch (err) { window.location.reload(); }
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
                            this.reverseError = data.message || 'Verification failed';
                        }
                    } catch (err) {
                        this.reverseError = 'Error connecting to server';
                    }
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



                openEdit(order) {
                    this.editOrderData = order;
                    this.editCart = (order.items || []).map(item => ({
                        service_type_id: item.service_type_id,
                        name: item.service_type ? item.service_type.name : (item.item_description || 'Service'),
                        base_price: parseFloat(item.unit_price || 0),
                        qty: parseFloat(item.quantity || 1),
                    }));
                    if (this.editCart.length === 0 && order.delivery_appointment && order.delivery_appointment.cart_json) {
                        try {
                            const parsed = JSON.parse(order.delivery_appointment.cart_json);
                            this.editCart = parsed.map(i => ({
                                service_type_id: i.service_type_id || i.id || null,
                                name: i.name || 'Service',
                                base_price: parseFloat(i.base_price || 0),
                                qty: parseFloat(i.qty || 1),
                            }));
                        } catch (e) { }
                    }
                    this.editDiscount = parseFloat(order.discount || 0);
                    this.editDeliveryFee = parseFloat(order.delivery_fee || 0);
                    this.editReason = '';
                    this.editErrorMsg = '';
                    this.editSuccessMsg = '';
                    this.showEditModal = true;
                },

                get editSubtotal() {
                    return this.editCart.reduce((s, i) => s + parseFloat(i.base_price || 0) * parseFloat(i.qty || 1), 0);
                },

                get editTotal() {
                    const sub = this.editSubtotal;
                    const disc = this.editDiscount;
                    return Math.max(0, sub - disc + this.editDeliveryFee);
                },

                async submitEditCorrection() {
                    if (!this.editReason.trim()) { this.editErrorMsg = 'Please enter a reason for this correction.'; return; }
                    if (this.editCart.length === 0) { this.editErrorMsg = 'Cart cannot be empty.'; return; }
                    this.editIsSaving = true;
                    this.editErrorMsg = '';
                    this.editSuccessMsg = '';
                    try {
                        const res = await fetch('/orders/' + this.editOrderData.id + '/corrections', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({
                                type: 'items_change',
                                reason: this.editReason,
                                new_data: {
                                    cart: this.editCart,
                                    discount_percentage: this.editDiscount,
                                    delivery_fee: this.editDeliveryFee,
                                }
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.editSuccessMsg = data.message || 'Correction submitted!';
                            setTimeout(() => { this.showEditModal = false; window.location.reload(); }, 1500);
                        } else {
                            this.editErrorMsg = data.message || 'Failed to submit correction.';
                        }
                    } catch (e) { this.editErrorMsg = 'Connection error.'; }
                    this.editIsSaving = false;
                },
            }));
        };

        if (window.Alpine) {
            initOrdersPage();
        } else {
            document.addEventListener('alpine:init', initOrdersPage);
        }
    </script>
