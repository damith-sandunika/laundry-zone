<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('finalizeOrderModal', () => ({
            showFinalizeModal: false,
            context: 'branch',
            categories: [],
            services: [],
            activeCategory: null,
            search: '',
            cart: [],
            requestedAdvance: null,
            discountPercentage: 0,
            deliveryFee: 0,
            paymentMethod: 'cash',
            isSaving: false,
            orderData: null,
            minimumDeliveryCost: 0,

            openModal(detail) {
                this.context = detail.context || 'branch';
                this.categories = detail.categories || [];
                this.services = [];
                this.categories.forEach(cat => {
                    if (cat.services) {
                        cat.services.forEach(s => {
                            s.category_id = cat.id;
                            this.services.push(s);
                        });
                    }
                });
                if (detail.services) {
                    detail.services.forEach(s => {
                        if (!this.services.find(existing => existing.id === s.id)) {
                            s.category_id = 'uncategorized';
                            this.services.push(s);
                        }
                    });
                }

                this.orderData = detail.order;
                this.cart = detail.cart || [];
                this.requestedAdvance = detail.advance || null;
                this.minimumDeliveryCost = parseFloat(detail.minimumDeliveryCost || 0);
                this.paymentMethod = this.orderData?.payment_method || 'cash';

                if (this.orderData && this.orderData.customer && this.orderData.customer.default_discount) {
                    this.discountPercentage = parseFloat(this.orderData.customer.default_discount) || 0;
                } else {
                    this.discountPercentage = 0;
                }

                this.deliveryFee = parseFloat(this.orderData?.delivery_fee || 0);
                this.showFinalizeModal = true;
            },

            get filteredServices() {
                let filtered = this.services;
                if (this.activeCategory !== null) {
                    filtered = filtered.filter(s => s.category_id === this.activeCategory);
                }
                if (this.search) {
                    filtered = filtered.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()));
                }
                return filtered;
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + ((parseFloat(item.base_price) || 0) * (parseFloat(item.qty) || 0)), 0);
            },

            get total() {
                return (this.subtotal * (1 - (this.discountPercentage / 100))) + (parseFloat(this.deliveryFee) || 0);
            },

            addToCart(service) {
                let existing = this.cart.find(i => i.service_type_id === service.id);
                if (existing) {
                    existing.qty++;
                } else {
                    this.cart.push({
                        service_type_id: service.id,
                        name: service.name,
                        base_price: service.base_price,
                        qty: 1
                    });
                }
            },

            async saveOrder(action) {
                if (this.cart.length === 0) return alert('Cart is empty!');

                let endpoint = `/orders/${this.orderData.order_number}/update`;
                let payload = {
                    cart: this.cart,
                    discount_percentage: this.discountPercentage,
                    delivery_fee: this.deliveryFee,
                    requested_advance: this.requestedAdvance,
                    next_action: action === 'save' ? null : action
                };

                if (this.context === 'correction') {
                    let reason = prompt('Please enter a reason for this correction request:');
                    if (!reason) return;
                    payload.payment_method = this.paymentMethod;
                    endpoint = `/orders/${this.orderData.order_number}/corrections`;
                    payload = {
                        type: 'order_change',
                        reason: reason,
                        new_data: payload
                    };
                }

                this.isSaving = true;
                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to update');
                    }
                } catch (e) {
                    alert('Error saving order');
                }
                this.isSaving = false;
            }
        }));
    });
</script>
