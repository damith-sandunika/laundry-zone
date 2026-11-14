            <!-- ======= PAYMENT SLIPS ======= -->
            <div class="max-w-sm flex flex-col gap-4 mt-4">

                @php
                    $paymentTxs = \App\Models\PaymentTransaction::where('order_id', $order->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                @endphp

                @if($paymentTxs->count() > 0)
                    <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                        <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            Payment Slips</h3>
                        <div style="display:flex;flex-direction:column;gap:10px;">
                            @foreach($paymentTxs as $index => $tx)
                                <div x-data="{ openReviewModal: false, openPaymentEditModal: false, editReason: '', newPaymentMethod: '{{ strtolower($tx->payment_method ?? 'cash') }}', isUpdating: false, updatePaymentMethod() { this.isUpdating = true; fetch('{{ route('orders.corrections.store', $order->id) }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ type: 'payment_change', reason: this.editReason, new_data: { payment_method: this.newPaymentMethod, transaction_id: {{ $tx->id }} } }) }).then(r => r.json()).then(res => { if (res.success) { window.location.reload(); } else { alert(res.message || 'Error occurred'); this.isUpdating = false; } }).catch(err => { alert('Error occurred'); this.isUpdating = false; }); } }"
                                    style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:12px;display:flex;flex-direction:column;gap:8px;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div
                                                style="width:32px;height:32px;background:#eef2ff;color:#4f46e5;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </div>
                                            <div>
                                                <div style="display:flex;align-items:center;gap:8px;">
                                                    <p style="font-size:11.5px;font-weight:800;color:#0f172a;margin:0;">
                                                        Rs.{{ number_format($tx->amount, 2) }}
                                                    </p>
                                                    @php
                                                        $pm = strtolower($tx->payment_method ?? 'unknown');
                                                        $pmLabels = ['cash'=>'Cash', 'card'=>'Card', 'bank'=>'Bank Transfer', 'credit'=>'Credit', 'online'=>'Online'];
                                                        $pmLabel = $pmLabels[$pm] ?? ucfirst($pm);
                                                        $pmColors = ['cash'=>'#d1fae5:#065f46', 'card'=>'#dbeafe:#1e40af', 'bank'=>'#f3e8ff:#7e22ce', 'credit'=>'#ffedd5:#c2410c', 'online'=>'#e0e7ff:#3730a3'];
                                                        $pmBg = explode(':', $pmColors[$pm] ?? '#f1f5f9:#475569');
                                                    @endphp
                                                    <span style="background:{{ $pmBg[0] }};color:{{ $pmBg[1] }};font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.02em;">
                                                        {{ $pmLabel }}
                                                    </span>
                                                    <button type="button" @click="openPaymentEditModal = true" style="color:#3b82f6;font-size:10px;font-weight:700;cursor:pointer;border:none;background:none;padding:2px;outline:none;text-decoration:underline;" title="Change Payment Method">
                                                        Edit
                                                    </button>
                                                </div>
                                                <p style="font-size:10px;color:#64748b;margin:3px 0 0;font-weight:600;">
                                                    {{ $tx->created_at->format('M d, h:i A') }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($tx->status === 'pending')
                                            <span
                                                style="background:#fef9c3;color:#a16207;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fef08a;text-transform:uppercase;">Pending</span>
                                        @elseif($tx->status === 'completed')
                                            <span
                                                style="background:#f0fdf4;color:#16a34a;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #bbf7d0;text-transform:uppercase;">Approved</span>
                                        @elseif($tx->status === 'rejected')
                                            <span
                                                style="background:#fef2f2;color:#dc2626;font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;border:1px solid #fecaca;text-transform:uppercase;">Rejected</span>
                                        @endif
                                    </div>

                                    @if($tx->receipt_path || $tx->status === 'pending')
                                    <div style="display:flex;gap:6px;margin-top:4px;">
                                        @if($tx->receipt_path)
                                            <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                                                target="_blank"
                                                style="flex:1;text-align:center;padding:6px;background:white;color:#334155;border:1px solid #cbd5e1;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;transition:all 0.15s;"
                                                onmouseover="this.style.background='#f1f5f9'"
                                                onmouseout="this.style.background='white'">View Slip</a>
                                        @endif

                                        @if($tx->status === 'pending')
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

                                    @include('orders.partials.details.payment_tx_modals', ['tx' => $tx, 'order' => $order])
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('orders.partials.details.payment_edit_history', ['order' => $order])

                @include('orders.partials.details.cubbies_notes', ['order' => $order])
            </div>
        </div>