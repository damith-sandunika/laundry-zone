            <!-- ── Reverse Authorization Modal ── -->
            <div x-cloak x-show="showReverseModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-[4px] p-4">
                <div style="background:white;border-radius:20px;box-shadow:0 25px 50px rgba(0,0,0,0.2);width:100%;max-width:400px;overflow:hidden;"
                    @click.away="showReverseModal = false">
                    <div
                        style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
                        <h3 style="font-size:16px;font-weight:900;color:#0f172a;margin:0;">Reverse Bill Authorization
                        </h3>
                        <button @click="showReverseModal = false"
                            style="background:none;border:none;cursor:pointer;color:#94a3b8;">✕</button>
                    </div>
                    <div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
                        <p style="font-size:12.5px;color:#64748b;margin:0;font-weight:500;line-height:1.4;">Enter the
                            admin or super admin password to reverse this bill and set its status back to received.</p>
                        <div>
                            <label
                                style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Admin
                                Password</label>
                            <input type="password" x-model="reversePassword" placeholder="Password"
                                style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;"
                                onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>
                        <div x-show="reverseError" style="font-size:12px;color:#dc2626;font-weight:700;"
                            x-text="reverseError"></div>
                        <div style="display:flex;gap:10px;">
                            <button @click="showReverseModal = false"
                                style="flex:1;padding:11px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                            <button @click="submitReverse"
                                style="flex:1;padding:11px;background:#ef4444;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;">Authorize
                                Reverse</button>
                        </div>
                    </div>
                </div>
            </div>
