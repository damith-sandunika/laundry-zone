<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="ph-title">Inventory Management</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">Track and manage your laundry supplies and stock levels</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="document.getElementById('bulk-restock-modal').style.display='flex'"
                    class="px-4 py-2 bg-amber-500 text-white text-xs font-semibold rounded hover:bg-amber-600 transition-colors flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Bulk Restock
                </button>
                <button onclick="document.getElementById('add-inventory-modal').style.display='flex'"
                    class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Inventory Item
                </button>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
    <div style="margin-bottom:16px;padding:12px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:13px;font-weight:700;color:#16a34a;">
        ✓ {{ session('success') }}
    </div>
    @endif

    <div style="background:white;border-radius:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,0.04);overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid #f8fafc;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:14px;font-weight:700;color:#0f172a;">Current Stock Levels</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Item Name</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Current Stock</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Low Threshold</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:left;">Location</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;">Status</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Unit</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;">Last Updated</th>
                        <th style="padding:10px 24px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    @php $isLow = $item->current_stock <= ($item->low_stock_threshold ?? 0); @endphp
                    <tr style="border-top:1px solid #f8fafc;background:{{ $isLow ? '#fef2f2' : 'white' }};transition:background 0.1s;"
                        onmouseover="this.style.background='{{ $isLow ? '#fef2f2' : '#f8fafc' }}'" onmouseout="this.style.background='{{ $isLow ? '#fef2f2' : 'white' }}'">
                        <td style="padding:14px 24px;font-size:13px;font-weight:700;color:#0f172a;">{{ $item->name }}</td>
                        <td style="padding:14px 24px;text-align:center;font-size:18px;font-weight:900;color:{{ $isLow ? '#dc2626' : '#0f172a' }};">{{ $item->current_stock }}</td>
                        <td style="padding:14px 24px;text-align:center;font-size:13px;font-weight:500;color:#64748b;">{{ $item->low_stock_threshold ?? 'N/A' }}</td>
                        <td style="padding:14px 24px;font-size:13px;font-weight:500;color:#64748b;">{{ $item->location ?? 'N/A' }}</td>
                        <td style="padding:14px 24px;">
                            @if($isLow)
                                <span style="font-size:11px;font-weight:700;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;padding:4px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;">Low Stock</span>
                            @else
                                <span style="font-size:11px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;padding:4px 10px;border-radius:6px;text-transform:uppercase;letter-spacing:0.06em;">Optimal</span>
                            @endif
                        </td>
                        <td style="padding:14px 24px;text-align:center;font-size:13px;font-weight:500;color:#64748b;">{{ $item->unit }}</td>
                        <td style="padding:14px 24px;font-size:12px;font-weight:500;color:#94a3b8;">{{ $item->updated_at->diffForHumans() }}</td>
                        <td style="padding:14px 24px;text-align:center;">
                            @if(auth()->user()->role === 'admin' || auth()->user()->role === 'super_admin')
                            <form method="POST" action="{{ route('inventory.destroy', $item) }}" onsubmit="return confirm('Delete this inventory item?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:11px;font-weight:700;padding:5px 12px;background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;border-radius:7px;cursor:pointer;transition:background 0.1s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">Delete</button>
                            </form>
                            @else
                                <span style="font-size:11px;color:#94a3b8;font-weight:600;">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="padding:40px;text-align:center;font-size:13px;color:#94a3b8;font-weight:500;">No inventory items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                        style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Unit (e.g. ml, kg)</label>
                        <input type="text" name="unit" required placeholder="e.g. ml"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Initial Stock</label>
                        <input type="number" name="current_stock" required min="0" step="0.01" placeholder="0"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Low Stock Alert Limit</label>
                        <input type="number" name="low_stock_threshold" required min="0" step="0.01" value="10"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Location (Shelf/Rack)</label>
                        <input type="text" name="location" placeholder="e.g. Shelf A2"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" onclick="document.getElementById('add-inventory-modal').style.display='none'"
                        style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#1d4ed8;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">Save Item</button>
                </div>
            </form>
        </div>
    </div>
    <!-- ── Bulk Restock Modal ── -->
    <div id="bulk-restock-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
        <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:600px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;" onclick="event.stopPropagation()">
            <div style="padding:24px 28px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3 style="font-size:17px;font-weight:900;color:#0f172a;margin:0 0 3px;">Bulk Restock Inventory</h3>
                    <p style="font-size:12px;color:#94a3b8;margin:0;font-weight:500;">Enter the amounts you are adding to stock</p>
                </div>
                <button onclick="document.getElementById('bulk-restock-modal').style.display='none'"
                    style="width:32px;height:32px;background:#f1f5f9;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="14" height="14" fill="none" stroke="#64748b" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('inventory.bulkStockIn') }}" style="display:flex;flex-direction:column;min-height:0;flex:1;">
                @csrf
                <div style="flex:1;overflow-y:auto;padding:24px 28px;">
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        @foreach($items as $index => $item)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:#0f172a;">{{ $item->name }}</div>
                                    <div style="font-size:11px;font-weight:600;color:#64748b;">Current: <span style="{{ $item->current_stock <= ($item->low_stock_threshold ?? 0) ? 'color:#dc2626;' : 'color:#16a34a;' }}">{{ $item->current_stock }} {{ $item->unit }}</span></div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <span style="font-size:12px;font-weight:700;color:#475569;">+</span>
                                    <input type="number" name="items[{{ $index }}][quantity]" placeholder="0" min="0" step="0.01"
                                        style="width:80px;padding:8px 10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;font-weight:700;outline:none;text-align:center;">
                                    <span style="font-size:11px;font-weight:600;color:#64748b;width:30px;">{{ $item->unit }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:20px;">
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Notes (Optional)</label>
                        <input type="text" name="notes" placeholder="e.g. Monthly restock from Supplier X"
                            style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;box-sizing:border-box;">
                    </div>
                </div>

                <div style="padding:16px 28px;border-top:1px solid #f1f5f9;background:#f8fafc;display:flex;gap:10px;">
                    <button type="button" onclick="document.getElementById('bulk-restock-modal').style.display='none'"
                        style="flex:1;padding:11px;background:white;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="flex:1;padding:11px;background:#f59e0b;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                        onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">Confirm Restock</button>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>
