        <!-- ── Inline Edit / Correction Modal ── -->
        <div x-cloak x-show="showEditModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:600px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden;" @click.away="if(!editIsSaving) showEditModal = false">
                
                <!-- Header -->
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-pen-to-square" style="color:#3b82f6;"></i>
                            Edit Order / Correction Request
                        </h3>
                        <p style="font-size:12px;color:#64748b;margin:4px 0 0 0;font-weight:500;">
                            Order: <span style="font-weight:700;color:#0f172a;" x-text="editOrderData?.order_number"></span>
                        </p>
                    </div>
                    <button @click="showEditModal = false" :disabled="editIsSaving" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:20px;padding:4px;border-radius:8px;transition:all 0.15s;" onmouseover="this.style.background='#e2e8f0';this.style.color='#475569';" onmouseout="this.style.background='none';this.style.color='#94a3b8';">✕</button>
                </div>

                <!-- Body (Scrollable) -->
                <div style="padding:24px;overflow-y:auto;display:flex;flex-direction:column;gap:20px;flex:1;">
                    
                    <!-- Alert Messages -->
                    <template x-if="editErrorMsg">
                        <div style="padding:12px 16px;background:#fef2f2;border:1.5px solid #fecaca;border-radius:10px;color:#dc2626;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span x-text="editErrorMsg"></span>
                        </div>
                    </template>
                    <template x-if="editSuccessMsg">
                        <div style="padding:12px 16px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;color:#16a34a;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;">
                            <i class="fa-solid fa-circle-check"></i>
                            <span x-text="editSuccessMsg"></span>
                        </div>
                    </template>

                    <!-- Info Alert for Staff -->
                    @if(auth()->user()->role === 'staff')
                    <div style="padding:12px 16px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;color:#1e3a8a;font-size:12.5px;font-weight:500;line-height:1.5;">
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <i class="fa-solid fa-info-circle" style="margin-top:2px;"></i>
                            <div>
                                <strong style="display:block;margin-bottom:2px;">Approval Required</strong>
                                Since you are a staff member, submitting these changes will create a <b>Pending Correction Request</b>. An admin must approve it before it takes effect.
                            </div>
                        </div>
                    </div>
                    @else
                    <div style="padding:12px 16px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;color:#166534;font-size:12.5px;font-weight:500;line-height:1.5;">
                        <div style="display:flex;gap:8px;align-items:flex-start;">
                            <i class="fa-solid fa-check-circle" style="margin-top:2px;"></i>
                            <div>
                                <strong style="display:block;margin-bottom:2px;">Auto-Approval Active</strong>
                                As an Admin, your corrections will be applied and approved immediately.
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Cart Items -->
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Order Items</label>
                        <div style="border:1.5px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-transform:uppercase;font-size:11px;color:#64748b;letter-spacing:0.05em;">
                                        <th style="padding:10px 12px;text-align:left;">Item</th>
                                        <th style="padding:10px 12px;text-align:right;width:80px;">Price</th>
                                        <th style="padding:10px 12px;text-align:center;width:80px;">Qty</th>
                                        <th style="padding:10px 12px;text-align:right;width:80px;">Total</th>
                                        <th style="padding:10px 12px;text-align:center;width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in editCart" :key="index">
                                        <tr style="border-bottom:1px solid #f1f5f9;">
                                            <td style="padding:10px 12px;font-weight:600;color:#1e293b;" x-text="item.name"></td>
                                            <td style="padding:8px 12px;text-align:right;">
                                                <input type="number" x-model="item.base_price" min="0" step="0.01" style="width:60px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:right;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                                            </td>
                                            <td style="padding:8px 12px;text-align:center;">
                                                <input type="number" x-model="item.qty" min="0.5" step="0.5" style="width:50px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:center;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#cbd5e1'">
                                            </td>
                                            <td style="padding:10px 12px;text-align:right;font-weight:700;color:#0f172a;" x-text="(parseFloat(item.base_price||0) * parseFloat(item.qty||0)).toFixed(2)"></td>
                                            <td style="padding:8px 12px;text-align:center;">
                                                <button type="button" @click="editCart.splice(index, 1)" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:4px;border-radius:4px;" title="Remove Item"><i class="fa-solid fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="editCart.length === 0">
                                        <td colspan="5" style="padding:20px;text-align:center;color:#94a3b8;font-size:12px;font-weight:500;font-style:italic;">No items in cart</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Add Item & Totals Row -->
                    <div style="display:flex;gap:20px;flex-wrap:wrap;">
                        <!-- Add Item -->
                        <div style="flex:1;min-width:200px;">
                            <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Add Item</label>
                            <div style="display:flex;gap:8px;">
                                <select x-model="selectedServiceToAdd" style="flex:1;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                    <option value="">-- Select Service --</option>
                                    <template x-for="category in allCategories" :key="category.id">
                                        <optgroup :label="category.name">
                                            <template x-for="service in category.services" :key="service.id">
                                                <option :value="service.id" x-text="service.name + ' (' + service.pricing_model + ')'"></option>
                                            </template>
                                        </optgroup>
                                    </template>
                                </select>
                                <button type="button" @click="
                                    if(selectedServiceToAdd) {
                                        let found = null;
                                        allCategories.forEach(c => {
                                            const s = c.services.find(x => x.id == selectedServiceToAdd);
                                            if(s) found = s;
                                        });
                                        if(found) {
                                            editCart.push({
                                                service_type_id: found.id,
                                                name: found.name,
                                                base_price: parseFloat(found.base_price || 0),
                                                qty: 1
                                            });
                                            selectedServiceToAdd = '';
                                        }
                                    }
                                " style="padding:10px 16px;background:#3b82f6;color:white;font-weight:700;font-size:13px;border:none;border-radius:10px;cursor:pointer;">Add</button>
                            </div>
                        </div>

                        <!-- Totals Summary -->
                        <div style="width:220px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:#64748b;font-weight:600;">
                                <span>Subtotal:</span>
                                <span x-text="editSubtotal.toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:13px;color:#64748b;font-weight:600;">
                                <span>Discount:</span>
                                <input type="number" x-model="editDiscount" min="0" step="0.01" style="width:70px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:right;outline:none;">
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;font-size:13px;color:#64748b;font-weight:600;">
                                <span>Delivery:</span>
                                <input type="number" x-model="editDeliveryFee" min="0" step="0.01" style="width:70px;padding:4px 8px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;text-align:right;outline:none;">
                            </div>
                            <div style="display:flex;justify-content:space-between;padding-top:12px;border-top:1.5px dashed #cbd5e1;font-size:15px;color:#0f172a;font-weight:900;">
                                <span>Total:</span>
                                <span x-text="editTotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Correction Reason -->
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">Reason for Correction <span style="color:#ef4444;">*</span></label>
                        <textarea x-model="editReason" placeholder="Briefly explain why this order is being edited (e.g. Added missing shirt, wrong price quoted)..." rows="3" style="width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-family:inherit;outline:none;resize:vertical;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                    </div>

                </div>

                <!-- Footer Actions -->
                <div style="padding:20px 24px;border-top:1px solid #f1f5f9;background:#f8fafc;display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" @click="showEditModal = false" :disabled="editIsSaving" style="padding:12px 20px;background:white;color:#64748b;font-size:13px;font-weight:700;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitEditCorrection" :disabled="editIsSaving" style="padding:12px 24px;background:#3b82f6;color:white;font-size:13px;font-weight:800;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(59,130,246,0.25);">
                        <template x-if="editIsSaving">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                        </template>
                        <template x-if="!editIsSaving">
                            <i class="fa-solid fa-paper-plane"></i>
                        </template>
                        <span x-text="editIsSaving ? 'Submitting...' : 'Submit Request'"></span>
                    </button>
                </div>

            </div>
        </div>

