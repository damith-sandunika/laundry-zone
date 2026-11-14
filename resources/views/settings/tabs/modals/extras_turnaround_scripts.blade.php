<!-- Add Extra Modal -->
<div id="add-extra-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Extra</h3>
            </div>
            <button onclick="document.getElementById('add-extra-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeExtra') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Extra Name</label>
                <input type="text" name="name" required placeholder="e.g. Hangers"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                <select name="price_type" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                    <option value="per_piece">Per Piece</option>
                    <option value="per_kg">Per KG</option>
                    <option value="per_sqft">Per SqFt</option>
                    <option value="per_pair">Per Pair</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price</label>
                <input type="number" step="0.01" name="price" required placeholder="e.g. 50.00"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('add-extra-modal').style.display='none'"
                    style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:12px;font-size:13px;font-weight:700;color:#475569;cursor:pointer;">Cancel</button>
                <button type="submit" style="flex:2;padding:12px;background:#2563eb;border:none;border-radius:12px;font-size:13px;font-weight:700;color:white;cursor:pointer;">Add Extra</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Turnaround Type Modal -->
<div id="add-turnaround-type-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Turnaround Type</h3>
            </div>
            <button onclick="document.getElementById('add-turnaround-type-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeTurnaroundType') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Type Name</label>
                <input type="text" name="name" required placeholder="e.g. Express"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Markup Type</label>
                <select name="markup_type" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                    <option value="percentage">Percentage (%)</option>
                    <option value="flat">Flat Amount (Rs)</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Markup Value</label>
                <input type="number" step="0.01" name="markup_value" required placeholder="e.g. 50"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_default" value="1"
                    style="width:16px;height:16px;border-radius:4px;border-color:#e2e8f0;accent-color:#2563eb;">
                <label style="font-size:13px;font-weight:600;color:#475569;">Set as Default</label>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('add-turnaround-type-modal').style.display='none'"
                    style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:12px;font-size:13px;font-weight:700;color:#475569;cursor:pointer;">Cancel</button>
                <button type="submit" style="flex:2;padding:12px;background:#2563eb;border:none;border-radius:12px;font-size:13px;font-weight:700;color:white;cursor:pointer;">Add Type</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, name, price, priceType, isActive, icon, color, categoryId, isVisibleOnWebsite, itemCode) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-item-code').value = itemCode || '';
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-price-type').value = priceType;
        document.getElementById('edit-active').value = isActive;
        document.getElementById('edit-category-id').value = categoryId || '';
        window.dispatchEvent(new CustomEvent('update-edit-category', { detail: categoryId || '' }));
        document.getElementById('edit-website-visible').checked = isVisibleOnWebsite == 1;
        document.getElementById('edit-service-form').action = '/settings/services/' + id;

        document.querySelectorAll('input[name="color"][id^="edit-color-"]').forEach(radio => {
            if (radio.value === color) radio.checked = true;
        });

        document.getElementById('edit-service-modal').style.display = 'flex';
    }

    function openEditCategoryModal(id, name, isActive, parentId) {
        document.getElementById('edit-category-name').value = name;
        document.getElementById('edit-category-active').value = isActive;
        document.getElementById('edit-category-parent').value = parentId || '';
        document.getElementById('edit-category-form').action = '/settings/service-categories/' + id;
        document.getElementById('edit-category-modal').style.display = 'flex';
    }

    document.getElementById('add-service-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('edit-service-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('add-category-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('edit-category-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('add-delivery-range-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
    document.getElementById('add-pickup-time-modal')?.addEventListener('click', function (e) {
        if (e.target === this) this.style.display = 'none';
    });
</script>

