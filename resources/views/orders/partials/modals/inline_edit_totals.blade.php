<!-- Add Item & Totals Row -->
<div style="display:flex;gap:20px;flex-wrap:wrap;">
    <!-- Add Item -->
    <div style="flex:1;min-width:200px;">
        <label
            style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Add
            Item</label>
        <div style="display:flex;gap:8px;">
            <select x-model="selectedServiceToAdd"
                style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                <option value="">-- Select Service --</option>
                <template x-for="category in allCategories" :key="category.id">
                    <optgroup :label="category.name">
                        <template x-for="service in category.services" :key="service.id">
                            <option :value="service.id"
                                x-text="service.name + ' (' + service.pricing_model + ')'"></option>
                        </template>
                    </optgroup>
                </template>
            </select>
            <button type="button" @click="
                if(selectedServiceToAdd) {
                    let found = null;
                    allCategories.forEach(c => {
                        const s = c.services.find(x => x.id == selectedServiceToAdd);
                        if(s) found = s;
                    });
                    if(found) {
                        editCart.push({
                            service_type_id: found.id,
                            name: found.name,
                            base_price: parseFloat(found.base_price || 0),
                            qty: 1
                        });
                        selectedServiceToAdd = '';
                    }
                }
            "
                style="padding:10px 16px;background:#3b82f6;color:white;font-weight:700;font-size:13px;border:none;border-radius:10px;cursor:pointer;">Add</button>
        </div>
    </div>

    <!-- Totals Summary -->
    <div style="width:220px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:#64748b;font-weight:600;">
            <span>Subtotal:</span>
            <span x-text="editSubtotal.toFixed(2)"></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;color:#64748b;font-weight:600;">
            <span>Discount:</span>
            <input type="number" x-model="editDiscount" min="0" step="0.01"
                style="width:70px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:right;outline:none;">
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:13px;color:#64748b;font-weight:600;">
            <span>Delivery:</span>
            <input type="number" x-model="editDeliveryFee" min="0" step="0.01"
                style="width:70px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:right;outline:none;">
        </div>
        <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:1.5px dashed #cbd5e1;font-size:15px;color:#0f172a;font-weight:900;">
            <span>Total:</span>
            <span x-text="editTotal.toFixed(2)"></span>
        </div>
    </div>
</div>
