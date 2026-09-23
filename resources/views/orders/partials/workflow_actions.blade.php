@php
    $dispatch = \App\Models\WorkshopDispatch::with(['order.paymentTransactions', 'order.cubby.cubby', 'branch'])->where('order_id', $order->id)->first();
    $hasPayment = $order->paymentTransactions()->whereIn('status', ['completed', 'pending'])->exists() || $order->receipt_path || ($order->deliveryAppointment && $order->deliveryAppointment->receipt_path);
    
    // Convert to JSON for Alpine
    $dispatchJson = $dispatch ? $dispatch->toJson() : 'null';
    $riders = \App\Models\User::where('role', 'rider')->get();
    $cubbies = \App\Models\Cubby::where('branch_id', auth()->user()->branch_id)->where('is_active', true)->get();
@endphp

<div x-data="orderWorkflowManager()" x-init="initData({{ $dispatchJson }})">
    <!-- Status Banner -->
    <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;
        {{ $order->status === 'received' ? 'background:#fffbeb;color:#d97706;border:1.5px solid #fde68a;' :
    ($order->status === 'done' ? 'background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;' :
        ($order->status === 'delivered' ? 'background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;' :
            'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;')) }}">

        <div style="display:flex;align-items:center;gap:10px;">
            <span>Order Status:</span>
            <span style="font-size:16px;font-weight:900;">
                <template x-if="dispatch">
                    <span x-text="dispatch.status.replace(/_/g, ' ')"></span>
                </template>
                <template x-if="!dispatch">
                    <span>{{ $order->status }}</span>
                </template>
            </span>
        </div>

        <!-- Workflow Actions -->
        <template x-if="dispatch">
            <div style="display:inline-flex;gap:6px;align-items:center;">
                <!-- Dispatched to Workshop -->
                <template x-if="dispatch.status === 'dispatched_to_workshop'">
                    <button @click="workshopAction('receive')"
                        style="padding:8px 16px;background:#eab308;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        Receive at Workshop
                    </button>
                </template>

                <!-- Received at Workshop (To Wash) -->
                <template x-if="dispatch.status === 'received_at_workshop'">
                    <div style="display:flex;gap:6px;">
                        <button @click="openFinalizePage()"
                            style="padding:8px 16px;background:#3b82f6;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.3);transition:transform 0.1s;"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            Finalize Bill
                        </button>
                        <button @click="workshopAction('start-processing')"
                            style="padding:8px 16px;background:#eab308;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(234,179,8,0.3);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-play"></i> Start Washing
                        </button>
                    </div>
                </template>

                <!-- Processing (Washing) -->
                <template x-if="dispatch.status === 'processing'">
                    <button @click="showDoneModal = true"
                        style="padding:8px 16px;background:#10b981;color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.3);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <i class="fa-solid fa-check"></i> Mark Done
                    </button>
                </template>

                <!-- Completed at Workshop -->
                <template x-if="dispatch.status === 'completed_at_workshop'">
                    <div style="display:flex;gap:6px;">
                        <template x-if="dispatch.order.branch_id == {{ auth()->user()->branch_id ?? 'null' }}">
                            <div style="display:flex;gap:6px;">
                                <template x-if="dispatch.order.is_delivery_order">
                                    <button @click="showDeliveryModal = true"
                                        style="padding:8px 16px;background:linear-gradient(to right,#3b82f6,#2563eb);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(59,130,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fa-solid fa-truck"></i> Assign Delivery
                                    </button>
                                </template>
                                <template x-if="!dispatch.order.is_delivery_order">
                                    <button @click="showHandoverModal = true"
                                        style="padding:8px 16px;background:linear-gradient(to right,#10b981,#059669);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(16,185,129,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                        onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        <i class="fa-solid fa-box-open"></i> Handover to Cust
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="dispatch.order.branch_id != {{ auth()->user()->branch_id ?? 'null' }}">
                            <button @click="workshopAction('return')"
                                style="padding:8px 16px;background:linear-gradient(to right,#8b5cf6,#6d28d9);color:white;border:none;border-radius:8px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 2px 4px rgba(139,92,246,0.35);transition:transform 0.1s;display:flex;align-items:center;gap:6px;"
                                onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Send to Branch
                            </button>
                        </template>
                    </div>
                </template>
                
                <template x-if="dispatch.status === 'returned_to_branch'">
                    <span style="font-size:11px;color:#94a3b8;font-weight:600;">Awaiting Branch...</span>
                </template>
            </div>
        </template>
    </div>

    <!-- Modals -->

    <!-- ── Dispatch Delivery Modal ── -->
    <div x-cloak x-show="showDeliveryModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:440px;overflow:hidden;" @click.away="showDeliveryModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:#eff6ff;">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#1d4ed8;margin:0;">Dispatch Delivery</h3>
                    <p style="font-size:12px;color:#3b82f6;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showDeliveryModal = false" style="background:none;border:none;cursor:pointer;color:#1d4ed8;font-size:20px;">✕</button>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:10px;text-transform:uppercase;letter-spacing:0.05em;">Delivery Method</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="deliveryMethod = 'rider'" :class="deliveryMethod === 'rider' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600'" class="p-3 border rounded-xl font-bold text-sm transition-all text-center">
                            Rider
                        </button>
                        <button @click="deliveryMethod = 'pickme_flash'" 
                            :class="deliveryMethod === 'pickme_flash' ? 'bg-blue-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' : 'bg-white border-slate-200 text-slate-600 hover:border-blue-300'"
                            class="p-3 border rounded-xl font-bold text-sm transition-all text-center relative group">
                            PickMe
                        </button>
                    </div>
                </div>

                <div x-show="deliveryMethod === 'rider'">
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Select Rider</label>
                    <select x-model="deliveryRiderId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">-- Choose Rider --</option>
                        @foreach($riders as $rider)
                            <option value="{{ $rider->id }}">{{ $rider->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="deliveryMethod === 'pickme_flash'">
                    <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">PickMe Cost (Rs.)</label>
                    <input type="number" x-model="deliveryPickmeCost" placeholder="e.g. 500" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    <p style="font-size:11px;color:#64748b;margin-top:6px;font-weight:600;">This will automatically be added as a Workshop Expense.</p>
                    
                    <div style="margin-top:12px;">
                        <div x-show="dispatch && dispatch.order && (dispatch.order.total - (dispatch.order.paid_amount || 0) > 0)" style="margin-bottom:8px;padding:8px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;font-size:12px;font-weight:700;color:#c2410c;">
                            ⚠️ Balance to Collect: LKR <span x-text="dispatch && dispatch.order ? (dispatch.order.total - (dispatch.order.paid_amount || 0)).toFixed(2) : '0.00'"></span>
                        </div>
                        <label style="display:block;font-size:11.5px;font-weight:700;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Payment Method</label>
                        <select x-model="deliveryPaymentMethod" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-weight:700;outline:none;">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit">Credit (Pending Payment)</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="button" @click="showDeliveryModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitDelivery()" :disabled="isDispatchingDelivery" style="flex:1;padding:14px;background:#3b82f6;color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(59,130,246,0.3);">
                        <span x-text="isDispatchingDelivery ? 'Dispatching...' : 'Dispatch Now'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Mark Done Modal ── -->
    <div x-cloak x-show="showDoneModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showDoneModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">✅ Mark as Done</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showDoneModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                <!-- Cubby Selection -->
                <div>
                    <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Assign Storage Location (Cubby)</label>
                    <select x-model="doneCubbyId" style="width:100%;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;font-weight:600;outline:none;background:white;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                        <option value="">-- No cubby (skip) --</option>
                        @foreach($cubbies as $cubby)
                            <option value="{{ $cubby->id }}">
                                {{ $cubby->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action buttons -->
                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showDoneModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitDone()" :disabled="isDoneSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isDoneSubmitting ? 'Processing...' : 'Confirm Done'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Handover Modal ── -->
    <div x-cloak x-show="showHandoverModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-[6px] p-4">
        <div style="background:white;border-radius:24px;box-shadow:0 25px 50px rgba(0,0,0,0.25);width:100%;max-width:460px;overflow:hidden;" @click.away="showHandoverModal = false">
            <div style="padding:24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                <div>
                    <h3 style="font-size:18px;font-weight:900;color:#15803d;margin:0;">🤝 Hand Over & Complete</h3>
                    <p style="font-size:12px;color:#16a34a;margin:2px 0 0;font-weight:600;" x-text="'Order #' + (dispatch?.order?.order_number || '')"></p>
                </div>
                <button @click="showHandoverModal = false" style="background:none;border:none;cursor:pointer;color:#15803d;font-size:20px;">✕</button>
            </div>

            <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
                <template x-if="dispatch?.order?.cubby?.cubby">
                    <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;background:#f59e0b;color:white;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;">
                            📦
                        </div>
                        <div>
                            <span style="font-size:11px;font-weight:800;color:#b45309;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Assigned Storage Location</span>
                            <span style="font-size:16px;font-weight:900;color:#92400e;" x-text="dispatch.order.cubby.cubby.name"></span>
                        </div>
                    </div>
                </template>

                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#64748b;">Total Amount</span>
                        <span style="font-size:13px;font-weight:800;color:#0f172a;" x-text="'Rs. ' + parseFloat(dispatch?.order?.total || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:700;color:#16a34a;">Already Paid</span>
                        <span style="font-size:13px;font-weight:800;color:#16a34a;" x-text="'Rs. ' + parseFloat(dispatch?.order?.paid_amount || 0).toFixed(2)"></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding-top:10px;border-top:1.5px dashed #cbd5e1;">
                        <span style="font-size:14px;font-weight:900;color:#dc2626;">Balance Due</span>
                        <span style="font-size:15px;font-weight:900;color:#dc2626;" x-text="'Rs. ' + Math.max(0, parseFloat(dispatch?.order?.total||0) - parseFloat(dispatch?.order?.paid_amount||0)).toFixed(2)"></span>
                    </div>
                </div>

                <template x-if="dispatch && (parseFloat(dispatch?.order?.total||0) - parseFloat(dispatch?.order?.paid_amount||0)) > 0">
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Collect Payment Now</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                            <div>
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Method</label>
                                <select x-model="handoverPaymentMethod" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;background:white;">
                                    <option value="cash">💵 Cash</option>
                                    <option value="bank">🏦 Bank Transfer</option>
                                    <option value="card">💳 Card</option>
                                    <option value="credit">⏳ Credit</option>
                                </select>
                            </div>
                            <div x-show="handoverPaymentMethod !== 'credit'">
                                <label style="font-size:11px;font-weight:700;color:#64748b;display:block;margin-bottom:4px;">Amount (Rs.)</label>
                                <input type="number" step="0.01" x-model="handoverAmountPaid" style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:600;outline:none;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='#e2e8f0'">
                            </div>
                        </div>
                    </div>
                </template>
                
                <div style="display:flex;align-items:center;gap:10px;margin-top:5px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;">
                    <input type="checkbox" id="printReceiptHandover2" x-model="handoverPrintReceipt" style="width:18px;height:18px;accent-color:#10b981;cursor:pointer;">
                    <label for="printReceiptHandover2" style="font-size:13px;font-weight:700;color:#334155;cursor:pointer;user-select:none;">Print Receipt & Hand Over</label>
                </div>

                <div style="display:flex;gap:12px;margin-top:4px;">
                    <button type="button" @click="showHandoverModal = false" style="flex:1;padding:14px;background:white;color:#64748b;font-size:14px;font-weight:800;border:2px solid #e2e8f0;border-radius:14px;cursor:pointer;">Cancel</button>
                    <button type="button" @click="submitHandover()" :disabled="isHandoverSubmitting" style="flex:1;padding:14px;background:linear-gradient(135deg,#10b981,#059669);color:white;font-size:14px;font-weight:900;border:none;border-radius:14px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                        <span x-text="isHandoverSubmitting ? 'Processing...' : 'Confirm Handover'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script logic -->
    <script>
        function orderWorkflowManager() {
            return {
                dispatch: null,

                showDoneModal: false,
                doneCubbyId: '',
                isDoneSubmitting: false,

                showDeliveryModal: false,
                deliveryMethod: 'rider',
                deliveryRiderId: '',
                deliveryPickmeCost: '',
                deliveryPaymentMethod: 'cash',
                isDispatchingDelivery: false,

                showHandoverModal: false,
                handoverPaymentMethod: 'cash',
                handoverAmountPaid: '',
                handoverPrintReceipt: true,
                isHandoverSubmitting: false,

                initData(dispatchObj) {
                    this.dispatch = dispatchObj;
                    if (this.dispatch && this.dispatch.order) {
                        this.handoverAmountPaid = Math.max(0, (parseFloat(this.dispatch.order.total||0) - parseFloat(this.dispatch.order.paid_amount||0))).toFixed(2);
                    }
                },

                openFinalizePage() {
                    window.location.href = '/workshop/orders/' + this.dispatch.order.order_number + '/finalize-bill';
                },

                async workshopAction(action) {
                    if(action === 'start-processing' && !confirm('Are you sure you want to start washing this order?')) return;

                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/${action}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (action === 'start-processing' && data.order_number) {
                                window.open(`/orders/${data.order_number}/chit`, '_blank');
                            }
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to update status.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                },

                async submitDone() {
                    this.isDoneSubmitting = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/complete`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                cubby_id: this.doneCubbyId || null
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (data.order_id) {
                                window.open(`/orders/${data.order_id}/label`, '_blank');
                            }
                            window.location.reload();
                        } else {
                            alert(data.message || 'Failed to mark done.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isDoneSubmitting = false;
                },

                async submitDelivery() {
                    if (this.deliveryMethod === 'rider' && !this.deliveryRiderId) return alert('Select a rider');
                    if (this.deliveryMethod === 'pickme_flash' && !this.deliveryPickmeCost) return alert('Enter PickMe cost');

                    this.isDispatchingDelivery = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/delivery`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                delivery_method: this.deliveryMethod,
                                rider_id: this.deliveryRiderId,
                                pickme_cost: this.deliveryPickmeCost,
                                payment_method: this.deliveryPaymentMethod
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (this.dispatch && this.dispatch.order && this.dispatch.order.order_number) {
                                window.open('/orders/' + this.dispatch.order.order_number + '/print', '_blank');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            alert(data.message || 'Failed to dispatch delivery.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isDispatchingDelivery = false;
                },

                async submitHandover() {
                    this.isHandoverSubmitting = true;
                    try {
                        const res = await fetch(`/workshop/dispatches/${this.dispatch.id}/handover`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_method: this.handoverPaymentMethod,
                                amount_paid: this.handoverAmountPaid ? parseFloat(this.handoverAmountPaid) : 0
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (this.handoverPrintReceipt && data.order_number) {
                                window.open(`/orders/${data.order_number}/print`, '_blank');
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        } else {
                            alert(data.message || 'Failed to hand over.');
                        }
                    } catch (err) {
                        console.error('AJAX Error:', err);
                        alert('Connection error');
                    }
                    this.isHandoverSubmitting = false;
                }
            }
        }
    </script>
</div>
