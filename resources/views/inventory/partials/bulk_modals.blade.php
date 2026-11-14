<!-- ⚡ Bulk Stock Out Modal ⚡ -->
<div id="bulk-stock-out-modal" style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);">
    <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:600px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;" onclick="event.stopPropagation()">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fff1f2;">
            <div>
                <h3 style="font-size:17px;font-weight:900;color:#e11d48;margin:0 0 3px;">Stock Out Inventory</h3>
                <p style="font-size:12px;color:#be123c;margin:0;font-weight:600;">Deduct quantities from stock.</p>
            </div>
            <button onclick="document.getElementById('bulk-stock-out-modal').style.display='none'"
                style="width:32px;height:32px;background:white;border:none;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg width="14" height="14" fill="none" stroke="#e11d48" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form method="POST" action="{{ route('inventory.bulkStockOut') }}" style="display:flex;flex-direction:column;flex:1;overflow:hidden;">
            @csrf
            <div style="padding:20px 24px;overflow-y:auto;flex:1;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead style="background:#f8fafc;position:sticky;top:-20px;">
                        <tr>
                            <th style="padding:10px;text-align:left;font-weight:700;color:#64748b;">Item Name</th>
                            <th style="padding:10px;text-align:center;font-weight:700;color:#64748b;width:100px;">Current</th>
                            <th style="padding:10px;text-align:right;font-weight:700;color:#64748b;width:120px;">Out Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px;font-weight:600;color:#334155;">
                                {{ $item->name }} <span style="color:#94a3b8;font-size:11px;">({{ $item->unit }})</span>
                                <input type="hidden" name="items[{{$index}}][id]" value="{{ $item->id }}">
                            </td>
                            <td style="padding:10px;text-align:center;font-weight:700;color:#1d4ed8;">
                                {{ number_format($item->current_stock, 2) }}
                            </td>
                            <td style="padding:8px 10px;text-align:right;">
                                <input type="number" name="items[{{$index}}][quantity]" min="0" step="0.01" placeholder="0.00" max="{{ $item->current_stock }}"
                                    style="width:90px;text-align:right;padding:8px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;font-weight:700;outline:none;"
                                    onfocus="this.style.borderColor='#e11d48'" onblur="this.style.borderColor='#cbd5e1'">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top:20px;">
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#475569;margin-bottom:6px;">Notes / Reason (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g. Used for daily operations"
                        style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:13px;outline:none;box-sizing:border-box;">
                </div>
            </div>
            
            <div style="padding:16px 24px;border-top:1px solid #f1f5f9;background:#f8fafc;display:flex;gap:10px;">
                <button type="button" onclick="document.getElementById('bulk-stock-out-modal').style.display='none'"
                    style="flex:1;padding:12px;background:white;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                <button type="submit"
                    style="flex:1;padding:12px;background:#e11d48;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;"
                    onmouseover="this.style.background='#be123c'" onmouseout="this.style.background='#e11d48'">Confirm Stock Out</button>
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

