                async saveOrder(action) {
                    if (this.cart.length === 0) return alert("Cart is empty!");
                    this.isSaving = true;

                    try {
                        const res = await fetch(`/orders/${this.orderNumber}/update`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]").content
                            },
                            body: JSON.stringify({
                                cart: this.cart,
                                discount_type: this.discountType,
                                discount_percentage: this.discountType === 'percent' ? this.discountPercent : (this.subtotal > 0 ? (this.discountAmount / this.subtotal) * 100 : 0),
                                discount_amount: this.discountAmount,
                                delivery_fee: this.deliveryFee,
                                requested_advance: this.requestedAdvance,
                                next_action: action
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && document.referrer.indexOf('finalize-bill') === -1) {
                                window.location.href = document.referrer;
                            } else {
                                window.location.href = `/orders/view/${this.orderNumber}`;
                            }
                        } else {
                            alert(data.message || "Failed to update");
                        }
                    } catch (e) {
                        alert("Error saving order");
                    }

                    this.isSaving = false;
                }
