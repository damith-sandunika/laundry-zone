<!-- Add Service Modal -->
<div x-show="serviceModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
        <div x-show="serviceModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity"
            @click="serviceModalOpen = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div x-show="serviceModalOpen" x-transition
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form action="{{ route('settings.storeService') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Laundry Service</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Service Name</label>
                            <input type="text" name="name" required
                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Base Price</label>
                                <input type="number" step="0.01" name="base_price" required
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pricing Unit</label>
                                <select name="price_type"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="per_kg">Per KG</option>
                                    <option value="per_item">Per Item</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <h4 class="text-xs font-bold text-gray-800 uppercase mb-2">Link Supplies to
                                Auto-Deduct</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                @foreach($inventoryItems as $item)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">{{ $item->name }}
                                            ({{ $item->unit }})</span>
                                        <input type="number" step="any" name="inventory[{{ $item->id }}]"
                                            placeholder="0"
                                            class="w-20 text-xs border-gray-300 rounded p-1 text-right">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Add Service
                    </button>
                    <button type="button" @click="serviceModalOpen = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Cubby Modal -->
<div x-show="cubbyModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
        <div x-show="cubbyModalOpen" x-transition.opacity class="fixed inset-0 transition-opacity"
            @click="cubbyModalOpen = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div x-show="cubbyModalOpen" x-transition
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form action="{{ route('settings.storeCubby') }}" method="POST">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Stock Location (Cubby/Rack)
                    </h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Name (e.g. Cubby 15)</label>
                        <input type="text" name="name" required
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" @click="cubbyModalOpen = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Add Delivery Range Modal ── -->
<div id="add-delivery-range-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div
            style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Delivery Range</h3>
            </div>
            <button onclick="document.getElementById('add-delivery-range-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeDeliveryRange') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label
                        style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Min
                        Distance (km)</label>
                    <input type="number" name="min_distance_km" required step="0.01" min="0" value="0"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label
                        style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Max
                        Distance (km)</label>
                    <input type="number" name="max_distance_km" required step="0.01" min="0.1"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Delivery
                    Cost (Rs.)</label>
                <input type="number" name="cost" required step="0.01" min="0"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button"
                    onclick="document.getElementById('add-delivery-range-modal').style.display='none'"
                    style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                    onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save
                    Range</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Add Pickup Time Modal ── -->
<div id="add-pickup-time-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div
            style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Pickup Time Period</h3>
            </div>
            <button onclick="document.getElementById('add-pickup-time-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storePickupTimePeriod') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Label
                    (e.g. Morning)</label>
                <input type="text" name="label" required
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label
                        style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Start
                        Time</label>
                    <input type="time" name="start_time" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label
                        style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">End
                        Time</label>
                    <input type="time" name="end_time" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button"
                    onclick="document.getElementById('add-pickup-time-modal').style.display='none'"
                    style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                    onmouseover="this.style.background='#1e40af'"
                    onmouseout="this.style.background='#1d4ed8'">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Extra Modal -->
<div id="add-extra-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div
            style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Extra</h3>
            </div>
            <button onclick="document.getElementById('add-extra-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeExtra') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Extra
                    Name</label>
                <input type="text" name="name" required placeholder="e.g. Hangers"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price
                    Type</label>
                <select name="price_type"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                    <option value="per_piece">Per Piece</option>
                    <option value="per_kg">Per KG</option>
                    <option value="per_sqft">Per SqFt</option>
                </select>
            </div>
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price</label>
                <input type="number" step="0.01" name="price" required placeholder="e.g. 50.00"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" onclick="document.getElementById('add-extra-modal').style.display='none'"
                    style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:12px;font-size:13px;font-weight:700;color:#475569;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:2;padding:12px;background:#2563eb;border:none;border-radius:12px;font-size:13px;font-weight:700;color:white;cursor:pointer;box-shadow:0 4px 14px rgba(37,99,235,0.2);">Add
                    Extra</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Turnaround Type Modal -->
<div id="add-turnaround-type-modal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
        onclick="event.stopPropagation()">
        <div
            style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add Turnaround Type</h3>
            </div>
            <button onclick="document.getElementById('add-turnaround-type-modal').style.display='none'"
                style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('settings.storeTurnaroundType') }}"
            style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;">
            @csrf
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Type
                    Name</label>
                <input type="text" name="name" required placeholder="e.g. Express"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Markup
                    Type</label>
                <select name="markup_type"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                    <option value="percentage">Percentage (%)</option>
                    <option value="flat">Flat Amount (Rs)</option>
                </select>
            </div>
            <div>
                <label
                    style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Markup
                    Value</label>
                <input type="number" step="0.01" name="markup_value" required placeholder="e.g. 50"
                    style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_default" value="1"
                    style="width:16px;height:16px;border-radius:4px;border-color:#e2e8f0;accent-color:#2563eb;">
                <label style="font-size:13px;font-weight:600;color:#475569;">Set as Default</label>
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button"
                    onclick="document.getElementById('add-turnaround-type-modal').style.display='none'"
                    style="flex:1;padding:12px;background:#f1f5f9;border:none;border-radius:12px;font-size:13px;font-weight:700;color:#475569;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:2;padding:12px;background:#2563eb;border:none;border-radius:12px;font-size:13px;font-weight:700;color:white;cursor:pointer;box-shadow:0 4px 14px rgba(37,99,235,0.2);">Add
                    Type</button>
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

        // Select the color
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
