<!-- ======= PAYMENT SLIPS ======= -->
            <div class="w-full flex flex-col gap-4 mt-4">

                @php
                    $paymentTxs = \App\Models\PaymentTransaction::withTrashed()
                        ->with(['creator', 'editor', 'deleter'])
                        ->where('order_id', $order->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                    $todayStr = now()->format('Y-m-d');
                @endphp

                @if($paymentTxs->count() > 0)
                    <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Payment Slips</h3>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:16px;">
                            @foreach($paymentTxs as $index => $tx)
                                @php
                                    $isDeleted = $tx->trashed();
                                    $isToday = $tx->created_at->format('Y-m-d') === $todayStr;
                                    $isRefund = $tx->amount < 0;
                                    $pm = strtolower($tx->payment_method ?? 'unknown');
                                    $pmLabels = ['cash'=>'Cash', 'card'=>'Card', 'bank'=>'Bank Transfer', 'credit'=>'Credit', 'online'=>'Online'];
                                    $pmLabel = $pmLabels[$pm] ?? ucfirst($pm);
                                    $pmColors = ['cash'=>'#d1fae5:#065f46', 'card'=>'#dbeafe:#1e40af', 'bank'=>'#f3e8ff:#7e22ce', 'credit'=>'#ffedd5:#c2410c', 'online'=>'#e0e7ff:#3730a3'];
                                    $pmBg = explode(':', $pmColors[$pm] ?? '#f1f5f9:#475569');
                                @endphp

                                <div x-data="{ 
                                        openReviewModal: false, 
                                        openPaymentEditModal: false, 
                                        openPaymentDeleteModal: false,
                                        editReason: '', 
                                        newPaymentMethod: '{{ $pm === 'unknown' ? 'cash' : $pm }}', 
                                        newPaymentAmount: '{{ abs($tx->amount) }}',
                                        deleteReason: '',
                                        isUpdating: false, 
                                        updatePayment(isDelete = false) { 
                                            this.isUpdating = true; 
                                            const payload = {
                                                type: 'payment_change',
                                                reason: isDelete ? this.deleteReason : this.editReason,
                                                new_data: {
                                                    transaction_id: {{ $tx->id }},
                                                    is_delete: isDelete,
                                                    payment_method: isDelete ? '{{ $pm }}' : this.newPaymentMethod,
                                                    amount: isDelete ? {{ $tx->amount }} : parseFloat(this.newPaymentAmount),
                                                    delete_reason: isDelete ? this.deleteReason : null,
                                                    reason: isDelete ? this.deleteReason : this.editReason
                                                }
                                            };
                                            fetch('{{ route('orders.corrections.store', $order->id) }}', { 
                                                method: 'POST', 
                                                headers: { 
                                                    'Content-Type': 'application/json', 
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                                                    'Accept': 'application/json' 
                                                }, 
                                                body: JSON.stringify(payload) 
                                            })
                                            .then(r => r.json())
                                            .then(res => { 
                                                if (res.success) { 
                                                    window.location.reload(); 
                                                } else { 
                                                    alert(res.message || 'Error occurred'); 
                                                    this.isUpdating = false; 
                                                } 
                                            })
                                            .catch(err => { 
                                                alert('Error occurred while communicating with server.'); 
                                                this.isUpdating = false; 
                                            }); 
                                        } 
                                    }"
                                    style="{{ $isDeleted ? 'background:#fff1f2;border:1.5px dashed #f43f5e;' : 'background:#f8fafc;border:1px solid #cbd5e1;' }}border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:8px;position:relative;">
                                    
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                                        <div style="display:flex;align-items:flex-start;gap:8px;">
                                            <div
                                                style="width:34px;height:34px;flex-shrink:0;{{ $isDeleted ? 'background:#ffe4e6;color:#e11d48;' : ($isRefund ? 'background:#fef2f2;color:#dc2626;' : 'background:#eef2ff;color:#4f46e5;') }}border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">
                                                @if($isDeleted)
                                                    <i class="fa-solid fa-ban"></i>
                                                @elseif($isRefund)
                                                    <i class="fa-solid fa-arrow-rotate-left"></i>
                                                @else
                                                    <i class="fa-solid fa-file-invoice"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                                    <p style="font-size:12px;font-weight:800;{{ $isDeleted ? 'color:#9f1239;text-decoration:line-through;' : ($isRefund ? 'color:#dc2626;' : 'color:#0f172a;') }}margin:0;">
                                                        {{ $isRefund ? '- Rs.' . number_format(abs($tx->amount), 2) : 'Rs.' . number_format($tx->amount, 2) }}
                                                    </p>

                                                    @if($isDeleted)
                                                        <span style="background:#e11d48;color:white;font-size:9px;font-weight:900;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;">
                                                            <i class="fa-solid fa-trash-can" style="font-size:8px;"></i> DELETED
                                                        </span>
                                                    @elseif($isRefund)
                                                        <span style="background:#fee2e2;color:#991b1b;font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.02em;">
                                                            Refund / Change
                                                        </span>
                                                    @else
                                                        <span style="background:{{ $pmBg[0] }};color:{{ $pmBg[1] }};font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.02em;">
                                                            {{ $pmLabel }}
                                                        </span>
                                                    @endif

                                                    @if(!$isDeleted && $tx->is_edited)
                                                        <span style="background:#fef3c7;color:#b45309;font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fde68a;" title="Original: Rs.{{ number_format($tx->original_amount ?? $tx->amount, 2) }} ({{ ucfirst($tx->original_payment_method ?? $tx->payment_method) }})">
                                                            <i class="fa-solid fa-pen" style="font-size:8px;"></i> Edited
                                                        </span>
                                                    @endif
                                                </div>

                                                <p style="font-size:10px;color:#64748b;margin:3px 0 0;font-weight:600;">
                                                    Recorded: {{ $tx->created_at->format('M d, h:i A') }}
                                                </p>

                                                <div style="margin-top:4px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                                    @if($tx->creator)
                                                        <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;color:#334155;background:#e2e8f0;padding:2px 6px;border-radius:4px;">
                                                            <i class="fa-solid fa-user-check" style="font-size:9px;color:#2563eb;"></i>
                                                            {{ $tx->creator->name }} <span style="font-size:9px;font-weight:600;color:#64748b;">({{ $tx->creator->roleLabel() }})</span>
                                                        </span>
                                                    @elseif($order->customer)
                                                        <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;color:#0369a1;background:#e0f2fe;padding:2px 6px;border-radius:4px;">
                                                            <i class="fa-solid fa-user-tag" style="font-size:9px;color:#0284c7;"></i>
                                                            Customer: {{ $order->customer->name }}
                                                        </span>
                                                    @else
                                                        <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;color:#64748b;background:#f1f5f9;padding:2px 6px;border-radius:4px;">
                                                            <i class="fa-solid fa-user" style="font-size:9px;"></i> System
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge & Quick Actions -->
                                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                            @if($isDeleted)
                                                <span style="background:#fee2e2;color:#991b1b;font-size:9.5px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;text-transform:uppercase;">
                                                    VOIDED
                                                </span>
                                            @elseif($tx->status === 'pending')
                                                <span style="background:#fef9c3;color:#a16207;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fef08a;text-transform:uppercase;">
                                                    Pending
                                                </span>
                                            @elseif($tx->status === 'completed')
                                                <span style="background:#f0fdf4;color:#16a34a;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #bbf7d0;text-transform:uppercase;">
                                                    Approved
                                                </span>
                                            @elseif($tx->status === 'rejected')
                                                <span style="background:#fef2f2;color:#dc2626;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;text-transform:uppercase;">
                                                    Rejected
                                                </span>
                                            @endif

                                            <!-- Edit & Delete Buttons for active slips -->
                                            @if(!$isDeleted && !$isRefund)
                                                <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                                    @if($isToday)
                                                        <button type="button" @click="openPaymentEditModal = true" 
                                                            style="color:#2563eb;font-size:10.5px;font-weight:700;cursor:pointer;border:none;background:none;padding:2px;outline:none;display:inline-flex;align-items:center;gap:3px;" 
                                                            title="Edit payment amount and method">
                                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                                        </button>
                                                        <button type="button" @click="openPaymentDeleteModal = true" 
                                                            style="color:#dc2626;font-size:10.5px;font-weight:700;cursor:pointer;border:none;background:none;padding:2px;outline:none;display:inline-flex;align-items:center;gap:3px;" 
                                                            title="Delete this payment slip">
                                                            <i class="fa-solid fa-trash-can"></i> Delete
                                                        </button>
                                                    @else
                                                        <span style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;" title="Past days cannot be edited or deleted to keep daily cash accounts balanced">
                                                            <i class="fa-solid fa-lock" style="font-size:8px;"></i> Locked (Past Day)
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Deletion Audit Information Banner -->
                                    @if($isDeleted)
                                        <div style="background:#ffe4e6;border:1px solid #fecdd3;border-radius:6px;padding:6px 10px;font-size:10.5px;color:#9f1239;margin-top:2px;">
                                            <div style="font-weight:800;display:flex;align-items:center;gap:4px;">
                                                <i class="fa-solid fa-circle-exclamation"></i>
                                                Deleted by {{ $tx->deleter->name ?? 'Admin' }} on {{ $tx->deleted_at ? \Carbon\Carbon::parse($tx->deleted_at)->format('M d, h:i A') : 'Unknown' }}
                                            </div>
                                            @if($tx->delete_reason)
                                                <div style="margin-top:2px;font-weight:600;color:#881337;">
                                                    Reason: "{{ $tx->delete_reason }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Edit Audit Information Banner -->
                                    @if(!$isDeleted && $tx->is_edited)
                                        <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:5px 8px;font-size:10px;color:#854d0e;margin-top:2px;">
                                            <div>
                                                <strong>Edited:</strong> Was <s>Rs.{{ number_format($tx->original_amount ?? $tx->amount, 2) }} ({{ ucfirst($tx->original_payment_method ?? $tx->payment_method) }})</s>
                                                @if($tx->editor)
                                                    by {{ $tx->editor->name }}
                                                @endif
                                                @if($tx->edited_at)
                                                    at {{ \Carbon\Carbon::parse($tx->edited_at)->format('M d, h:i A') }}
                                                @endif
                                            </div>
                                            @if($tx->edit_reason)
                                                <div style="margin-top:2px;font-style:italic;color:#713f12;">
                                                    "{{ $tx->edit_reason }}"
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($tx->receipt_path || ($tx->status === 'pending' && !$isDeleted))
                                    <div style="display:flex;gap:6px;margin-top:4px;">
                                        @if($tx->receipt_path)
                                            <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                target="_blank"
                                                style="flex:1;text-align:center;padding:6px;background:white;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;transition:all 0.15s;"
                                                onmouseover="this.style.background='#f1f5f9'"
                                                onmouseout="this.style.background='white'">View Slip</a>
                                        @endif

                                        @if($tx->status === 'pending' && !$isDeleted)
                                            <button type="button" @click="openReviewModal = true"
                                                style="flex:1;padding:6px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 1px 2px rgba(79,70,229,0.3);">Review</button>
                                        @endif
                                    </div>
                                    @endif

                                    @if($tx->status === 'rejected')
                                        <div
                                            style="font-size:10px;color:#b91c1c;background:#fef2f2;padding:6px;border-radius:4px;margin-top:2px;">
                                            <strong
                                                style="text-transform:uppercase;letter-spacing:0.05em;font-size:9px;">Reason:</strong><br>
                                            {{ $tx->reject_reason }}
                                        </div>
                                    @endif

                                    @if(!$isDeleted && $isToday)
                                    <!-- Edit Payment Modal -->
                                    <div x-show="openPaymentEditModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openPaymentEditModal = false"></div>
                                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
                                            <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                                                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                                    <i class="fa-solid fa-pen text-blue-600"></i> Edit Payment Slip #{{ $tx->id }}
                                                </h3>
                                                <button type="button" @click="openPaymentEditModal = false" class="text-slate-400 hover:text-slate-600">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                                                    <input type="number" step="0.01" min="0" x-model="newPaymentAmount" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-bold text-slate-800 outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                                                    <select x-model="newPaymentMethod" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-semibold outline-none">
                                                        <option value="cash">Cash</option>
                                                        <option value="card">Card</option>
                                                        <option value="bank">Bank Transfer</option>
                                                        <option value="credit">Credit</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Edit <span class="text-red-500">*</span></label>
                                                    <input type="text" x-model="editReason" placeholder="e.g. Corrected cash amount entered" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 font-medium outline-none">
                                                </div>
                                                <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg text-[11px] text-amber-800 font-medium leading-tight">
                                                    <i class="fa-solid fa-shield-halved text-amber-600"></i> Changes are recorded in audit logs and will update drawer balance automatically.
                                                </div>
                                            </div>
                                            <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-2">
                                                <button type="button" @click="openPaymentEditModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                                                <button type="button" @click="updatePayment(false)" :disabled="!editReason || !newPaymentAmount || newPaymentAmount <= 0 || isUpdating" class="flex-1 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 shadow-sm flex items-center justify-center gap-2 transition-colors">
                                                    <span x-show="!isUpdating">Save Changes</span>
                                                    <i x-show="isUpdating" class="fa-solid fa-spinner fa-spin"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Edit Payment Modal -->

                                    <!-- Delete Payment Confirmation Modal -->
                                    <div x-show="openPaymentDeleteModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openPaymentDeleteModal = false"></div>
                                        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm flex flex-col overflow-hidden">
                                            <div class="p-4 border-b border-rose-200 flex justify-between items-center bg-rose-50">
                                                <h3 class="text-sm font-bold text-rose-800 flex items-center gap-2">
                                                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Delete Payment Slip #{{ $tx->id }}
                                                </h3>
                                                <button type="button" @click="openPaymentDeleteModal = false" class="text-rose-400 hover:text-rose-600">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <div class="text-xs text-slate-700 font-medium">
                                                    Are you sure you want to delete this payment of <strong class="text-slate-900 font-bold">Rs. {{ number_format($tx->amount, 2) }} ({{ ucfirst($tx->payment_method) }})</strong>?
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Deletion <span class="text-rose-500">*</span></label>
                                                    <textarea x-model="deleteReason" rows="2" placeholder="e.g. Duplicate entry by staff, customer cancelled" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-rose-500 font-medium outline-none resize-none"></textarea>
                                                </div>
                                                <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-[11px] text-rose-800 font-medium leading-tight">
                                                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i> The slip will remain visible marked as <strong>DELETED</strong> for accounting audit, and the order balance will adjust.
                                                </div>
                                            </div>
                                            <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-2">
                                                <button type="button" @click="openPaymentDeleteModal = false" class="flex-1 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancel</button>
                                                <button type="button" @click="updatePayment(true)" :disabled="!deleteReason || isUpdating" class="flex-1 py-2 text-xs font-bold text-white bg-rose-600 rounded-lg hover:bg-rose-700 disabled:opacity-50 shadow-sm flex items-center justify-center gap-2 transition-colors">
                                                    <span x-show="!isUpdating">Confirm Delete</span>
                                                    <i x-show="isUpdating" class="fa-solid fa-spinner fa-spin"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Delete Payment Confirmation Modal -->
                                    @endif

                                    <!-- Review Modal -->
                                    <div x-show="openReviewModal" style="display:none;">
                                        <div
                                            style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:9999;">
                                                <div @click.outside="openReviewModal = false"
                                                    style="background:white;border-radius:16px;padding:20px;width:100%;max-width:350px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);">
                                                    <div
                                                        style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                                                        <h3 style="font-size:15px;font-weight:900;color:#0f172a;margin:0;">
                                                            Review Payment</h3>
                                                        <button @click="openReviewModal = false"
                                                            style="background:none;border:none;cursor:pointer;color:#64748b;font-size:16px;line-height:1;">✕</button>
                                                    </div>

                                                    @if($tx->receipt_path)
                                                        <!-- Scrollable Image -->
                                                        <div
                                                            style="width:100%;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;overflow-y:auto;overflow-x:hidden;max-height:220px;margin-bottom:12px;">
                                                            <img src="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                                alt="Payment Slip" style="width:100%;height:auto;display:block;">
                                                        </div>
                                                        <div style="text-align:right;margin-bottom:12px;">
                                                            <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                                target="_blank"
                                                                style="font-size:11px;color:#4f46e5;font-weight:700;text-decoration:none;"><i
                                                                    class="fa-solid fa-arrow-up-right-from-square"></i> Open in New
                                                                Tab</a>
                                                        </div>
                                                    @else
                                                        <!-- No Image Info -->
                                                        <div
                                                            style="padding:24px 20px;text-align:center;background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border-radius:16px;border:1px solid #e2e8f0;margin-bottom:16px;box-shadow:inset 0 2px 4px rgba(255,255,255,0.7), 0 4px 12px rgba(0,0,0,0.02);position:relative;overflow:hidden;">
                                                            <!-- Decorative circles -->
                                                            <div
                                                                style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);border-radius:50%;opacity:0.5;">
                                                            </div>
                                                            <div
                                                                style="position:absolute;bottom:-30px;left:-10px;width:60px;height:60px;background:linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);border-radius:50%;opacity:0.4;">
                                                            </div>

                                                            <div style="position:relative;z-index:1;">
                                                                <div
                                                                    style="width:56px;height:56px;background:linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 8px 16px rgba(79,70,229,0.25);transform:rotate(-5deg);">
                                                                    <i class="fa-solid fa-credit-card"
                                                                        style="font-size:24px;color:white;transform:rotate(5deg);"></i>
                                                                </div>
                                                                <h4
                                                                    style="font-size:16px;font-weight:900;color:#0f172a;margin:0 0 6px;letter-spacing:-0.02em;">
                                                                    Online Card Payment</h4>
                                                                <p
                                                                    style="font-size:12px;color:#64748b;margin:0;line-height:1.5;font-weight:500;padding:0 10px;">
                                                                    No physical slip was uploaded.<br>
                                                                    <span style="color:#475569;font-weight:700;">Please verify with
                                                                        your payment gateway.</span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div
                                                        style="margin-bottom:12px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;font-size:12px;color:#334155;display:flex;justify-content:space-between;align-items:center;">
                                                        <div>
                                                            <span
                                                                style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Amount
                                                                Paid</span>
                                                            <span
                                                                style="font-size:16px;font-weight:900;color:#0f172a;">Rs.{{ number_format($tx->amount, 2) }}</span>
                                                        </div>
                                                        <div style="text-align:right;">
                                                            <span
                                                                style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Time</span>
                                                            <span
                                                                style="font-size:11px;font-weight:700;color:#334155;">{{ $tx->created_at->format('M d, h:i A') }}</span>
                                                        </div>
                                                    </div>

                                                    <div style="margin-bottom:12px;padding:8px 12px;background:#f1f5f9;border-radius:8px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;">
                                                        <span style="font-size:11px;font-weight:700;color:#64748b;">Paid By / Recorded By:</span>
                                                        @if($tx->creator)
                                                            <span style="font-size:11px;font-weight:800;color:#1e40af;">
                                                                <i class="fa-solid fa-user-check"></i> {{ $tx->creator->name }}
                                                            </span>
                                                        @elseif($order->customer)
                                                            <span style="font-size:11px;font-weight:800;color:#0369a1;">
                                                                <i class="fa-solid fa-user-tag"></i> {{ $order->customer->name }} (Customer)
                                                            </span>
                                                        @else
                                                            <span style="font-size:11px;font-weight:700;color:#64748b;">System</span>
                                                        @endif
                                                    </div>

                                                    <div x-data="{ mode: 'review' }">
                                                        <!-- Action Buttons -->
                                                        <div x-show="mode === 'review'"
                                                            style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                            <form method="POST"
                                                                action="{{ route('orders.payments.approve', [$order->order_number, $tx->id]) }}"
                                                                style="margin:0;">
                                                                @csrf
                                                                <button type="submit"
                                                                    style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #16a34a, #15803d);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;"
                                                                    onmouseover="this.style.transform='translateY(-1px)'"
                                                                    onmouseout="this.style.transform='translateY(0)'">
                                                                    <i class="fa-solid fa-check"></i> Approve
                                                                </button>
                                                            </form>
                                                            <button type="button" @click="mode = 'reject'"
                                                                style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #dc2626, #b91c1c);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);display:flex;align-items:center;justify-content:center;gap:6px;transition:transform 0.15s;"
                                                                onmouseover="this.style.transform='translateY(-1px)'"
                                                                onmouseout="this.style.transform='translateY(0)'">
                                                                <i class="fa-solid fa-xmark"></i> Reject
                                                            </button>
                                                        </div>

                                                        <!-- Reject Form -->
                                                        <form x-cloak x-show="mode === 'reject'" method="POST"
                                                            action="{{ route('orders.payments.reject', [$order->order_number, $tx->id]) }}"
                                                            style="margin-top:8px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                                                            @csrf
                                                            <div>
                                                                <label
                                                                    style="font-size:11px;font-weight:800;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:6px;"><i
                                                                        class="fa-solid fa-circle-info"></i> Rejection
                                                                    Reason</label>
                                                                <textarea name="reject_reason" required rows="2"
                                                                    placeholder="e.g. Unclear image..."
                                                                    style="width:100%;padding:10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#334155;outline:none;resize:none;"
                                                                    onfocus="this.style.borderColor='#dc2626'"
                                                                    onblur="this.style.borderColor='#cbd5e1'"></textarea>
                                                            </div>
                                                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                                                <button type="button" @click="mode = 'review'"
                                                                    style="width:100%;padding:12px;background:white;color:#64748b;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;">Cancel</button>
                                                                <button type="submit"
                                                                    style="width:100%;padding:12px;background:#dc2626;color:white;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);">Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($order->correctionRequests->where('type', 'payment_change')->count() > 0)
                    <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Payment Audit & Correction History</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($order->correctionRequests->where('type', 'payment_change')->sortByDesc('created_at') as $cr)
                                <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;font-size:12px;display:flex;flex-direction:column;gap:6px;
                                    {{ $cr->status === 'pending' ? 'border-left:4px solid #f59e0b;' : ($cr->status === 'approved' ? 'border-left:4px solid #10b981;' : 'border-left:4px solid #ef4444;') }}">
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <strong style="font-size:10px;text-transform:uppercase;letter-spacing:0.05em;color:#475569;">
                                            {{ ucfirst($cr->status) }} Request
                                        </strong>
                                        <span style="font-size:10px;color:#64748b;font-weight:600;">{{ $cr->created_at->format('M d, h:i A') }}</span>
                                    </div>
                                    @php
                                        $oldMethod = $cr->old_data['payment_method'] ?? 'unknown';
                                        $newMethod = $cr->new_data['payment_method'] ?? 'unknown';
                                        $isDel = !empty($cr->new_data['is_delete']);
                                        $oldAmt = $cr->old_data['amount'] ?? null;
                                        $newAmt = $cr->new_data['amount'] ?? null;
                                    @endphp
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                        @if($isDel)
                                            <span style="background:#fee2e2;color:#b91c1c;padding:2px 6px;border-radius:4px;font-weight:800;font-size:10px;">
                                                <i class="fa-solid fa-trash-can"></i> PAYMENT DELETION
                                            </span>
                                            <span style="color:#64748b;font-weight:600;">Slip #{{ $cr->new_data['transaction_id'] ?? '?' }}</span>
                                        @else
                                            <span style="text-decoration:line-through;color:#ef4444;font-weight:700;">{{ ucfirst($oldMethod) }}</span>
                                            <i class="fa-solid fa-arrow-right" style="color:#94a3b8;font-size:10px;"></i>
                                            <span style="color:#10b981;font-weight:800;">{{ ucfirst($newMethod) }}</span>
                                            @if($newAmt !== null && $oldAmt !== null && $newAmt != $oldAmt)
                                                <span style="font-size:11px;color:#475569;font-weight:700;">
                                                    (Rs. {{ number_format($oldAmt, 2) }} &rarr; Rs. {{ number_format($newAmt, 2) }})
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    @if($cr->reason)
                                        <div style="background:white;padding:6px 8px;border-radius:6px;border:1px solid #e2e8f0;color:#334155;font-style:italic;font-size:11px;">
                                            "{{ $cr->reason }}"
                                        </div>
                                    @endif
                                    <div style="font-size:10px;color:#64748b;margin-top:2px;">
                                        By <strong>{{ $cr->requestedBy->name ?? 'Unknown' }}</strong>
                                        @if($cr->status !== 'pending' && $cr->approvedBy)
                                            &bull; {{ ucfirst($cr->status) }} by <strong>{{ $cr->approvedBy->name }}</strong>
                                        @endif
                                    </div>
                                    @if($cr->status === 'pending' && in_array(auth()->user()->role, ['admin', 'branch_admin', 'workshop_admin', 'super_admin']))
                                        <div style="display:flex;gap:6px;margin-top:6px;">
                                            <form action="{{ route('corrections.approve', $cr->id) }}" method="POST" style="flex:1;" onsubmit="return confirm('Approve payment change?')">
                                                @csrf
                                                <button type="submit" style="width:100%;padding:6px;background:#10b981;color:white;border:none;border-radius:6px;font-size:10px;font-weight:800;cursor:pointer;">Approve</button>
                                            </form>
                                            <form action="{{ route('corrections.reject', $cr->id) }}" method="POST" style="flex:1;" onsubmit="return confirm('Reject payment change?')">
                                                @csrf
                                                <button type="submit" style="width:100%;padding:6px;background:#fee2e2;color:#ef4444;border:none;border-radius:6px;font-size:10px;font-weight:800;cursor:pointer;">Reject</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($order->cubbies->count())
                    <div
                        style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3
                            style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Storage</h3>
                        @foreach($order->cubbies->sortByDesc('assigned_at') as $oc)
                            <div
                                style="display:flex;align-items:center;gap:10px;margin-bottom:8px;{{ $oc->released_at ? 'opacity:0.5;' : '' }}">
                                <div
                                    style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">
                                    {{ $oc->cubby->name ?? '?' }}
                                </div>
                                <div style="flex:1;">
                                    <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubby:
                                        {{ $oc->cubby->name ?? 'N/A' }}
                                    </p>
                                    <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">
                                        Assigned: {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}
                                        &bull; Loc: {{ $oc->cubby->branch->name ?? 'Unknown' }}
                                    </p>
                                </div>
                                @if($oc->released_at)
                                    <span
                                        style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Released</span>
                                @else
                                    <span
                                        style="font-size:9px;font-weight:800;color:#16a34a;background:#f0fdf4;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Active</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Order Notes Card -->
                @if($order->notes)
                    <div style="background:#fffbeb;border:1.5px solid #fef3c7;border-radius:12px;padding:12px;">
                        <span
                            style="font-size:10px;font-weight:800;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Order
                            Notes</span>
                        <p style="font-size:12px;color:#b45309;margin:0;font-weight:600;line-height:1.4;">
                            {{ $order->notes }}
                        </p>
                    </div>
                @endif
            </div>
        </div>