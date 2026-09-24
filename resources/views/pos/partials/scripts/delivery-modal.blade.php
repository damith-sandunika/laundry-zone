<script>
// Delivery Modal Component
        window.deliveryModal = function() {
            return {
                show: false,
                isSaving: false,
                selectedCust: null,
                cartItems: [],
                pickupAddress: '',
                deliveryAddress: '',
                pickupDateOnly: '',
                pickupTimeSlot: '',
                deliveryDateOnly: '',
                deliveryTimeSlot: '',
                notes: '',

                formatTime(timeStr) {
                    if(!timeStr) return '';
                    let [h, m] = timeStr.split(':');
                    h = parseInt(h);
                    let ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return h + ':' + m + ' ' + ampm;
                },

                init() {
                    this.$watch('show', v => {
                        if (v) {
                            // Set default pickup date to today
                            let d = new Date();
                            this.pickupDateOnly = d.toISOString().split('T')[0];
                        }
                    });
                    window.addEventListener('open-delivery-modal', (e) => {
                        this.selectedCust = e.detail.customer || null;
                        this.cartItems    = e.detail.cart || [];
                        // Pre-fill address from customer if available
                        if (this.selectedCust && this.selectedCust.address) {
                            this.pickupAddress = this.selectedCust.address;
                        } else {
                            this.pickupAddress = '';
                        }
                        this.deliveryAddress = '';
                        this.notes = '';
                        this.show = true;
                    });
                },

                async submitDelivery() {
                    if (!this.selectedCust) { alert('Please select a customer in POS first.'); return; }
                    if (!this.pickupAddress.trim()) { alert('Please enter the Pickup Address.'); return; }
                    if (!this.pickupDateOnly) { alert('Please select Pickup Date.'); return; }

                    let selectedTimeStart = this.pickupTimeSlot ? this.pickupTimeSlot.split('-')[0] : '00:00:00';
                    let combinedDateTime = this.pickupDateOnly + ' ' + selectedTimeStart;
                    
                    let deliveryCombined = null;
                    if (this.deliveryDateOnly && this.deliveryTimeSlot) {
                        let delTimeStart = this.deliveryTimeSlot.split('-')[0];
                        deliveryCombined = this.deliveryDateOnly + ' ' + delTimeStart;
                    }

                    this.isSaving = true;
                    try {
                        const payload = {
                            customer_id:      this.selectedCust.id,
                            pickup_address:   this.pickupAddress,
                            delivery_address: this.deliveryAddress || this.pickupAddress,
                            pickup_date:      combinedDateTime,
                            delivery_date:    deliveryCombined,
                            notes:            this.notes,
                            cart:             this.cartItems.length > 0 ? this.cartItems : [],
                            payment_mode:     'pay_on_pickup',
                        };

                        const res = await fetch('/pos/delivery', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.show = false;
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong.'));
                        }
                    } catch(e) {
                        alert('Network error. Please try again.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            };
        };
</script>
