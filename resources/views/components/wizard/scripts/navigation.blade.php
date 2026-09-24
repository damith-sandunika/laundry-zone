                    async goToStep(n) {
                        const targetView = this.viewOrder[n - 1];
                        
                        if (n > this.highestStepReached + 1) {
                            // Allow skipping account if already authenticated
                            if (!(targetView === 'pickup' && this.viewOrder[this.highestStepReached - 1] === 'services' && this.isAuthenticated)) {
                                return;
                            }
                        }
                        
                        // Validation: If leaving services, must have items in cart
                        if (this.viewOrder[this.step - 1] === 'services' && n > this.step && this.cart.length === 0) {
                            alert("Please select at least one service before proceeding.");
                            this.step = this.viewOrder.indexOf('services') + 1;
                            return;
                        }

                        // Validation: Must be authenticated to proceed to Pickup or Delivery (or Services if decentralized)
                        if (['pickup', 'delivery'].includes(targetView) && !this.isAuthenticated) {
                            alert("Please login or register first to continue your booking.");
                            this.step = this.viewOrder.indexOf('account') + 1;
                            return;
                        }

                        // Validation logic before moving to Delivery & Pay
                        if (targetView === 'delivery') {
                            if (this.totalPrice < this.minLimit) {
                                alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                                return;
                            }
                            
                            if (this.viewOrder.indexOf('pickup') < n - 1) {
                                const date = document.getElementById('pickup_date_input')?.value;
                                const time = document.getElementById('pickup_time_input')?.value;
                                const addr = document.getElementById('pickup_address')?.value;
                                
                                if (!date || !time || !addr) {
                                    alert("Please fill in all pickup details (Date, Time, Address).");
                                    this.step = this.viewOrder.indexOf('pickup') + 1;
                                    return;
                                }
                            }
                        }

                        this.step = n;
                        if (n > this.highestStepReached) {
                            this.highestStepReached = n;
                        }

                        this.$nextTick(() => {
                            const servicesHeader = document.getElementById('services');
                            const wiz = document.getElementById('booking-wizard-container');
                            const target = servicesHeader || wiz;
                            if (target) {
                                const y = target.getBoundingClientRect().top + window.scrollY - 80;
                                window.scrollTo({top: y, behavior: 'smooth'});
                            }
                        });
                    },

                    // ─── Category Drill-Down Navigation ───
                    selectLevel1(cat) {
                        this.selectedMainCategory = cat;
                        this.selectedSubcategory = null;

                        // If category has no children, go straight to services level
                        if (!cat.children || cat.children.length === 0) {
                            this.currentServices = this.categories
                                .flatMap(c => c.children || [])
                                .filter(sub => sub.parent_id == cat.id)
                                .flatMap(sub => sub.services || []);

                            // Fallback: services directly on main category
                            if (this.currentServices.length === 0) {
                                this.currentServices = (cat.services || []).filter(s => s.is_active !== false);
                            }

                            this.currentSubcategories = [];
                            this.currentViewLevel = 2;
                        } else {
                            // Has sub-categories — show subcategory grid
                            this.currentSubcategories = (cat.children || []).filter(s => s.is_active !== false);
                            this.currentServices = [];
                            this.currentViewLevel = 1;
                        }
                    },

                    selectLevel2(sub) {
                        this.selectedSubcategory = sub;
                        this.currentServices = (sub.services || []).filter(s => s.is_active !== false);
                        this.currentViewLevel = 2;
                    },

                    openGlobalCheckoutModal() {
                        if (this.cart.length === 0) return;
                        this.isGlobalCheckoutModalOpen = true;
                    },

                    proceedToDelivery() {
                        this.isGlobalCheckoutModalOpen = false;
                        // Go to the NEXT step after 'services', not directly to pickup
                        // goToStep's own auth check will handle skipping 'account' if already logged in
                        const nextStep = this.viewOrder.indexOf('services') + 2;
                        this.goToStep(nextStep);
                    },

                    updateCartQty(index, delta) {
                        const item = this.cart[index];
                        if (!item) return;
                        item.qty = Math.max(1, parseFloat(item.qty || 1) + delta);
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

                    removeFromCartByIndex(index) {
                        this.cart.splice(index, 1);
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

                    confirmAddToCart() {
                        if (!this.selectedService) return;
                        const key = `${this.selectedService.id}`;
                        const existing = this.cart.find(i => i.cart_key === key);
                        if (existing) {
                            existing.qty += this.serviceQty;
                        } else {
                            this.cart.push({
                                cart_key: key,
                                service_type_id: this.selectedService.id,
                                name: this.selectedService.name,
                                base_price: parseFloat(this.selectedService.base_price || 0),
                                qty: this.serviceQty,
                                turnaround_type_id: this.globalTurnaround || null,
                                custom_turnaround_percentage: null,
                                turnaround_extra_charge: 0,
                                selected_extras: [],
                                is_special: false,
                            });
                        }
                        this.isQtyModalOpen = false;
                        this.selectedService = null;
                        this.serviceQty = 1;
                        this.isCartExpanded = true;
                        localStorage.setItem('customer_booking_cart', JSON.stringify(this.cart));
                    },

