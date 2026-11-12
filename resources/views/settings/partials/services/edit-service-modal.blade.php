    <!-- Edit Service Modal -->
    <div id="edit-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Service</h3>
                </div>
                <button onclick="document.getElementById('edit-service-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="edit-service-form" method="POST" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category</label>
                    <select id="edit-category-id" name="category_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">Uncategorized</option>
                        @foreach($mainCategories as $mainCat)
                            <optgroup label="📁 {{ $mainCat->name }}">
                                <option value="{{ $mainCat->id }}">{{ $mainCat->name }} (Main)</option>
                                @foreach($mainCat->children as $subCat)
                                    <option value="{{ $subCat->id }}">&nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $subCat->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" id="edit-name" name="name" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Item Code</label>
                    <input type="text" id="edit-item-code" name="item_code" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price</label>
                        <input type="number" id="edit-price" name="base_price" required min="0" step="0.01" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select id="edit-price-type" name="price_type" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                            <option value="per_sqft">Per SqFt</option>
                                    <option value="per_pair">Per Pair</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-active" name="is_active" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                @if(config('app.delivery_enabled'))
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_visible_on_website" id="edit-website-visible" value="1" style="width:16px;height:16px;accent-color:#1d4ed8;cursor:pointer;">
                    <label for="edit-website-visible" style="font-size:12px;font-weight:700;color:#374151;cursor:pointer;">Visible on Website</label>
                </div>
                @else
                <input type="hidden" name="is_visible_on_website" id="edit-website-visible" value="1">
                @endif
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-service-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Update Service</button>
                </div>
            </form>
        </div>
    </div>

