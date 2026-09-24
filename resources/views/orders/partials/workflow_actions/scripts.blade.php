    <!-- Script logic -->
    <script>
        function orderWorkflowManager() {
            return {
                dispatch: null,

                showDoneModal: false,
                doneCubbyId: '',
                isDoneSubmitting: false,

                showDeliveryModal: false,
                deliveryMethod: 'rider',
                deliveryRiderId: '',
                deliveryPickmeCost: '',
                deliveryPaymentMethod: 'cash',
                isDispatchingDelivery: false,

                showHandoverModal: false,
                handoverPaymentMethod: 'cash',
                handoverAmountPaid: '',
                handoverPrintReceipt: true,
                isHandoverSubmitting: false,

                initData(dispatchObj) {
                    this.dispatch = dispatchObj;
                    if (this.dispatch && this.dispatch.order) {
                        this.handoverAmountPaid = Math.max(0, (parseFloat(this.dispatch.order.total||0) - parseFloat(this.dispatch.order.paid_amount||0))).toFixed(2);
                    }
                },

                openFinalizePage() {
                    window.location.href = '/workshop/orders/' + this.dispatch.order.order_number + '/finalize-bill';
                },

                async workshopAction(action) {
                    if(action === 'start-processing' && !confirm('Are you sure you want to start washing this order?')) return;

                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/${action}`, {
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

                async submitDone() {
                    this.isDoneSubmitting = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/complete`, {
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
                            if (data.order_id) {
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
                },

                async submitDelivery() {
                    if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
                    if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');

                    this.isDispatchingDelivery = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/delivery`, {
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
                            if (this.dispatch && this.dispatch.order && this.dispatch.order.order_number) {
                                window.open('/orders/' + this.dispatch.order.order_number + '/print', '_blank');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            alert(data.message || 'Failed to dispatch delivery.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isDispatchingDelivery = false;
                },

                async submitHandover() {
                    this.isHandoverSubmitting = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/handover`, {
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
                            if (this.handoverPrintReceipt && data.order_number) {
                                window.open(`/orders/${data.order_number}/print`, '_blank');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            alert(data.message || 'Failed to hand over.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isHandoverSubmitting = false;
                }
            }
        }
    </script>
