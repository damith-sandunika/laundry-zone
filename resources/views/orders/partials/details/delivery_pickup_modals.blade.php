        <!-- Convert to Delivery Modal -->
        <div x-data="convertDeliveryModal()" @open-convert-delivery-modal.window="openModal()">
            <div x-cloak x-show="isOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
                <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                    @click.away="isOpen = false">
                    <div
                        style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fff7ed;">
                        <div>
                            <h3 style="font-size:18px;font-weight:900;color:#ea580c;margin:0;">🚚 Convert to Delivery
                            </h3>
                            <p style="font-size:12px;color:#c2410c;margin:2px 0 0;font-weight:600;">Order
                                #{{ $order->order_number }}</p>
                        </div>
                        <button @click="isOpen = false"
                            style="background:none;border:none;cursor:color:#ea580c;font-size:20px;">✕</button>
                    </div>
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Delivery
                                Address *</label>
                            <textarea x-model="address" rows="2"
                                style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                placeholder="Enter full delivery address..."></textarea>
                        </div>

                        <div style="display:flex;gap:16px;">
                            <div style="flex:1;">
                                <label
                                    style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Delivery
                                    Fee (Rs.)</label>
                                <input type="number" step="0.01" x-model="fee"
                                    style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                    placeholder="0.00">
                            </div>
                            <div style="flex:1;">
                                <label
                                    style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Phone</label>
                                <input type="text" x-model="phone"
                                    style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;"
                                    onfocus="this.style.borderColor='#f97316'" onblur="this.style.borderColor='#e2e8f0'"
                                    placeholder="Phone number">
                            </div>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="isOpen = false"
                                style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="button" @click="submitConvert()" :disabled="isSubmitting"
                                style="flex:1;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(234,88,12,0.3);">
                                <span x-text="isSubmitting ? 'Converting...' : 'Confirm Delivery'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function convertDeliveryModal() {
                return {
                    isOpen: false,
                    isSubmitting: false,
                    address: `{{ addslashes($order->customer->address ?? '') }}`,
                    phone: `{{ addslashes($order->customer->phone ?? '') }}`,
                    fee: 0,
                    openModal() {
                        this.isOpen = true;
                    },
                    async submitConvert() {
                        if (!this.address.trim()) {
                            alert('Delivery address is required.');
                            return;
                        }
                        this.isSubmitting = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/convert-to-delivery`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    delivery_address: this.address,
                                    delivery_fee: this.fee,
                                    contact_phone: this.phone
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Error converting order.');
                            }
                        } catch (e) {
                            alert('Connection error.');
                        }
                        this.isSubmitting = false;
                    }
                }
            }

            function convertPickupModal() {
                return {
                    showConvertPickupModal: false,
                    isConvertingPickup: false,
                    convertPickupDeliveryFee: {{ $order->delivery_fee ?? 0 }},
                    convertPickupDeductAmount: {{ $order->delivery_fee ?? 0 }},
                    // Overpayment modal
                    showOverpaymentModal: false,
                    overpaymentAmount: 0,
                    isProcessingRefund: false,
                    openModal() {
                        this.showConvertPickupModal = true;
                        this.convertPickupDeductAmount = this.convertPickupDeliveryFee;
                    },
                    async submitConvertPickup() {
                        this.isConvertingPickup = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/convert-to-pickup`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    deduct_amount: this.convertPickupDeductAmount
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.showConvertPickupModal = false;
                                if (data.overpayment > 0) {
                                    // Show overpayment modal instead of reloading
                                    this.overpaymentAmount = data.overpayment;
                                    this.showOverpaymentModal = true;
                                } else {
                                    window.location.reload();
                                }
                            } else {
                                alert(data.message || 'Failed to convert.');
                            }
                        } catch (err) {
                            alert('Connection error');
                        }
                        this.isConvertingPickup = false;
                    },
                    async handleOverpayment(action) {
                        this.isProcessingRefund = true;
                        try {
                            const res = await fetch(`/orders/{{ $order->id }}/refund-overpayment`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ action: action })
                            });
                            const data = await res.json();
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Failed to process.');
                            }
                        } catch (err) {
                            alert('Connection error');
                        }
                        this.isProcessingRefund = false;
                    }
                }
            }
        </script>
