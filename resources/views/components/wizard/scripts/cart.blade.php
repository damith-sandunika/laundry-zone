                    isCartExpanded: false,

                    get baseCartTotal() {
                        return this.cart.reduce((sum, item) => {
                            const base = parseFloat(item.base_price || 0) * parseFloat(item.qty || 1);
                            const extras = (item.selected_extras || []).reduce((es, ex) => {
                                const extraModel = this.extras.find(e => e.id === ex.id);
                                return es + (extraModel ? parseFloat(extraModel.price || 0) * (ex.qty || 1) : 0);
                            }, 0);
                            return sum + base + extras;
                        }, 0);
                    },

                    get calculatedGrandTotal() {
                        let total = this.baseCartTotal;
                        if (this.currentView === 'delivery' || this.step > this.viewOrder.indexOf('pickup') + 1) {
                            total += parseFloat(this.estimatedDeliveryFee || 0);
                        }
                        return total;
                    },

                    get totalPrice() {
                        return this.calculatedGrandTotal;
                    },

                    get formattedCart() {
                        return this.cart.map(item => {
                            let label = `${item.name} x${item.qty}`;
                            if (item.selected_extras && item.selected_extras.length > 0) {
                                const extraNames = item.selected_extras.map(ex => {
                                    const extraModel = this.extras.find(e => e.id === ex.id);
                                    return extraModel ? extraModel.name + (ex.qty > 1 ? ` x${ex.qty}` : '') : '';
                                }).filter(Boolean);
                                if (extraNames.length > 0) label += ` + ${extraNames.join(', ')}`;
                            }
                            return label;
                        }).join(', ');
                    },

                    openQtyModal(service) {
                        this.selectedService = service;
                        this.serviceQty = 1;
                        this.isQtyModalOpen = true;
                    },

                    closeQtyModal() {
                        this.isQtyModalOpen = false;
                        this.selectedService = null;
                    },

                    addToCart() {
                        if (!this.selectedService) return;
                        const existing = this.cart.find(i => i.service_type_id === this.selectedService.id);
                        if (existing) {
                            existing.qty += this.serviceQty;
                        } else {
                            this.cart.push({
                                service_type_id: this.selectedService.id,
                                name: this.selectedService.name,
                                base_price: parseFloat(this.selectedService.base_price || 0),
                                qty: this.serviceQty,
                                turnaround_type_id: this.globalTurnaround,
                                custom_turnaround_percentage: null,
                                turnaround_extra_charge: 0,
                                selected_extras: [],
                                is_special: false,
                            });
                        }
                        this.closeQtyModal();
                        this.isCartExpanded = true;
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

                    removeFromCart(index) {
                        this.cart.splice(index, 1);
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

                    updateQty(index, delta) {
                        const item = this.cart[index];
                        if (!item) return;
                        item.qty = Math.max(1, parseFloat(item.qty || 1) + delta);
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

                    openGlobalCheckout() {
                        if (this.cart.length === 0) return;
                        this.globalTurnaround = null;
                        this.globalExtras = {};
                        this.isGlobalCheckoutModalOpen = true;
                    },

                    get extrasTotalPrice() {
                        let total = 0;
                        for (let eId in this.globalExtras) {
                            const qty = this.globalExtras[eId] || 0;
                            const extraModel = this.extras.find(e => e.id === parseInt(eId));
                            if (extraModel && qty > 0) {
                                total += parseFloat(extraModel.price || 0) * qty;
                            }
                        }
                        return total;
                    },

                    confirmGlobalCheckout() {
                        // Remove extras that were added as line items and move them to globalExtras
                        let cleanCart = [];
                        this.cart.forEach(item => {
                            const extraModel = this.extras.find(e => e.name && e.name === item.name);
                            if (extraModel) {
                                const extraName = item.name;
                                const extraModel = this.extras.find(e => e.name.toLowerCase() === extraName.toLowerCase());
                                if (extraModel) {
                                    if (!this.globalExtras[extraModel.id]) this.globalExtras[extraModel.id] = 0;
                                    this.globalExtras[extraModel.id] += parseInt(item.qty);
                                } else {
                                    cleanCart.push(item);
                                }
                            } else {
                                cleanCart.push(item);
                            }
                        });
                        this.cart = cleanCart;

                        let selectedExtrasList = [];
                        for (let eId in this.globalExtras) {
                            if (this.globalExtras[eId] > 0) {
                                selectedExtrasList.push({ id: parseInt(eId), qty: this.globalExtras[eId] });
                            }
                        }

                        // Apply global turnaround to every cart item, but extras only to the first item
                        this.cart.forEach((item, index) => {
                            item.turnaround_type_id = this.globalTurnaround;
                            if (index === 0) {
                                item.selected_extras = selectedExtrasList;
                            } else {
                                item.selected_extras = [];
                            }
                        });
                        
                        this.isGlobalCheckoutModalOpen = false;
                        this.goToStep(this.viewOrder.indexOf('services') + 2);
                    },
                }
            }
        </script>
