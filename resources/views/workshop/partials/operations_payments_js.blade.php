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

        async submitPaymentRequest() {
            if (!this.paymentDispatch) return;
            let amount = (this.paymentAmountType === 'full') 
                ? (parseFloat(this.paymentDispatch.order.total || 0) - parseFloat(this.paymentDispatch.order.paid_amount || 0))
                : parseFloat(this.customPaymentAmount || 0);
            if (amount <= 0) return alert('Invalid payment amount');

            this.isRequestingPayment = true;
            try {
                const res = await fetch(`/workshop/dispatches/${this.paymentDispatch.id}/request-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ amount: amount })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Payment Request Sent successfully via WhatsApp.');
                    this.showPaymentModal = false;
                } else {
                    alert(data.message || 'Failed to send payment request.');
                }
            } catch (err) {
                console.error(err);
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
                console.error(e);
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

