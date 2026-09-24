        <script>
            function bookingWizard(categories, isLoggedIn, user, minOrderLimit, deliveryRanges, workshops, turnaroundTypes, extras, shopLat, shopLng, pickupTimePeriods, isCentralized, isHomepage, paymentMethods) {
                return {
                    categories: categories,
                    turnaroundTypes: turnaroundTypes,
                    extras: extras,
                    shopLat: shopLat,
                    shopLng: shopLng,
                    branchPickupTimes: pickupTimePeriods || [],
                    servicesLoading: false,
                    detectedBranchId: null,
                    isPlacingOrder: false,
                    isCentralized: isCentralized,
                    isHomepage: isHomepage,
                    
                    get viewOrder() {
                        if (this.isCentralized || this.isHomepage) {
                            return ['services', 'account', 'pickup', 'delivery'];
                        }
                        return ['account', 'pickup', 'services', 'delivery'];
                    },
                    
                    get viewLabels() {
                        if (this.isCentralized || this.isHomepage) {
                            return ['Services', 'Account', 'Pickup', 'Delivery & Pay'];
                        }
                        return ['Account', 'Pickup', 'Services', 'Delivery & Pay'];
                    },
                    
                    get currentView() {
                        return this.viewOrder[this.step - 1];
                    },

                    submitBooking() {
                        if (this.totalPrice < this.minLimit) {
                            alert("Minimum delivery limit is LKR " + this.minLimit.toFixed(2) + ". Please add more items to proceed.");
                            return;
                        }
                        const date = document.getElementById('pickup_date_input')?.value;
                        const time = document.getElementById('pickup_time_input')?.value;
                        const addr = document.getElementById('pickup_address')?.value;
                        
                        if (!date || !time || !addr) {
                            alert("Please fill in all pickup details (Date, Time, Address).");
                            return;
                        }
                        this.submitFinalBooking();
                    },

                    async submitFinalBooking() {
                        this.isPlacingOrder = true;
                        
                        const form = document.getElementById('bookingForm');
                        const formData = new FormData(form);
                        
                        try {
                            const response = await axios.post(form.action, formData, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            if (response.data && response.data.redirect) {
                                window.location.href = response.data.redirect;
                            } else {
                                window.location.href = '/'; 
                            }
                        } catch (error) {
                            this.isPlacingOrder = false;
                            
                            if (error.response && error.response.status === 422) {
                                const errors = error.response.data.errors;
                                let errorMessage = "Please check the following details:\n\n";
                                for (let key in errors) {
                                    errorMessage += "• " + errors[key][0] + "\n";
                                }
                                alert(errorMessage);
                            } else {
                                alert("An error occurred while placing the order. Please try again.");
                                console.error(error);
                            }
                        }
                    },
                    
                    formatTime(timeStr) {
                        if (!timeStr) return '';
                        const parts = timeStr.split(':');
                        const hour = parseInt(parts[0]);
                        const ampm = hour >= 12 ? 'PM' : 'AM';
                        const displayHour = hour % 12 || 12;
                        return `${displayHour}:${parts[1]} ${ampm}`;
                    },
