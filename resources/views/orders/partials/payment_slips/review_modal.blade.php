<div x-show="openReviewModal" style="display:none;">
    <div style="position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(2px);display:flex;align-items:center;justify-content:center;z-index:9999;">
        <div @click.outside="openReviewModal = false"
            style="background:white;border-radius:16px;padding:20px;width:100%;max-width:350px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h3 style="font-size:15px;font-weight:900;color:#0f172a;margin:0;">Review Payment</h3>
                <button @click="openReviewModal = false" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:16px;line-height:1;">✕</button>
            </div>

            @if($tx->receipt_path)
                <div style="width:100%;background:#f1f5f9;border-radius:12px;border:1px solid #e2e8f0;overflow-y:auto;overflow-x:hidden;max-height:220px;margin-bottom:12px;">
                    <img src="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                        alt="Payment Slip" style="width:100%;height:auto;display:block;">
                </div>
                <div style="text-align:right;margin-bottom:12px;">
                    <a href="{{ \Illuminate\Support\Str::startsWith($tx->receipt_path, 'http') ? $tx->receipt_path : asset('storage/' . $tx->receipt_path) }}"
                        target="_blank"
                        style="font-size:11px;color:#4f46e5;font-weight:700;text-decoration:none;"><i
                            class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab</a>
                </div>
            @else
                <div style="padding:24px 20px;text-align:center;background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border-radius:16px;border:1px solid #e2e8f0;margin-bottom:16px;box-shadow:inset 0 2px 4px rgba(255,255,255,0.7), 0 4px 12px rgba(0,0,0,0.02);position:relative;overflow:hidden;">
                    <div style="position:relative;z-index:1;">
                        <div style="width:56px;height:56px;background:linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 8px 16px rgba(79,70,229,0.25);transform:rotate(-5deg);">
                            <i class="fa-solid fa-credit-card" style="font-size:24px;color:white;transform:rotate(5deg);"></i>
                        </div>
                        <h4 style="font-size:16px;font-weight:900;color:#0f172a;margin:0 0 6px;letter-spacing:-0.02em;">Online Card Payment</h4>
                        <p style="font-size:12px;color:#64748b;margin:0;line-height:1.5;font-weight:500;padding:0 10px;">
                            No physical slip was uploaded.<br>
                            <span style="color:#475569;font-weight:700;">Please verify with your payment gateway.</span>
                        </p>
                    </div>
                </div>
            @endif

            <div style="margin-bottom:12px;background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0;font-size:12px;color:#334155;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <span style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Amount Paid</span>
                    <span style="font-size:16px;font-weight:900;color:#0f172a;">Rs.{{ number_format($tx->amount, 2) }}</span>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;display:block;">Time</span>
                    <span style="font-size:11px;font-weight:700;color:#334155;">{{ $tx->created_at->format('M d, h:i A') }}</span>
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
                <div x-show="mode === 'review'" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <form method="POST" action="{{ route('orders.payments.approve', [$order->order_number, $tx->id]) }}" style="margin:0;">
                        @csrf
                        <button type="submit" style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #16a34a, #15803d);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(22,163,74,0.3);display:flex;align-items:center;justify-content:center;gap:6px;">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                    </form>
                    <button type="button" @click="mode = 'reject'" style="width:100%;height:100%;padding:12px;background:linear-gradient(to right, #dc2626, #b91c1c);color:white;border:none;border-radius:12px;font-size:13px;font-weight:900;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);display:flex;align-items:center;justify-content:center;gap:6px;">
                        <i class="fa-solid fa-xmark"></i> Reject
                    </button>
                </div>

                <form x-cloak x-show="mode === 'reject'" method="POST" action="{{ route('orders.payments.reject', [$order->order_number, $tx->id]) }}" style="margin-top:8px;display:flex;flex-direction:column;gap:12px;background:#f8fafc;padding:16px;border-radius:12px;border:1px solid #e2e8f0;">
                    @csrf
                    <div>
                        <label style="font-size:11px;font-weight:800;color:#dc2626;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:6px;">
                            <i class="fa-solid fa-circle-info"></i> Rejection Reason
                        </label>
                        <textarea name="reject_reason" required rows="2" placeholder="e.g. Unclear image..." style="width:100%;padding:10px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:13px;color:#334155;outline:none;resize:none;"></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <button type="button" @click="mode = 'review'" style="width:100%;padding:12px;background:white;color:#64748b;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;">Cancel</button>
                        <button type="submit" style="width:100%;padding:12px;background:#dc2626;color:white;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,0.3);">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

