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
                handoverRefundOverpayment: true,
                isHandoverSubmitting: false,

                showBranchArrivalModal: false,
                branchArrivalCubbyId: '',
                isBranchArrivalSubmitting: false,

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
                            const autoPrintChit = {{ \App\Models\Setting::get('auto_print_workshop_chit', '1') == '1' ? 'true' : 'false' }};
                            if (action === 'start-processing' && data.order_number && autoPrintChit) {
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

                async submitBranchArrival() {
                    this.isBranchArrivalSubmitting = true;
                    try {
                        const res = await fetch(`/orders/${this.dispatch.order.id}/status`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                status: 'done',
                                cubby_id: this.branchArrivalCubbyId || null
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to confirm arrival.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isBranchArrivalSubmitting = false;
                },

                @include('orders.partials.workflow_actions.delivery_methods')
            }
        }
    </script>
