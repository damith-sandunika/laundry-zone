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
            <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Payment Slips</h3>
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
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, 
                                    body: JSON.stringify(payload) 
                                })
                                .then(r => r.json())
                                .then(res => { 
                                    if (res.success) { window.location.reload(); } else { alert(res.message || 'Error occurred'); this.isUpdating = false; } 
                                })
                                .catch(err => { alert('Error occurred while communicating with server.'); this.isUpdating = false; }); 
                            } 
                        }"
                        style="{{ $isDeleted ? 'background:#fff1f2;border:1.5px dashed #f43f5e;' : 'background:#f8fafc;border:1px solid #cbd5e1;' }}border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:8px;position:relative;">
                        
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <div style="width:34px;height:34px;flex-shrink:0;{{ $isDeleted ? 'background:#ffe4e6;color:#e11d48;' : ($isRefund ? 'background:#fef2f2;color:#dc2626;' : 'background:#eef2ff;color:#4f46e5;') }}border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">
                                    @if($isDeleted) <i class="fa-solid fa-ban"></i>
                                    @elseif($isRefund) <i class="fa-solid fa-arrow-rotate-left"></i>
                                    @else <i class="fa-solid fa-file-invoice"></i> @endif
                                </div>
                                <div>
                                    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                        <p style="font-size:12px;font-weight:800;{{ $isDeleted ? 'color:#9f1239;text-decoration:line-through;' : ($isRefund ? 'color:#dc2626;' : 'color:#0f172a;') }}margin:0;">
                                            {{ $isRefund ? '- Rs.' . number_format(abs($tx->amount), 2) : 'Rs.' . number_format($tx->amount, 2) }}
                                        </p>
                                        @if($isDeleted)
                                            <span style="background:#e11d48;color:white;font-size:9px;font-weight:900;padding:2px 6px;border-radius:4px;text-transform:uppercase;">
                                                <i class="fa-solid fa-trash-can" style="font-size:8px;"></i> DELETED
                                            </span>
                                        @elseif($isRefund)
                                            <span style="background:#fee2e2;color:#991b1b;font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Refund</span>
                                        @else
                                            <span style="background:{{ $pmBg[0] }};color:{{ $pmBg[1] }};font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;">{{ $pmLabel }}</span>
                                        @endif
                                        @if(!$isDeleted && $tx->is_edited)
                                            <span style="background:#fef3c7;color:#b45309;font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fde68a;">
                                                <i class="fa-solid fa-pen" style="font-size:8px;"></i> Edited
                                            </span>
                                        @endif
                                    </div>
                                    <p style="font-size:10px;color:#64748b;margin:3px 0 0;font-weight:600;">Recorded: {{ $tx->created_at->format('M d, h:i A') }}</p>
                                    <div style="margin-top:4px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;">
                                        @if($tx->creator)
                                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;color:#334155;background:#e2e8f0;padding:2px 6px;border-radius:4px;">
                                                <i class="fa-solid fa-user-check" style="font-size:9px;color:#2563eb;"></i> {{ $tx->creator->name }}
                                            </span>
                                        @elseif($order->customer)
                                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:700;color:#0369a1;background:#e0f2fe;padding:2px 6px;border-radius:4px;">
                                                <i class="fa-solid fa-user-tag" style="font-size:9px;color:#0284c7;"></i> {{ $order->customer->name }}
                                            </span>
                                        @else
                                            <span style="display:inline-flex;align-items:center;gap:3px;font-size:10px;font-weight:600;color:#64748b;background:#f1f5f9;padding:2px 6px;border-radius:4px;">System</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                @if($isDeleted) <span style="background:#fee2e2;color:#991b1b;font-size:9.5px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;">VOIDED</span>
                                @elseif($tx->status === 'pending') <span style="background:#fef9c3;color:#a16207;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fef08a;">Pending</span>
                                @elseif($tx->status === 'completed') <span style="background:#f0fdf4;color:#16a34a;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #bbf7d0;">Approved</span>
                                @elseif($tx->status === 'rejected') <span style="background:#fef2f2;color:#dc2626;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;">Rejected</span>
                                @endif

                                @if(!$isDeleted && !$isRefund)
                                    <div style="display:flex;align-items:center;gap:6px;margin-top:2px;">
                                        @if($isToday)
                                            <button type="button" @click="openPaymentEditModal = true" style="color:#2563eb;font-size:10.5px;font-weight:700;cursor:pointer;border:none;background:none;padding:2px;">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <button type="button" @click="openPaymentDeleteModal = true" style="color:#dc2626;font-size:10.5px;font-weight:700;cursor:pointer;border:none;background:none;padding:2px;">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </button>
                                        @else
                                            <span style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;"><i class="fa-solid fa-lock" style="font-size:8px;"></i> Locked</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if($isDeleted)
                            <div style="background:#ffe4e6;border:1px solid #fecdd3;border-radius:6px;padding:6px 10px;font-size:10.5px;color:#9f1239;">
                                <div style="font-weight:800;"><i class="fa-solid fa-circle-exclamation"></i> Deleted by {{ $tx->deleter->name ?? 'Admin' }}</div>
                                @if($tx->delete_reason) <div style="margin-top:2px;font-weight:600;color:#881337;">Reason: "{{ $tx->delete_reason }}"</div> @endif
                            </div>
                        @endif

                        @if(!$isDeleted && $tx->is_edited)
                            <div style="background:#fefce8;border:1px solid #fef08a;border-radius:6px;padding:5px 8px;font-size:10px;color:#854d0e;">
                                <div><strong>Edited:</strong> Was <s>Rs.{{ number_format($tx->original_amount ?? $tx->amount, 2) }}</s></div>
                                @if($tx->edit_reason) <div style="font-style:italic;">"{{ $tx->edit_reason }}"</div> @endif
                            </div>
                        @endif

                        @if($tx->receipt_path || ($tx->status === 'pending' && !$isDeleted))
                            <div style="display:flex;gap:6px;margin-top:4px;">
                                @if($tx->receipt_path)
                                    <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}" target="_blank" style="flex:1;text-align:center;padding:6px;background:white;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;">View Slip</a>
                                @endif
                                @if($tx->status === 'pending' && !$isDeleted)
                                    <button type="button" @click="openReviewModal = true" style="flex:1;padding:6px;background:#4f46e5;color:white;border:none;border-radius:6px;font-size:11px;font-weight:800;cursor:pointer;">Review</button>
                                @endif
                            </div>
                        @endif

                        @if($tx->status === 'rejected')
                            <div style="font-size:10px;color:#b91c1c;background:#fef2f2;padding:6px;border-radius:4px;">
                                <strong>Reason:</strong> {{ $tx->reject_reason }}
                            </div>
                        @endif

                        @include('orders.partials.payment_slips.edit_delete_modals', ['tx' => $tx])
                        @include('orders.partials.payment_slips.review_modal', ['tx' => $tx])
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @include('orders.partials.payment_slips.audit_history')

    @if($order->cubbies->count())
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
            <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Storage</h3>
            @foreach($order->cubbies->sortByDesc('assigned_at') as $oc)
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;{{ $oc->released_at ? 'opacity:0.5;' : '' }}">
                    <div style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">{{ $oc->cubby->name ?? '?' }}</div>
                    <div style="flex:1;">
                        <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubby: {{ $oc->cubby->name ?? 'N/A' }}</p>
                        <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">Assigned: {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}</p>
                    </div>
                    @if($oc->released_at) <span style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;">Released</span>
                    @else <span style="font-size:9px;font-weight:800;color:#16a34a;background:#f0fdf4;padding:2px 6px;border-radius:4px;">Active</span> @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($order->notes)
        <div style="background:#fffbeb;border:1.5px solid #fef3c7;border-radius:12px;padding:12px;">
            <span style="font-size:10px;font-weight:800;color:#d97706;text-transform:uppercase;display:block;margin-bottom:2px;">Order Notes</span>
            <p style="font-size:12px;color:#b45309;margin:0;font-weight:600;line-height:1.4;">{{ $order->notes }}</p>
        </div>
    @endif
</div>
