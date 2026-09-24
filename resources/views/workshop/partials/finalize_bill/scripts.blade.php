    <script>
        function finalizeBill() {
            return {
                services: @json($services),
                categories: @json($categories),
                allTurnaroundTypes: @json($turnaroundTypes ?? []),
                allExtras: @json($systemExtras ?? []),
                searchQuery: "",
                activeCategory: null,
                activeSubcategory: null,
                cart: @json($cartItems),

                deliveryFee: {{ (float) ($dispatch->order->delivery_fee ?? $order->delivery_fee ?? 0) }},
                discountPercent: 0,
                requestedAdvance: 0,
                isSaving: false,
                orderId: {{ $dispatch->order_id ?? $order->id ?? 0 }},
                orderNumber: '{{ $dispatch->order->order_number ?? $order->order_number ?? '' }}',

                init() {
                    if (this.categories.length > 0) {
                        this.activeCategory = this.categories[0].id;
                        // Auto-select first sub-category if it exists
                        const firstCat = this.categories[0];
                        if (firstCat.children && firstCat.children.length > 0) {
                            this.activeSubcategory = firstCat.children[0].id;
                        }
                    }
                },

                get filteredCategories() {
                    if (!this.searchQuery) return this.categories;
                    let q = this.searchQuery.toLowerCase();
                    return this.categories.filter(c => c.name.toLowerCase().includes(q));
                },

                get activeCategoryData() {
                    return this.categories.find(c => c.id === this.activeCategory);
                },

                get currentSubcategories() {
                    if (!this.activeCategory) return [];
                    const cat = this.categories.find(c => c.id === this.activeCategory);
                    return cat && cat.children ? cat.children : [];
                },

                get filteredServices() {
                    let result = this.services;
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        result = result.filter(s => s.name.toLowerCase().includes(q));
                    }
                    if (this.activeCategory) {
                        const cat = this.categories.find(c => c.id === this.activeCategory);
                        let validIds = [this.activeCategory];
                        if (cat && cat.children) {
                            validIds.push(...cat.children.map(c => c.id));
                        }

                        if (this.activeSubcategory) {
                            // Show sub-category services AND services directly under the parent category
                            validIds = [this.activeSubcategory, this.activeCategory];
                        }

                        result = result.filter(s => {
                            let sId = s.service_category_id || s.category_id;
                            return validIds.map(String).includes(String(sId));
                        });
                    } else {
                        result = [];
                    }
                    return result;
                },

                addToCart(service) {
                    // Intercept ServiceTypes that are actually Extras
                    if (service.name.toLowerCase().startsWith('extra:')) {
                        let extraName = service.name.substring(6).trim();
                        let extraModel = this.allExtras.find(e => e.name.toLowerCase() === extraName.toLowerCase());

                        if (extraModel && this.cart.length > 0) {
                            let firstItem = this.cart[0];
                            if (!firstItem.selected_extras) firstItem.selected_extras = [];
                            let existingEx = firstItem.selected_extras.find(e => e.id === extraModel.id);
                            if (existingEx) {
                                existingEx.qty++;
                            } else {
                                firstItem.selected_extras.push({ id: extraModel.id, qty: 1 });
                            }
                            return;
                        }
                    }

                    let defaultTt = this.allTurnaroundTypes.find(tt => tt.is_default);
                    let defaultTtId = defaultTt ? defaultTt.id : null;
                    
                    let existing = this.cart.find(i => 
                        i.service_type_id === service.id && 
                        parseFloat(i.base_price) === parseFloat(service.base_price) &&
                        i.turnaround_type_id == defaultTtId &&
                        (!i.selected_extras || i.selected_extras.length === 0)
                    );
                    
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({
                            service_type_id: service.id,
                            name: service.name,
                            base_price: service.base_price,
                            qty: 1,
                            turnaround_type_id: defaultTtId,
                            custom_turnaround_percentage: null,
                            selected_extras: []
                        });
                    }
                },

                getCategoryString(serviceId) {
                    let serviceArray = this.services || this.allServices || [];
                    let service = serviceArray.find(s => s.id === serviceId);
                    if (!service) return '';
                    let cId = service.service_category_id || service.category_id;
                    for (let cat of this.categories) {
                        if (cat.id == cId) return cat.name;
                        if (cat.children) {
                            for (let sub of cat.children) {
                                if (sub.id == cId) return cat.name + ' > ' + sub.name;
                            }
                        }
                    }
                    return '';
                },
                getItemTotal(item) {
                    let base = parseFloat(item.base_price || 0) * parseFloat(item.qty || 1);
                    let turnaroundCharge = 0;
                    if (item.turnaround_type_id === 'custom') {
                        turnaroundCharge = base * (parseFloat(item.custom_turnaround_percentage || 0) / 100);
                    } else if (item.turnaround_type_id) {
                        let tt = this.allTurnaroundTypes.find(t => t.id == item.turnaround_type_id);
                        if (tt) {
                            if (tt.markup_type === 'percentage') {
                                turnaroundCharge = base * (parseFloat(tt.markup_value) / 100);
                            } else {
                                turnaroundCharge = parseFloat(tt.markup_value) * (parseFloat(item.qty) || 1);
                            }
                        }
                    }
                    let extrasTotal = 0;
                    if (item.selected_extras && item.selected_extras.length > 0) {
                        for (let ex of item.selected_extras) {
                            let eData = this.allExtras.find(e => e.id == ex.id);
                            if (eData) {
                                extrasTotal += parseFloat(eData.price) * parseFloat(ex.qty || 1);
                            }
                        }
                    }
                    return base + turnaroundCharge + extrasTotal;
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.getItemTotal(item), 0);
                },

                get total() {
                    let baseSub = this.subtotal;
                    let discountAmount = baseSub * (this.discountPercent / 100);
                    let calculated = (baseSub - discountAmount) + this.deliveryFee;
                    return Math.max({{ $minimumDeliveryCost }}, calculated);
                },

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
                                discount_percentage: this.discountPercent,
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
            }
        }
    </script>
