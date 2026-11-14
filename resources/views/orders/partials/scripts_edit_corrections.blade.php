    openEdit(order) {
        this.editOrderData = order;
        this.editCart = (order.items || []).map(item => ({
            service_type_id: item.service_type_id,
            name: item.service_type ? item.service_type.name : (item.item_description || 'Service'),
            base_price: parseFloat(item.unit_price || 0),
            qty: parseFloat(item.quantity || 1),
        }));
        if (this.editCart.length === 0 && order.delivery_appointment && order.delivery_appointment.cart_json) {
            try {
                const parsed = JSON.parse(order.delivery_appointment.cart_json);
                this.editCart = parsed.map(i => ({
                    service_type_id: i.service_type_id || i.id || null,
                    name: i.name || 'Service',
                    base_price: parseFloat(i.base_price || 0),
                    qty: parseFloat(i.qty || 1),
                }));
            } catch (e) { }
        }
        this.editDiscount = parseFloat(order.discount || 0);
        this.editDeliveryFee = parseFloat(order.delivery_fee || 0);
        this.editReason = '';
        this.editErrorMsg = '';
        this.editSuccessMsg = '';
        this.showEditModal = true;
    },

    get editSubtotal() {
        return this.editCart.reduce((s, i) => s + parseFloat(i.base_price || 0) * parseFloat(i.qty || 1), 0);
    },

    get editTotal() {
        const sub = this.editSubtotal;
        const disc = this.editDiscount;
        return Math.max(0, sub - disc + this.editDeliveryFee);
    },

    async submitEditCorrection() {
        if (!this.editReason.trim()) { this.editErrorMsg = 'Please enter a reason for this correction.'; return; }
        if (this.editCart.length === 0) { this.editErrorMsg = 'Cart cannot be empty.'; return; }
        this.editIsSaving = true;
        this.editErrorMsg = '';
        this.editSuccessMsg = '';
        try {
            const res = await fetch('/orders/' + this.editOrderData.id + '/corrections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({
                    type: 'items_change',
                    reason: this.editReason,
                    new_data: {
                        cart: this.editCart,
                        discount_percentage: this.editDiscount,
                        delivery_fee: this.editDeliveryFee,
                    }
                })
            });
            const data = await res.json();
            if (data.success) {
                this.editSuccessMsg = data.message || 'Correction submitted!';
                setTimeout(() => { this.showEditModal = false; window.location.reload(); }, 1500);
            } else {
                this.editErrorMsg = data.message || 'Failed to submit correction.';
            }
        } catch (e) { this.editErrorMsg = 'Connection error.'; }
        this.editIsSaving = false;
    },
