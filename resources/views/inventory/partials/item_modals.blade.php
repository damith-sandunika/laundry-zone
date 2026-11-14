<!-- ── Add Inventory Modal ── -->
<div id="add-inventory-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:460px;overflow:hidden;" onclick="event.stopPropagation()">
        <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add New Inventory</h3>
                <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Add items like Detergent, Fabric Softener</p>
            </div>
            <button onclick="document.getElementById('add-inventory-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeInventory') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Item Name</label>
                <input type="text" name="name" required placeholder="e.g. Premium Detergent"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Unit (e.g. ml, kg)</label>
                    <input type="text" name="unit" required placeholder="e.g. ml"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Initial Stock</label>
                    <input type="number" name="current_stock" required min="0" step="0.01" placeholder="0"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Low Stock Alert Limit</label>
                    <input type="number" name="low_stock_threshold" required min="0" step="0.01" value="10"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Location (Shelf/Rack)</label>
                    <input type="text" name="location" placeholder="e.g. Shelf A2"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('add-inventory-modal').style.display='none'"
                    style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit Inventory Modal ── -->
<div id="edit-inventory-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:460px;overflow:hidden;" onclick="event.stopPropagation()">
        <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Inventory Item</h3>
                <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Update item details</p>
            </div>
            <button onclick="document.getElementById('edit-inventory-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-inventory-form" method="POST" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PUT')
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Item Name</label>
                <input type="text" name="name" id="edit-item-name" required placeholder="e.g. Premium Detergent"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Unit (e.g. ml, kg)</label>
                    <input type="text" name="unit" id="edit-item-unit" required placeholder="e.g. ml"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Low Stock Alert</label>
                    <input type="number" name="low_stock_threshold" id="edit-item-threshold" required min="0" step="0.01" value="10"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div>
                <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Location (Shelf/Rack)</label>
                <input type="text" name="location" id="edit-item-location" placeholder="e.g. Shelf A2"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('edit-inventory-modal').style.display='none'"
                    style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:1;padding:11px;background:#0ea5e9;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Update Item</button>
            </div>
        </form>
    </div>
</div>

