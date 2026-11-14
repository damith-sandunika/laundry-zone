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
                    alert(data.message || 'Failed to dispatch for delivery.');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error');
            }
            this.isDispatchingDelivery = false;
        },

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
                console.error(err);
                alert('Connection error');
            }
            this.isHandoverSubmitting = false;
        },

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
                console.error(err);
                alert('Connection error');
            }
            this.isDoneSubmitting = false;
        },

