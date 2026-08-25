
        window.posSystem = function() {
            return {
                allServices: @json($services),
                allCategories: @json($categories),
                allCustomers: @json($customers),
                allCustomerPackages: @json($customerPackages),
                activeCategory: null,
                searchQuery: '',
                customerSearch: '',
                filteredCustomers: [],
                showCustDropdown: false,
                selectedCustomer: null,
                cart: [],

                activePackage: null,
                showPackageModal: false,
                packageCart: [],

                showCheckoutModal: false,
                isPlacingOrder: false,
                paymentMode: 'pay_on_pickup',
                paymentMethod: 'cash',
                dueDate: '',
                discountPercentage: 0,
                specialName: '',
                specialPrice: 0,
                
                init() {
                    window.posAddCustomer = (customer) => {
                        this.allCustomers.push(customer);
                        this.selectCustomer(customer);
                    };
                },

                                get filteredServices() {
                    let filtered = this.allServices;
                    
                    if (this.activeCategory === 'uncategorized') {
                        filtered = filtered.filter(s => s.category_id === null);
                    } else if (this.activeCategory !== null) {
                        filtered = filtered.filter(s => s.category_id === this.activeCategory);
                    }
                    
                    if (this.searchQuery) {
                        filtered = filtered.filter(s => s.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                    }
                    return filtered;
                },

                getColorStyle(colorName) {
                    const styles = {
                        'blue': { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-600' },
                        'green': { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-600' },
                        'purple': { bg: 'bg-purple-50', border: 'border-purple-200', text: 'text-purple-600' },
                        'orange': { bg: 'bg-orange-50', border: 'border-orange-200', text: 'text-orange-600' },
                        'teal': { bg: 'bg-teal-50', border: 'border-teal-200', text: 'text-teal-600' },
                        'red': { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-600' }
                    };
                    return styles[colorName] || styles['blue'];
                },

                filterCustomers() {
                    const q = this.customerSearch.toLowerCase();
                    this.filteredCustomers = q.length > 0
                        ? this.allCustomers.filter(c => c.name.toLowerCase().includes(q) || c.phone.includes(q))
                        : this.allCustomers.slice(0, 8);
                    this.showCustDropdown = true;
                },

                selectCustomer(cust) {
                    this.selectedCustomer = cust;
                    this.customerSearch = '';
                    this.showCustDropdown = false;

                    let pkgs = this.allCustomerPackages.filter(p => p.customer_id === cust.id && p.status !== 'exhausted');
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
                    let existing = this.cart.find(i => i.id === service.id);
                    if (existing) { existing.qty += 1; } else { this.cart.push({ ...service, qty: 1 }); }
                },

                removeFromCart(index) { this.cart.splice(index, 1); },

                updateQty(index, change) {
                    let currentQty = parseFloat(this.cart[index].qty) || 0;
                    let newQty = currentQty + change;
                    if (newQty > 0) this.cart[index].qty = Math.round(newQty * 100) / 100;
                    else this.removeFromCart(index);
                },

                get subtotal() {
                    return this.cart.reduce((sum, item) => sum + (item.base_price * (parseFloat(item.qty) || 0)), 0);
                },
                get total() {
                    const discount = this.subtotal * ((parseFloat(this.discountPercentage) || 0) / 100);
                    return this.subtotal - discount;
                }, addSpecialToCart() {
                    if (!this.specialName || !this.specialPrice || this.specialPrice <= 0) {
                        alert('Please enter a service name and price.');
                        return;
                    }
                    this.cart.push({
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

                placePackageOrder() {
                    this.cart = this.packageCart.filter(item => item.qty > 0).map(item => ({
                        id: item.service_type_id,
                        service_type_id: item.service_type_id,
                        name: item.name,
                        base_price: item.base_price,
                        price_type: item.price_type,
                        qty: item.qty
                    }));
                    
                    if(this.cart.length === 0) {
                        alert("Please enter quantities to proceed.");
                        return;
                    }
                    
                    this.paymentMode = 'pay_on_pickup';
                    this.paymentMethod = 'cash';
                    this.showPackageModal = false;
                    this.placeOrder(this.activePackage.id);
                },

                async placeOrder(packageId = null) {
                    if (this.cart.length === 0) return;
                    if (!this.selectedCustomer) {
                        alert('Please select a customer for this order.');
                        return;
                    }
                    if (!this.dueDate) {
                        alert('Please select a Pickup Date for this order.');
                        return;
                    }
                    if (this.isPlacingOrder) return; // prevent double submit
                    this.isPlacingOrder = true;

                    const payload = {
                        customer_id: this.selectedCustomer ? this.selectedCustomer.id : null,
                        new_customer_name: this.selectedCustomer ? null : 'Walk-in',
                        new_customer_phone: this.selectedCustomer ? null : '0000000000',
                        cart: this.cart,
                        payment_mode: this.paymentMode,
                        payment_method: this.paymentMethod,
                        due_date: this.dueDate,
                        discount_percentage: this.discountPercentage || 0,
                        customer_package_id: typeof packageId === 'number' ? packageId : null
                    };

                    try {
                        const res = await fetch("{{ route('pos.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.showCheckoutModal = false;
                            window.dispatchEvent(new CustomEvent('order-placed', {
                                detail: {
                                    orderId: data.order_id,
                                    orderNumber: data.order_number,
                                    customerName: data.customer_name,
                                    total: parseFloat(data.total).toFixed(2),
                                    paymentMode: this.paymentMode,
                                    dueDate: data.due_date,
                                    cart: data.cart
                                }
                            }));
                            printOrderChit(data.order_id);
                            this.cart = [];
                            this.selectedCustomer = null;
                            this.paymentMode = 'pay_on_pickup';
                            this.paymentMethod = 'cash';
                            this.dueDate = '';
                            this.activePackage = null;
                        } else {
                            console.error('Backend Error Response:', data);
                            alert('Order failed: ' + (data.message || data.error || 'Unknown error'));
                        }
                    } catch (err) { 
                        console.error('Fetch exception:', err); 
                        alert('Network error. Please try again.'); 
                    }
                    finally { this.isPlacingOrder = false; }
                }
            };

        // The save-customer-btn listener is mostly handled by Alpine now, but we keep this just in case or we can rely fully on alpine
        // Wait, the new customer modal Alpine scope already handles it. So we can remove the vanilla JS listener to prevent duplication.

        function printSilent(url) {
            let iframe = document.getElementById('print-iframe');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe';
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
            }
            iframe.src = url;
        }

        function printOrderChit(orderId) {
            printSilent('/orders/' + orderId + '/chit');
        }

        function printReceipt(orderId) {
            printSilent('/orders/' + orderId + '/print');
        }
    