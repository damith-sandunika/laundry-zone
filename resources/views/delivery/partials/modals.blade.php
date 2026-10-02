        <!-- ── Cash Received Modal ── -->
        <div x-cloak x-show="showCashModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:400px;overflow:hidden;" @click.away="showCashModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fffbeb;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#d97706;margin:0;">Verify Cash Collection</h3>
                        <p style="font-size:12px;color:#b45309;margin:2px 0 0;font-weight:600;" x-text="'Order ' + (cashData?.orderNumber || '')"></p>
                    </div>
                    <button @click="showCashModal = false" style="background:none;border:none;cursor:pointer;color:#d97706;font-size:20px;">✕</button>
                </div>
                
                <form :action="`/delivery/${cashData?.id}/collect-cash`" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#64748b;">Total Balance</span>
                                <span style="font-size:13px;font-weight:800;color:#334155;" x-text="'LKR ' + Number(cashData?.total || 0).toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#16a34a;">Received Balance</span>
                                <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'- LKR ' + Number(cashData?.paid || 0).toFixed(2)"></span>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:12px;padding-top:12px;border-top:1.5px dashed #cbd5e1;">
                                <span style="font-size:14px;font-weight:900;color:#ea580c;">Pending Payment</span>
                                <span style="font-size:16px;font-weight:900;color:#ea580c;" x-text="'LKR ' + Number(cashData?.pending || 0).toFixed(2)"></span>
                            </div>
                        </div>

                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Cash Count by Rider (Rs.)</label>
                            <input type="number" step="0.01" name="cash_collected" :value="cashData?.pending" required style="width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:18px;font-weight:900;color:#0f172a;outline:none;text-align:center;" onfocus="this.style.borderColor='#f59e0b'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="showCashModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:14px;background:#f59e0b;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                                Confirm Cash
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Reject Modal ── -->
        <div x-cloak x-show="showRejectModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:400px;overflow:hidden;" @click.away="showRejectModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fef2f2;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#dc2626;margin:0;">Reject Order</h3>
                        <p style="font-size:12px;color:#b91c1c;margin:2px 0 0;font-weight:600;">Please provide a reason for rejecting this order.</p>
                    </div>
                    <button @click="showRejectModal = false" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:20px;">✕</button>
                </div>
                
                <form :action="`/delivery/${rejectApptId}/cancel`" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Rejection Reason</label>
                            <input type="text" name="reason" x-model="rejectReason" required placeholder="E.g. We are full for today" style="width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:600;color:#0f172a;outline:none;" onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="showRejectModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:14px;background:#ef4444;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(239,68,68,0.3);">
                                Confirm Reject
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Confirm Return Modal (Assign Cubby) ── -->
        <div x-cloak x-show="showConfirmReturnModal" class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
            <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:400px;overflow:hidden;" @click.away="showConfirmReturnModal = false">
                <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#fef2f2;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#dc2626;margin:0;">Confirm Delivery Return</h3>
                        <p style="font-size:12px;color:#b91c1c;margin:2px 0 0;font-weight:600;">Assign this returned order to a cubby.</p>
                    </div>
                    <button @click="showConfirmReturnModal = false" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:20px;">✕</button>
                </div>
                
                <form :action="`/delivery/${confirmReturnOrderId}/confirm-return`" method="POST" style="margin:0;">
                    @csrf
                    <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                        
                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Assign Cubby</label>
                            <select name="cubby_id" required style="width:100%;padding:14px;border:2px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:600;color:#0f172a;outline:none;" onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e2e8f0'">
                                <option value="">Select a Cubby</option>
                                @foreach($cubbies ?? [] as $cubby)
                                    <option value="{{ $cubby->id }}">{{ $cubby->name }} ({{ ucfirst($cubby->status) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="button" @click="showConfirmReturnModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                            <button type="submit" style="flex:1;padding:14px;background:#ef4444;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(239,68,68,0.3);">
                                Confirm Return
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── Preview Modal ── -->
        <template x-teleport="body">
            <div x-cloak x-show="showPreviewModal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 2147483647; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px);">
                <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:850px;max-height:90vh;overflow-y:auto;display:flex;flex-direction:column;" @click.away="showPreviewModal = false">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;position:sticky;top:0;z-index:10;">
                    <div>
                        <h3 style="font-size:18px;font-weight:900;color:#0f172a;margin:0;">Order Preview</h3>
                        <p style="font-size:12px;color:#64748b;margin:2px 0 0;font-weight:600;" x-text="previewData?.order?.order_number ? 'Order #' + previewData.order.order_number : 'Pending Order'"></p>
                    </div>
                    <button @click="showPreviewModal = false" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:20px;">✕</button>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6 p-6">
                    
                    <!-- Left Column: Customer & Order Details -->
                    <div class="flex-1 flex flex-col gap-5">
                        
                        <!-- Customer Details -->
                        <div style="background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
                            <div style="width:40px;height:40px;border-radius:50%;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:bold;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">Customer Details</div>
                                <div style="font-size:15px;font-weight:800;color:#0f172a;" x-text="previewData?.customer?.name || 'Unknown'"></div>
                                <div style="font-size:13px;font-weight:600;color:#475569;margin-top:2px;" x-text="previewData?.customer?.contact_phone || previewData?.customer?.phone || ''"></div>
                            </div>
                        </div>
                        
                        <!-- Order Summary -->
                        <div style="background:white;padding:16px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                            <h4 style="font-size:13px;font-weight:800;color:#334155;text-transform:uppercase;margin:0 0 16px 0;letter-spacing:0.05em;display:flex;align-items:center;gap:8px;">
                                <i class="fa-solid fa-clipboard-list" style="color:#3b82f6;"></i> Order Summary
                            </h4>
                            
                            <template x-if="!previewData?.order?.items?.length">
                                <div style="display:flex;flex-direction:column;gap:8px;">
                                    <template x-if="previewData?.cart_json?.length">
                                        <template x-for="item in previewData.cart_json">
                                            <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:8px;">
                                                <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#334155;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                                    <div>
                                                        <!-- Category -->
                                                        <template x-if="item.service?.category?.name">
                                                            <div style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;" x-text="item.service.category.name"></div>
                                                        </template>
                                                        <!-- Item Name -->
                                                        <div>
                                                            <span x-text="(item.service?.name || item.name || 'Item')" style="font-size:14px;color:#0f172a;font-weight:700;"></span>
                                                            <template x-if="parseFloat(item.turnaround_extra_charge || 0) > 0">
                                                                <span style="font-size:10px;background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;margin-left:6px;font-weight:800;text-transform:uppercase;">Express</span>
                                                            </template>
                                                            <div style="font-size:11px;color:#64748b;margin-top:2px;" x-text="parseFloat(item.qty || item.quantity || 1) + ' x LKR ' + parseFloat((item.base_price || item.service?.base_price || item.unit_price || 0) + (item.turnaround_extra_charge || 0)).toFixed(2)"></div>
                                                        </div>
                                                    </div>
                                                    <span x-text="'LKR ' + parseFloat((item.qty || item.quantity || 1) * ((item.base_price || item.service?.base_price || item.unit_price || 0) + (item.turnaround_extra_charge || 0))).toFixed(2)" style="font-weight:900;color:#0f172a;font-size:14px;"></span>
                                                </div>
                                                <!-- Extras -->
                                                <template x-if="item.extras && item.extras.length > 0">
                                                    <template x-for="extra in item.extras">
                                                        <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#475569;padding:6px 12px 6px 24px;background:#f8faff;border-radius:8px;border:1px solid #e0e7ff;">
                                                            <div>
                                                                + <span x-text="extra.name || 'Extra'"></span>
                                                                <span x-text="' (x' + parseFloat(extra.pivot?.quantity || extra.qty || 1) + ')'" style="color:#94a3b8;"></span>
                                                            </div>
                                                            <span x-text="'LKR ' + parseFloat((extra.price || extra.unit_price || 0) * (extra.pivot?.quantity || extra.qty || 1)).toFixed(2)" style="font-weight:800;color:#3730a3;"></span>
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </template>
                                    </template>
                                    <template x-if="!previewData?.cart_json?.length && previewData?.item_description">
                                        <div style="font-size:13px;font-weight:600;color:#334155;white-space:pre-line;padding:12px;background:#f8fafc;border-radius:8px;border:1px dashed #cbd5e1;" x-text="previewData.item_description"></div>
                                    </template>
                                </div>
                            </template>
    
                            <template x-if="previewData?.order?.items?.length">
                                <div style="display:flex;flex-direction:column;gap:12px;">
                                    <template x-for="item in (previewData?.order?.items || [])">
                                        <div style="display:flex;flex-direction:column;gap:4px;margin-bottom:8px;">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#334155;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #f1f5f9;">
                                                <div>
                                                    <!-- Category -->
                                                    <template x-if="(item.service_type || item.serviceType)?.category?.name">
                                                        <div style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;" x-text="(item.service_type || item.serviceType).category.name"></div>
                                                    </template>
                                                    <!-- Item Name -->
                                                    <div>
                                                        <span x-text="((item.service_type || item.serviceType)?.name || item.item_description || 'Service')" style="font-size:14px;color:#0f172a;font-weight:700;"></span>
                                                        <template x-if="(item.turnaround_type || item.turnaroundType) && !(item.turnaround_type || item.turnaroundType).is_default">
                                                            <span style="font-size:10px;background:#fef3c7;color:#d97706;padding:2px 6px;border-radius:4px;margin-left:6px;font-weight:800;text-transform:uppercase;" x-text="(item.turnaround_type || item.turnaroundType).name"></span>
                                                        </template>
                                                        <div style="font-size:11px;color:#64748b;margin-top:2px;" x-text="parseFloat(item.quantity) + ' x LKR ' + (parseFloat(item.quantity) > 0 ? (parseFloat(item.total_price) / parseFloat(item.quantity)).toFixed(2) : parseFloat(item.total_price).toFixed(2))"></div>
                                                    </div>
                                                </div>
                                                <span x-text="'LKR ' + parseFloat(item.total_price).toFixed(2)" style="font-weight:900;color:#0f172a;font-size:14px;"></span>
                                            </div>
                                            <!-- Extras -->
                                            <template x-if="item.extras && item.extras.length > 0">
                                                <template x-for="extra in item.extras">
                                                    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#475569;padding:6px 12px 6px 24px;background:#f8faff;border-radius:8px;border:1px solid #e0e7ff;">
                                                        <div>
                                                            + <span x-text="extra.extra ? extra.extra.name : 'Extra'"></span>
                                                            <span x-text="' (x' + parseFloat(extra.quantity || 1) + ')'" style="color:#94a3b8;"></span>
                                                        </div>
                                                        <span x-text="'LKR ' + parseFloat(extra.unit_price * extra.quantity).toFixed(2)" style="font-weight:800;color:#3730a3;"></span>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <div style="margin-top:8px;padding-top:12px;border-top:1px dashed #cbd5e1;display:flex;flex-direction:column;gap:6px;">
                                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#64748b;">
                                            <span>Subtotal</span>
                                            <span x-text="'LKR ' + parseFloat(previewData.order.subtotal || 0).toFixed(2)"></span>
                                        </div>
                                        <template x-if="parseFloat(previewData.order.discount || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#16a34a;">
                                                <span>Discount</span>
                                                <span x-text="'- LKR ' + parseFloat(previewData.order.discount).toFixed(2)"></span>
                                            </div>
                                        </template>
                                        <template x-if="parseFloat(previewData.order.additional || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#dc2626;">
                                                <span>Additional Charges</span>
                                                <span x-text="'+ LKR ' + parseFloat(previewData.order.additional).toFixed(2)"></span>
                                            </div>
                                        </template>
                                        <template x-if="parseFloat(previewData.order.delivery_fee || 0) > 0">
                                            <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:#475569;">
                                                <span>Delivery Fee</span>
                                                <span x-text="'+ LKR ' + parseFloat(previewData.order.delivery_fee).toFixed(2)"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;color:#0f172a;margin-top:4px;padding-top:12px;border-top:1px solid #cbd5e1;">
                                        <span>Total Amount</span>
                                        <span x-text="'LKR ' + parseFloat(previewData.order.total || previewData.order.total_amount).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="previewData?.notes">
                            <div style="background:#fef2f2;padding:16px;border-radius:12px;border:1px solid #fca5a5;">
                                <div style="font-size:11px;font-weight:900;color:#dc2626;text-transform:uppercase;margin-bottom:6px;letter-spacing:0.05em;"><i class="fa-solid fa-circle-exclamation" style="margin-right:6px;"></i>Special Notes</div>
                                <div style="font-size:13px;font-weight:600;color:#991b1b;line-height:1.5;" x-text="previewData.notes"></div>
                            </div>
                        </template>
                    </div>

                    <!-- Right Column: Maps -->
                    <div class="w-full md:w-[320px] flex flex-col gap-4 shrink-0">
                        <!-- Pickup Location Map -->
                        <div style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div style="padding:12px 16px;background:#fff7ed;display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;background:#ffedd5;color:#ea580c;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;"><i class="fa-solid fa-arrow-up"></i></div>
                                <div>
                                    <div style="font-size:10px;font-weight:900;color:#ea580c;text-transform:uppercase;letter-spacing:0.05em;">Pickup Location</div>
                                    <div style="font-size:12px;font-weight:800;color:#431407;line-height:1.2;margin-top:2px;">
                                        Branch: <span x-text="previewData?.branch?.name || 'Main Branch'"></span>
                                    </div>
                                    <template x-if="previewData?.pickup_scheduled_at">
                                        <div style="font-size:11px;font-weight:800;color:#9a3412;margin-top:2px;">
                                            <i class="fa-regular fa-clock" style="margin-right:2px;"></i> <span x-text="new Date(previewData.pickup_scheduled_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div style="padding:10px 16px;font-size:13px;font-weight:600;color:#334155;background:white;" x-text="previewData?.pickup_address || 'Not specified'"></div>

                            <template x-if="parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.pickup_lng)">
                                <div>
                                    <!-- Pickup Route Distance Badge -->
                                    <div id="pickup-route-badge" style="display:none;padding:8px 14px;background:linear-gradient(135deg,#ea580c,#f97316);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                        <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                        <span id="pickup-route-text">Calculating route...</span>
                                    </div>
                                    <!-- Pickup Route Map -->
                                    <div id="pickup-route-map" style="width:100%;height:200px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;"></div>
                                </div>
                            </template>
                            <template x-if="!(parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.pickup_lng))">
                                <div style="width:100%;height:140px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;font-weight:600;">
                                    <i class="fa-solid fa-map-location-dot" style="margin-right:6px;"></i> Map pin not provided
                                </div>
                            </template>
                        </div>
                        
                        <!-- Delivery Location Map -->
                        <div style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div style="padding:12px 16px;background:#f0fdf4;display:flex;align-items:center;gap:10px;">
                                <div style="width:28px;height:28px;background:#dcfce7;color:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;"><i class="fa-solid fa-arrow-down"></i></div>
                                <div>
                                    <div style="font-size:10px;font-weight:900;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;">Delivery Location</div>
                                    <template x-if="previewData?.delivery_scheduled_at">
                                        <div style="font-size:11px;font-weight:800;color:#166534;margin-top:2px;">
                                            <i class="fa-regular fa-clock" style="margin-right:2px;"></i> <span x-text="new Date(previewData.delivery_scheduled_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Case 1: Customer will pick up from branch -->
                            <template x-if="previewData?.delivery_preference === 'pickup_from_branch' || previewData?.delivery_preference === 'pickup_from_store' || previewData?.delivery_preference === 'self_pickup'">
                                <div style="padding:20px 16px;background:white;text-align:center;font-size:13px;font-weight:700;color:#475569;border-top:1px solid #f1f5f9;">
                                    <i class="fa-solid fa-store" style="color:#ea580c;margin-bottom:8px;font-size:22px;display:block;"></i>
                                    Customer will pick up from branch
                                </div>
                            </template>

                            <!-- Case 2: Deliver to same address as pickup -->
                            <template x-if="previewData?.delivery_preference !== 'pickup_from_branch' && previewData?.delivery_preference !== 'pickup_from_store' && previewData?.delivery_preference !== 'self_pickup' && (!previewData?.delivery_address || previewData?.delivery_address === previewData?.pickup_address || (parseFloat(previewData?.delivery_lat) > 0 && parseFloat(previewData?.delivery_lat) === parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.delivery_lng) === parseFloat(previewData?.pickup_lng)))">
                                <div style="padding:20px 16px;background:white;text-align:center;font-size:13px;font-weight:700;color:#475569;border-top:1px solid #f1f5f9;">
                                    <i class="fa-solid fa-arrows-left-right" style="color:#6366f1;margin-bottom:8px;font-size:22px;display:block;"></i>
                                    Same as pickup location
                                </div>
                            </template>

                            <!-- Case 3: Different delivery address with map + route from branch -->
                            <template x-if="previewData?.delivery_preference !== 'pickup_from_branch' && previewData?.delivery_preference !== 'pickup_from_store' && previewData?.delivery_preference !== 'self_pickup' && previewData?.delivery_address && previewData?.delivery_address !== previewData?.pickup_address && !(parseFloat(previewData?.delivery_lat) > 0 && parseFloat(previewData?.delivery_lat) === parseFloat(previewData?.pickup_lat) && parseFloat(previewData?.delivery_lng) === parseFloat(previewData?.pickup_lng))">
                                <div>
                                    <div style="padding:10px 16px;font-size:13px;font-weight:600;color:#334155;background:white;border-top:1px solid #f1f5f9;" x-text="previewData?.delivery_address || 'Not specified'"></div>
                                    <template x-if="parseFloat(previewData?.delivery_lat) && parseFloat(previewData?.delivery_lng)">
                                        <div>
                                            <!-- Route Distance Badge -->
                                            <div id="route-distance-badge" style="display:none;padding:8px 14px;background:linear-gradient(135deg,#1d4ed8,#4f46e5);font-size:12px;font-weight:800;color:white;text-align:center;letter-spacing:0.03em;">
                                                <i class="fa-solid fa-route" style="margin-right:6px;"></i>
                                                <span id="route-distance-text">Calculating route...</span>
                                            </div>
                                            <!-- Route Map -->
                                            <div id="delivery-route-map" style="width:100%;height:200px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;"></div>
                                        </div>
                                    </template>
                                    <template x-if="!(parseFloat(previewData?.delivery_lat) && parseFloat(previewData?.delivery_lng))">
                                        <div style="width:100%;height:140px;background:#f8fafc;z-index:1;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;font-weight:600;">
                                            <i class="fa-solid fa-map-location-dot" style="margin-right:6px;"></i> Map pin not provided
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>