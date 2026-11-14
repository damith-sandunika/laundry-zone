                    initDataWatchers() {
                        this.$watch('step', value => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            
                            // Initialize pickup map on pickup view if not already done
                            if (this.currentView === 'pickup') {
                                setTimeout(() => {
                                    if (this.pickupMap) {
                                        this.pickupMap.invalidateSize();
                                    } else {
                                        this.initPickupMap();
                                    }
                                }, 500);
                            }
                            
                            // Initialize delivery map on delivery view if not same as pickup
                            if (this.currentView === 'delivery' && !this.sameAsPickup && this.deliveryPref === 'deliver_to_address') {
                                setTimeout(() => { if (this.deliveryMap) this.deliveryMap.invalidateSize(); this.initDeliveryMap(); }, 500);
                            }
                        });

                        this.$watch('sameAsPickup', value => {
                            if (!value && this.currentView === 'delivery') {
                                setTimeout(() => { if (this.deliveryMap) this.deliveryMap.invalidateSize(); this.initDeliveryMap(); }, 500);
                            }
                            this.calculateDeliveryFee();
                        });
                        
                        this.$watch('nearestWorkshop', value => {
                            if (value && value.id) {
                                if (!this.isCentralized && !this.isHomepage) {
                                    this.fetchBranchServices(value.id);
                                }
                            }
                        });
                        
                        this.$watch('deliveryPref', () => this.calculateDeliveryFee());

                        this.$watch('pickupDate', value => {
                            if (value) {
                                let pd = new Date(value);
                                pd.setDate(pd.getDate() + 2); // Default to 2 days after pickup
                                this.deliveryDate = pd.toISOString().split('T')[0];
                            }
                        });
                    },
