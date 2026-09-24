            <!-- ── Mark as Done (Cubbet Assign) Modal ── -->
            <div x-cloak x-show="showCubbetModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;"
                    @click.away="showCubbetModal = false">
                    <div
                        style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f0fdf4;">
                        <div>
                            <h3 style="font-size:18px;font-weight:900;color:#16a34a;margin:0;">Mark Order as Done</h3>
                            <p style="font-size:12px;color:#15803d;margin:2px 0 0;font-weight:600;"
                                x-text="'Order #' + cubbetOrder?.order_number"></p>
                        </div>
                        <button @click="showCubbetModal = false"
                            style="background:none;border:none;cursor:pointer;color:#16a34a;font-size:20px;">✕</button>
                    </div>
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        <div style="padding:16px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;">
                            <label
                                style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Assign
                                to a Cubbet</label>
                            <select x-model="selectedCubby"
                                style="width:100%;padding:12px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px;font-weight:700;outline:none;background:white;color:#0f172a;">
                                <option value="" style="color:#94a3b8;">-- Select Empty Cubbet --</option>
                                @foreach($cubbies ?? [] as $cubby)
                                    <option value="{{ $cubby->id }}" style="color:#0f172a;">{{ $cubby->name }}</option>
                                @endforeach
                            </select>
                            <p style="font-size:11px;color:#64748b;margin:8px 0 0;font-weight:600;">The system will send
                                an automated WhatsApp message to <span style="color:#1d4ed8;font-weight:800;"
                                    x-text="cubbetOrder?.customer?.name"></span>.</p>
                        </div>

                        <div style="display:flex;gap:12px;">
                            <button type="button" @click="showCubbetModal = false"
                                style="flex:1;padding:12px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:12px;cursor:pointer;">Cancel</button>
                            <button type="button" @click="markAsDone()"
                                style="flex:1;padding:12px;background:#16a34a;color:white;font-size:14px;font-weight:900;border:none;border-radius:12px;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);">✓
                                Confirm Done</button>
                        </div>
                    </div>
                </div>
            </div>
