<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2 style="font-size:22px;font-weight:900;color:#0f172a;margin:0 0 4px;letter-spacing:-0.02em;">Services</h2>
                <p style="font-size:13px;color:#64748b;margin:0;font-weight:500;">Manage laundry service types, pricing & availability</p>
            </div>
            <button onclick="document.getElementById('add-service-modal').style.display='flex'"
                style="display:flex;align-items:center;gap:8px;padding:10px 18px;background:#1d4ed8;color:white;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;transition:background 0.15s;"
                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Add New Service
            </button>
        </div>
    </x-slot>

    @if(session('success'))
    <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
        ✓ {{ session('success') }}
    </div>
    @endif

    @php
        $colorStyles = [
            'blue' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8'],
            'green' => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d'],
            'purple' => ['bg' => '#fdf4ff', 'border' => '#e9d5ff', 'text' => '#7e22ce'],
            'orange' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#c2410c'],
            'teal' => ['bg' => '#f0fdfa', 'border' => '#99f6e4', 'text' => '#0f766e'],
            'red' => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#b91c1c'],
        ];
        
        $availableColors = ['blue', 'green', 'purple', 'orange', 'teal', 'red'];
    @endphp

    <style>
        .color-radio input[type="radio"] { display: none; }

        .color-radio label {
            display: flex; align-items: center; justify-content: center; width: 30px; height: 30px;
            border-radius: 50%; cursor: pointer; transition: all 0.15s; border: 2px solid transparent;
        }
        .color-radio input[type="radio"]:checked + label {
            transform: scale(1.15); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-color: #0f172a;
        }
    </style>

    <!-- Services Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        @forelse($services as $service)
        @php
            $style = $colorStyles[$service->color ?? 'blue'] ?? $colorStyles['blue'];
        @endphp
        <div style="background:white;border-radius:16px;border:1.5px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;transition:box-shadow 0.15s;"
            onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.04)'">
            <div style="padding:20px 20px 14px;border-bottom:1px solid #f1f5f9;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:12px;background:{{ $style['bg'] }};border:1.5px solid {{ $style['border'] }};color:{{ $style['text'] }};display:flex;align-items:center;justify-content:center;">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.01em;">{{ $service->name }}</h3>
                            <p style="font-size:12px;font-weight:600;color:#64748b;margin:0;text-transform:uppercase;letter-spacing:0.05em;">
                                {{ $service->price_type === 'per_kg' ? 'Per Kilogram' : 'Per Item' }}
                            </p>
                        </div>
                    </div>
                    <span style="font-size:10px;font-weight:800;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;
                        {{ $service->is_active ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;' }}">
                        {{ $service->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div style="padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:22px;font-weight:900;color:{{ $style['text'] }};letter-spacing:-0.02em;">Rs.{{ number_format($service->base_price, 2) }}</span>
                <div style="display:flex;gap:8px;">
                    <button onclick="openEditModal({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->base_price }}, '{{ $service->price_type }}', {{ $service->is_active ? 1 : 0 }}, '{{ $service->icon ?? '👕' }}', '{{ $service->color ?? 'blue' }}')"
                        style="padding:7px 14px;background:#eff6ff;color:#1d4ed8;font-size:12px;font-weight:700;border:1.5px solid #bfdbfe;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                        onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('settings.destroyService', $service) }}" onsubmit="return confirm('Delete this service?')" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit"
                            style="padding:7px 14px;background:#fef2f2;color:#dc2626;font-size:12px;font-weight:700;border:1.5px solid #fecaca;border-radius:8px;cursor:pointer;transition:all 0.15s;"
                            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;padding:60px;text-align:center;background:white;border-radius:16px;border:1.5px solid #e2e8f0;">
            <svg width="48" height="48" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p style="font-size:15px;font-weight:800;color:#0f172a;margin:0 0 6px;">No services yet</p>
            <p style="font-size:13px;color:#94a3b8;font-weight:500;">Click "Add New Service" to get started.</p>
        </div>
        @endforelse
    </div>

    <!-- ── Add Service Modal ── -->
    <div id="add-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Add New Service</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Define a new laundry service type</p>
                </div>
                <button onclick="document.getElementById('add-service-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.storeService') }}" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" name="name" required placeholder="e.g. Wash & Iron"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                


                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Select Color</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        @foreach($availableColors as $i => $color)
                        @php $bg = $colorStyles[$color]['text']; @endphp
                        <div class="color-radio">
                            <input type="radio" name="color" id="add-color-{{ $i }}" value="{{ $color }}" {{ $i === 0 ? 'checked' : '' }}>
                            <label for="add-color-{{ $i }}" style="background:{{ $bg }};"></label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price (Rs.)</label>
                        <input type="number" name="base_price" required min="0" step="0.01" placeholder="0.00"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select name="price_type" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>

                @if($inventoryItems->count() > 0)
                <div style="border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="padding:10px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <p style="margin:0;font-size:11.5px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:0.05em;">📦 Auto-Deduct Inventory</p>
                        <p style="margin:2px 0 0;font-size:11px;color:#94a3b8;font-weight:500;">Set how much stock to deduct per order quantity</p>
                    </div>
                    <div style="padding:10px 14px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($inventoryItems as $invItem)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                            <label style="font-size:12px;font-weight:600;color:#374151;flex:1;">{{ $invItem->name }} <span style="color:#94a3b8;">({{ $invItem->unit }})</span></label>
                            <input type="number" name="inventory[{{ $invItem->id }}]" min="0" step="0.01" placeholder="0"
                                style="width:80px;padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:700;text-align:right;outline:none;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-service-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Edit Service Modal ── -->
    <div id="edit-service-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:480px;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Edit Service</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Update service details</p>
                </div>
                <button onclick="document.getElementById('edit-service-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="edit-service-form" method="POST" style="padding:24px 28px;display:flex;flex-direction:column;gap:16px;max-height:80vh;overflow-y:auto;">
                @csrf @method('PUT')
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Service Name</label>
                    <input type="text" id="edit-name" name="name" required
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                


                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Select Color</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        @foreach($availableColors as $i => $color)
                        @php $bg = $colorStyles[$color]['text']; @endphp
                        <div class="color-radio">
                            <input type="radio" name="color" id="edit-color-{{ $i }}" value="{{ $color }}">
                            <label for="edit-color-{{ $i }}" style="background:{{ $bg }};"></label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Base Price (Rs.)</label>
                        <input type="number" id="edit-price" name="base_price" required min="0" step="0.01"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Price Type</label>
                        <select id="edit-price-type" name="price_type" required
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                            <option value="per_item">Per Item</option>
                            <option value="per_kg">Per KG</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Status</label>
                    <select id="edit-active" name="is_active"
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;box-sizing:border-box;">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div id="edit-inventory-section" style="border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                    <div style="padding:10px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <p style="margin:0;font-size:11.5px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:0.05em;">📦 Auto-Deduct Inventory</p>
                        <p style="margin:2px 0 0;font-size:11px;color:#94a3b8;font-weight:500;">Set how much stock to deduct per order quantity</p>
                    </div>
                    <div style="padding:10px 14px;display:flex;flex-direction:column;gap:8px;">
                        @foreach($inventoryItems as $invItem)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                            <label style="font-size:12px;font-weight:600;color:#374151;flex:1;">{{ $invItem->name }} <span style="color:#94a3b8;">({{ $invItem->unit }})</span></label>
                            <input type="number" id="edit-inv-{{ $invItem->id }}" name="inventory[{{ $invItem->id }}]" min="0" step="0.01" value="0"
                                style="width:80px;padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:700;text-align:right;outline:none;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        @endforeach
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('edit-service-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Update Service</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const allServicesData = @json($services->load('inventoryItems'));

    function openEditModal(id, name, price, priceType, isActive, icon, color) {
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-price-type').value = priceType;
        document.getElementById('edit-active').value = isActive;
        document.getElementById('edit-service-form').action = '/settings/services/' + id;

        // Select the icon
        document.querySelectorAll('input[name="icon"][id^="edit-icon-"]').forEach(radio => {
            if (radio.value === icon) radio.checked = true;
        });

        // Select the color
        document.querySelectorAll('input[name="color"][id^="edit-color-"]').forEach(radio => {
            if (radio.value === color) radio.checked = true;
        });

        // Reset all inventory inputs to 0
        document.querySelectorAll('#edit-service-form input[name^="inventory["]').forEach(input => {
            input.value = 0;
            input.style.borderColor = '#e2e8f0';
            input.style.background = 'white';
        });

        // Pre-fill with current linked inventory
        const service = allServicesData.find(s => s.id === id);
        if (service && service.inventory_items) {
            service.inventory_items.forEach(inv => {
                const input = document.getElementById('edit-inv-' + inv.id);
                if (input) {
                    input.value = inv.pivot.qty_per_use;
                    if (inv.pivot.qty_per_use > 0) {
                        input.style.borderColor = '#3b82f6';
                        input.style.background = '#eff6ff';
                    }
                }
            });
        }

        document.getElementById('edit-service-modal').style.display = 'flex';
    }
    
    document.getElementById('add-service-modal').addEventListener('click', function(e) {
        if(e.target === this) this.style.display = 'none';
    });
    document.getElementById('edit-service-modal').addEventListener('click', function(e) {
        if(e.target === this) this.style.display = 'none';
    });
    </script>
</x-app-layout>
