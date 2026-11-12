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
                        discount_type: this.discountType,
                        discount_value: parseFloat(this.discountValue) || 0,
                        discount_percentage: this.discountType === 'percentage' ? (parseFloat(this.discountValue) || 0) : ((this.subtotal > 0 ? (this.calculatedDiscount / this.subtotal) * 100 : 0)),
                        discount_amount: this.calculatedDiscount,
                        advance_amount: this.advanceAmount !== '' ? parseFloat(this.advanceAmount) : null,
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
                },

                openDeliveryModal() {
                    // Dispatch with current POS state (customer + cart)
                    window.dispatchEvent(new CustomEvent('open-delivery-modal', {
                        detail: {
                            customer: this.selectedCustomer,
                            cart: this.cart
                        }
                    }));
                }
            };
        };
</script>
