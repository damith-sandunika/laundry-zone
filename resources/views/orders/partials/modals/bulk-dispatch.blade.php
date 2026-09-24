<div x-cloak x-show="showBulkModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
                    @click.away="showBulkModal = false">
                    <div
                        style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Dispatch Order</h3>
                        <button @click="showBulkModal = false"
                            style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                    </div>
                    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select
                                Workshop</label>
                            <select x-model="bulkWorkshopId"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                <option value="">-- Select Workshop --</option>
                                @foreach($workshops ?? [] as $ws)
                                    <option value="{{ $ws->id }}">{{ $ws->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:flex;gap:10px;">
                            <button @click="showBulkModal = false"
                                style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                            <button @click="submitDispatch"
                                style="flex:1;padding:11px;background:#16a34a;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Dispatch</button>
                        </div>
                    </div>
                </div>
            </div>
