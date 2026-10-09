    <!-- Edit Category Modal -->
    <div id="edit-category-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Category</h3>
                </div>
                <button onclick="document.getElementById('edit-category-modal').style.display='none'" style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="edit-category-form" method="POST" enctype="multipart/form-data" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Parent Category (Optional)</label>
                    <select id="edit-cat-parent-id" name="parent_id" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="">None (Main Category)</option>
                        @foreach($mainCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Category Name</label>
                    <input type="text" id="edit-cat-name" name="name" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                @if(config('app.delivery_enabled'))
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Update Image (Optional)</label>
                    <input type="file" name="image" accept="image/*" style="width:100%;font-size:13px;color:#64748b;background:#f8fafc;padding:10px;border-radius:8px;border:1px dashed #cbd5e1;cursor:pointer;">
                </div>
                @endif
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-cat-active" name="is_active" style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                @if(config('app.delivery_enabled'))
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_visible_on_website" id="edit-cat-website-visible" value="1" style="width:16px;height:16px;accent-color:#1d4ed8;cursor:pointer;">
                    <label for="edit-cat-website-visible" style="font-size:12px;font-weight:700;color:#374151;cursor:pointer;">Visible on Website</label>
                </div>
                @endif
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-category-modal').style.display='none'" style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit" style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Update Category</button>
                </div>
            </form>
        </div>
    </div>
