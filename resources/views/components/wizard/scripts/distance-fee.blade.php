                    calculateDistance(lat1, lon1, lat2, lon2) {
                        const R = 6371; // km
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLon = (lon2 - lon1) * Math.PI / 180;
                        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                Math.sin(dLon/2) * Math.sin(dLon/2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                        return R * c;
                    },
                    
                    calculateDeliveryFee() {
                        let distanceToPickup = 0;
                        let distanceToDelivery = 0;
                        let pickupFee = 0;
                        let deliveryFee = 0;
                        let isPickupOutOfRange = false;
                        let isDeliveryOutOfRange = false;
                        
                        // Default to 15km if no delivery ranges are set
                        let maxAllowedDistance = this.deliveryRanges.length > 0 ? 
                            Math.max(...this.deliveryRanges.map(r => Number(r.max_distance_km))) : 15;
                        
                        const getFeeForDistance = (distance) => {
                            if (this.deliveryRanges.length === 0) {
                                let rawFee = distance * 100;
                                return rawFee > 0 && rawFee < 300 ? 300 : Math.round(rawFee);
                            }
                            for (let range of this.deliveryRanges) {
                                if (distance >= Number(range.min_distance_km) && distance <= Number(range.max_distance_km)) {
                                    return Number(range.cost);
                                }
                            }
                            return -1; // Out of all ranges
                        };
                        
                        // Use nearest workshop as base
                        const wsLat = this.baseLat;
                        const wsLng = this.baseLng;
                        
                        // Distance to pickup
                        if (this.pickupLat && this.pickupLng) {
                            distanceToPickup = this.calculateDistance(wsLat, wsLng, this.pickupLat, this.pickupLng);
                            this.pickupDistance = Math.round(distanceToPickup * 10) / 10; // 1 decimal
                            if (distanceToPickup > maxAllowedDistance) {
                                isPickupOutOfRange = true;
                                pickupFee = -1;
                            } else {
                                pickupFee = getFeeForDistance(distanceToPickup);
                            }
                        } else {
                            this.pickupDistance = 0;
                        }
                        
                        // Distance to delivery
                        if (this.deliveryPref === 'deliver_to_address') {
                            if (this.sameAsPickup && this.pickupLat && this.pickupLng) {
                                distanceToDelivery = distanceToPickup;
                                this.deliveryDistance = this.pickupDistance;
                                isDeliveryOutOfRange = isPickupOutOfRange;
                                deliveryFee = pickupFee;
                            } else if (!this.sameAsPickup && this.deliveryLat && this.deliveryLng) {
                                distanceToDelivery = this.calculateDistance(wsLat, wsLng, this.deliveryLat, this.deliveryLng);
                                this.deliveryDistance = Math.round(distanceToDelivery * 10) / 10;
                                if (distanceToDelivery > maxAllowedDistance) {
                                    isDeliveryOutOfRange = true;
                                    deliveryFee = -1;
                                } else {
                                    deliveryFee = getFeeForDistance(distanceToDelivery);
                                }
                            } else {
                                this.deliveryDistance = 0;
                            }
                        }
                        
                        this.isOutOfRange = isPickupOutOfRange || (this.deliveryPref === 'deliver_to_address' && !this.sameAsPickup && isDeliveryOutOfRange);
                        const totalFee = (pickupFee > 0 ? pickupFee : 0) + (deliveryFee > 0 ? deliveryFee : 0);
                        this.estimatedDeliveryFee = Math.round(totalFee);
                    },

