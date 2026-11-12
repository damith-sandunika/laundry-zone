                filterCustomers() {
                    const q = this.customerSearch.trim();
                    if (q.length === 0) {
                        this.filteredCustomers = [];
                        this.showCustDropdown = false;
                        return;
                    }

                    fetch('/customers/search?q=' + encodeURIComponent(q) + '&with_packages=1', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.filteredCustomers = data;
                        this.showCustDropdown = true;
                    })
                    .catch(err => console.error(err));
                },

                registerNewCustomerFromSearch() {
                    const searchVal = this.customerSearch.trim();
                    this.showCustDropdown = false;
                    window.dispatchEvent(new CustomEvent('open-customer-modal', {
                        detail: { phone: searchVal }
                    }));
                },

                selectCustomer(cust) {
                    this.selectedCustomer = cust;
                    this.customerSearch = '';
                    this.showCustDropdown = false;
                    
                    if (cust.default_discount) {
                        this.discountType = 'percentage';
                        this.discountValue = parseFloat(cust.default_discount) || 0;
                        this.discountPercentage = this.discountValue;
                    } else {
                        this.discountValue = 0;
                        this.discountPercentage = 0;
                    }

                    let pkgs = cust.customer_packages || [];
                    if(pkgs.length > 0) {
                        this.activePackage = pkgs[0];
                        this.packageCart = this.activePackage.services.map(s => {
                            return {
                                service_type_id: s.service_type.id,
                                name: s.service_type.name,
                                base_price: s.service_type.base_price,
                                price_type: s.service_type.price_type,
                                remaining: s.total_quantity - s.used_quantity,
                                qty: 0
                            };
                        });
                        
                        let d = new Date(); d.setDate(d.getDate() + 1); d.setHours(17, 0, 0, 0); 
                        this.dueDate = new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                    } else {
                        this.activePackage = null;
                        this.packageCart = [];
                    }
                },

                openPackageModal() {
                    console.log('Opening package modal', this.activePackage);
                    if(this.activePackage) {
                        this.showPackageModal = true;
                    }
                },

                clearCustomer() {
                    this.selectedCustomer = null;
                    this.customerSearch = '';
                    this.activePackage = null;
                    this.packageCart = [];
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

                    let defaultPrice = parseFloat(service.base_price);
                    let existingIdx2 = this.cart.findIndex(i => i.id === service.id && !i.is_special && parseFloat(i.base_price) === defaultPrice);
                    if (existingIdx2 !== -1) {
                        let existing = this.cart.splice(existingIdx2, 1)[0];
                        existing.qty += 1;
                        this.cart.unshift(existing);
                    } else { 
                        let defaultTt = this.allTurnaroundTypes.find(tt => tt.is_default);
                        this.cart.unshift({ 
                            ...service, 
                            qty: 1,
                            base_price: defaultPrice,
                            turnaround_type_id: defaultTt ? defaultTt.id : null,
                            custom_turnaround_percentage: null,
                            turnaround_multiplier: defaultTt ? defaultTt.multiplier : 1,
                            is_special: false,
                            selected_extras: []
                        }); 
                    }
                },

                getCategoryString(serviceId) {
                    let service = this.allServices.find(s => s.id === serviceId) || this.cart.find(i => i.id === serviceId);
                    if (!service) return '';
                    let cId = service.category_id;
                    for (let cat of this.allCategories) {
                        if (cat.id == cId) return cat.name;
                        if (cat.children) {
                            for (let sub of cat.children) {
                                if (sub.id == cId) return cat.name + ' > ' + sub.name;
                            }
                        }
                    }
                    return '';
                },

                removeFromCart(index) { this.cart.splice(index, 1); },

                updateQty(index, change) {
                    let currentQty = parseFloat(this.cart[index].qty) || 0;
                    let newQty = currentQty + change;
                    if (newQty > 0) this.cart[index].qty = Math.round(newQty * 100) / 100;
                    else this.removeFromCart(index);
                },

                getItemTotal(item) {
                    let base = parseFloat(item.base_price || 0) * (parseFloat(item.qty) || 0);
                    
                    let turnaroundCharge = 0;
                    if (item.turnaround_type_id === 'custom') {
                        turnaroundCharge = base * (parseFloat(item.custom_turnaround_percentage || 0) / 100);
                    } else if (item.turnaround_type_id) {
                        let tt = this.allTurnaroundTypes.find(t => t.id == item.turnaround_type_id);
                        if (tt) {
                            if (tt.markup_type === 'percentage') {
                                turnaroundCharge = base * (parseFloat(tt.markup_value) / 100);
                            } else {
                                turnaroundCharge = parseFloat(tt.markup_value) * (parseFloat(item.qty) || 0);
                            }
                        }
                    }

                    let extrasTotal = 0;
                    if (item.selected_extras && item.selected_extras.length > 0) {
                        item.selected_extras.forEach(selEx => {
                            let extra = this.allExtras.find(e => e.id == selEx.id);
                            if (extra) {
                                extrasTotal += parseFloat(extra.price) * (parseFloat(selEx.qty) || 1);
                            }
                        });
                    }
                    
                    return base + turnaroundCharge + extrasTotal;
                },

                get calculatedDiscount() {
                    if (this.discountType === 'fixed') {
                        const fixedVal = parseFloat(this.discountValue) || 0;
                        return Math.max(0, Math.min(this.subtotal, fixedVal));
                    }
                    const percent = parseFloat(this.discountValue) || 0;
                    return Math.max(0, this.subtotal * (percent / 100));
                },
                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + this.getItemTotal(item), 0);
                },
                get total() {
                    return Math.max(0, this.subtotal - this.calculatedDiscount);
                }, addSpecialToCart() {
                    if (!this.specialName || !this.specialPrice || this.specialPrice <= 0) {
                        alert('Please enter a service name and price.');
                        return;
                    }
                    this.cart.unshift({
                        id: 'special_' + Date.now(),
                        service_type_id: 9,
                        name: this.specialName,
                        base_price: parseFloat(this.specialPrice),
                        price_type: 'per_item',
                        qty: 1,
                        is_special: true
                    });
                    this.specialName = '';
                    this.specialPrice = 0;
                },
