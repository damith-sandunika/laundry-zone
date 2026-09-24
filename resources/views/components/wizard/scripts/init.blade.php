                    async fetchBranchServices(branchId) {
                        if (!branchId) return;
                        if (this.detectedBranchId === branchId) return;
                        this.servicesLoading = true;
                        try {
                            const response = await axios.get(`/customer/branch-services/${branchId}`);
                            if (response.data.categories) {
                                this.categories = response.data.categories;
                            }
                            if (response.data.turnaroundTypes) {
                                this.turnaroundTypes = response.data.turnaroundTypes;
                            }
                            if (response.data.extras) {
                                this.extras = response.data.extras;
                            }
                            if (response.data.pickupTimePeriods) {
                                this.branchPickupTimes = response.data.pickupTimePeriods;
                            }
                            this.detectedBranchId = branchId;
                            this.currentViewLevel = 0;
                            this.selectedMainCategory = null;
                            this.selectedSubcategory = null;
                        } catch(e) {
                            console.error('Failed to fetch branch services', e);
                        } finally {
                            this.servicesLoading = false;
                        }
                    },

                    selectedMainCategory: null,
                    selectedSubcategory: null,
                    currentSubcategories: [],
                    currentServices: [],
                    currentViewLevel: 0, // 0: Categories, 1: Subcategories, 2: Services
                    
                    init() {
                        let catId = null;
                        const urlParams = new URLSearchParams(window.location.search);
                        catId = urlParams.get('category');
                        if (!catId) {
                            let hash = window.location.hash;
                            if (hash.includes('category=')) {
                                catId = hash.split('category=')[1].split('&')[0];
                            }
                        }
                        if (catId) {
                            setTimeout(() => {
                                for (let cat of Object.values(this.categories)) {
                                    if (cat.id == catId) {
                                        this.selectLevel1(cat);
                                        const servicesHeader = document.getElementById('services');
                                        if (servicesHeader) {
                                            const y = servicesHeader.getBoundingClientRect().top + window.scrollY - 80;
                                            window.scrollTo({top: y, behavior: 'smooth'});
                                        }
                                        return;
                                    }
                                    if (cat.children && cat.children.length > 0) {
                                        for (let sub of Object.values(cat.children)) {
                                            if (sub.id == catId) {
                                                this.selectLevel1(cat);
                                                this.selectLevel2(sub);
                                                const servicesHeader = document.getElementById('services');
                                                if (servicesHeader) {
                                                    const y = servicesHeader.getBoundingClientRect().top + window.scrollY - 80;
                                                    window.scrollTo({top: y, behavior: 'smooth'});
                                                }
                                                return;
                                            }
                                        }
                                    }
                                }
                            }, 300);
                        }
                    },
                    
                    // Modal state
                    
                    selectedService: null,
                    serviceQty: 1,
                    selectedTurnaround: '',
                    
                    // New step 3 state
                    isCartExpanded: false,
                    isQtyModalOpen: false,
                    isGlobalCheckoutModalOpen: false,
                    globalTurnaround: '',
                    deliveryDate: '',
                    pickupDate: new Date().toISOString().split('T')[0],
                    deliveryTimePeriodId: '',
                    globalExtras: {},                    routeLayer: null,
                    step: 1,
                    highestStepReached: 1,
                    minLimit: Number(minOrderLimit || 0),
                    expandedCategory: null,
                    cart: [],
                    
                    // Auth state
                    isAuthenticated: isLoggedIn,
                    authCustomer: user,
                    authTab: 'login',
                    authLoading: false,
                    authError: null,
                    loginData: { phone: '', password: '' },
                    registerData: { name: '', phone: '', email: '', password: '' },

                    // Delivery state
                    deliveryPref: 'deliver_to_address',
                    sameAsPickup: false,
                    selectedPaymentMethod: (paymentMethods && paymentMethods.length > 0) ? paymentMethods[0].type : 'cash_on_delivery',
                    
                    // Maps
                    pickupMap: null,
                    deliveryMap: null,
                    deliveryMapInitialized: false,
                    pickupLat: null,
                    pickupLng: null,
                    deliveryLat: null,
                    deliveryLng: null,
                    estimatedDeliveryFee: 0,
                    pickupDistance: 0,       // km from workshop to pickup
                    deliveryDistance: 0,     // km from workshop to delivery
                    pickupRouteLayer: null,  // OSRM route polyline on pickup map
                    deliveryRouteLayer: null,// OSRM route polyline on delivery map
                    deliveryRanges: deliveryRanges || [],
                    // Workshops array for multi-workshop support
                    workshopsArray: workshops || [],
                    nearestWorkshop: workshops && workshops.length > 0 ? workshops[0] : null,
                    baseLat: (workshops && workshops.length > 0 && !isNaN(parseFloat(workshops[0].latitude))) ? parseFloat(workshops[0].latitude) : 6.9322889,
                    baseLng: (workshops && workshops.length > 0 && !isNaN(parseFloat(workshops[0].longitude))) ? parseFloat(workshops[0].longitude) : 79.8567772,
                    isOutOfRange: false,

                    initData() {
                        let pd = new Date(this.pickupDate);
                        pd.setDate(pd.getDate() + 2);
                        this.deliveryDate = pd.toISOString().split('T')[0];
                        const savedHomepageCart = localStorage.getItem('customer_booking_cart');
                        if (savedHomepageCart) {
                            try {
                                const parsedCart = JSON.parse(savedHomepageCart);
                                if (Array.isArray(parsedCart) && parsedCart.length > 0) {
                                    this.cart = parsedCart;
                                    localStorage.removeItem('customer_booking_cart');
                                    
                                    const savedTurnaround = localStorage.getItem('customer_booking_turnaround');
                                    if (savedTurnaround) {
                                        this.globalTurnaround = savedTurnaround;
                                        localStorage.removeItem('customer_booking_turnaround');
                                    }
                                    const savedExtras = localStorage.getItem('customer_booking_extras');
                                    if (savedExtras) {
                                        try {
                                            this.globalExtras = JSON.parse(savedExtras);
                                            localStorage.removeItem('customer_booking_extras');
                                        } catch(err) {}
                                    }

                                    // Auto-advance step: if not logged in -> step 2 (Account), if logged in -> step 3 (Pickup)
                                    // Wait, if decentralized, Account is step 1, Pickup is step 2.
                                    if (!this.isAuthenticated) {
                                        this.step = this.viewOrder.indexOf('account') + 1;
                                        this.highestStepReached = this.step;
                                    } else {
                                        this.step = this.viewOrder.indexOf('pickup') + 1;
                                        this.highestStepReached = this.step;
                                    }
                                }
                            } catch(e) {
                                console.error('Error parsing customer_booking_cart from localStorage', e);
                            }
                        }
                        
                        // If decentralized, fetch services for the initial default nearest workshop
                        if (!this.isCentralized && !this.isHomepage && this.nearestWorkshop) {
                            this.fetchBranchServices(this.nearestWorkshop.id);
                        }

                        // Check if we are starting on pickup
                        if (this.currentView === 'pickup') {
                            setTimeout(() => {
                                if (this.pickupMap) {
                                    this.pickupMap.invalidateSize();
                                } else {
                                    this.initPickupMap();
                                }
                            }, 500);
                        }

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
