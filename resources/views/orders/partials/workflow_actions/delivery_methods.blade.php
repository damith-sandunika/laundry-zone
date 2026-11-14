                async submitDelivery() {
                    if (this.isDispatchingDelivery) return;
                    if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
                    if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');

                    this.isDispatchingDelivery = true;
                    try {
                        let url = `/workshop/dispatches/${this.dispatch.id}/delivery`;
                        if (this.dispatch.status === 'received_at_branch') {
                            url = `/orders/${this.dispatch.order.id}/dispatch-delivery`;
                        }
                        
                        const res = await fetch(url, {
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

                async convertToCustomerWillPickup() {
                    if (!confirm('Customer will come and collect from branch. Convert this delivery order to pickup?')) return;
                    try {
                        const res = await fetch(`/orders/${this.dispatch.order.id}/convert-to-pickup`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to convert.');
                        }
                    } catch (err) {
                        alert('Connection error');
                    }
                },

                async submitHandover() {
                    this.isHandoverSubmitting = true;
                    try {
                        let url = `/workshop/dispatches/${this.dispatch.id}/handover`;
                        let body = {
                            payment_method: this.handoverPaymentMethod,
                            amount_paid: this.handoverAmountPaid ? parseFloat(this.handoverAmountPaid) : 0,
                            refund_overpayment: this.handoverRefundOverpayment ? 1 : 0
                        };

                        if (this.dispatch.status === 'received_at_branch') {
                            url = `/orders/${this.dispatch.order.id}/status`;
                            body = {
                                status: 'delivered',
                                payment_method: this.handoverPaymentMethod,
                                refund_overpayment: this.handoverRefundOverpayment ? 1 : 0
                            };
                        }

                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(body)
                        });
                        const data = await res.json();
                        if (data.success) {
                            const printId = data.order_number || data.print_bill_id;
                            if (this.handoverPrintReceipt && printId) {
                                window.open(`/orders/${printId}/print`, '_blank');
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
