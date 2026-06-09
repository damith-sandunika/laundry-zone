<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <a href="{{ route('orders.index') }}" style="font-size:12px;color:#94a3b8;text-decoration:none;font-weight:700;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#94a3b8'">← Orders</a>
                    <span style="color:#cbd5e1;font-size:11px;">/</span>
                    <span style="font-size:12px;color:#64748b;font-weight:700;">{{ $order->order_number }}</span>
                </div>
                <h2 style="font-size:24px;font-weight:900;color:#0f172a;letter-spacing:-0.03em;margin:0 0 4px;">Order Details</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                    Customer: <span style="font-weight:700;color:#334155;">{{ $order->customer->name }}</span> &bull; {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
            </div>
            <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                style="display:flex;align-items:center;gap:7px;padding:9px 16px;background:white;color:#334155;border:1.5px solid #e2e8f0;font-size:13px;font-weight:700;border-radius:10px;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,0.04);transition:all 0.15s;"
                onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
                🖨 Print Bill
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div style="margin-bottom:12px;padding:10px 14px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;font-size:12px;font-weight:700;color:#16a34a;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if(session('print_bill_id'))
        <script>
            window.onload = function() {
                window.open("{{ route('orders.print', session('print_bill_id')) }}", "_blank");
            };
        </script>
    @endif

    <!-- Status Banner -->
    <div style="margin-bottom:16px;padding:12px 20px;border-radius:12px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:800;text-transform:uppercase;letter-spacing:0.05em;
        {{ $order->status === 'received' ? 'background:#fffbeb;color:#d97706;border:1.5px solid #fde68a;' : 
           ($order->status === 'done' ? 'background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;' : 
           ($order->status === 'delivered' ? 'background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;' : 
           'background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;')) }}">
        <span>Order Status:</span>
        <span style="font-size:16px;font-weight:900;">{{ $order->status }}</span>
    </div>

    <div class="flex flex-col md:flex-row gap-4 items-start">

        <!-- ======= LEFT COLUMN: Status + Items ======= -->
        <div class="w-full md:w-2/3 flex flex-col gap-4">

            <!-- Status Progress -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                @php
                    $stages = [
                        'received' => ['label' => 'Received', 'icon' => '📥'],
                        'done'     => ['label' => 'Done', 'icon' => '✅'],
                        'delivered'=> ['label' => 'Delivered', 'icon' => '🚚'],
                    ];
                    $statusOrder = ['received', 'done', 'delivered'];
                    $currentIndex = array_search($order->status, $statusOrder);
                @endphp

                <div style="display:flex;justify-content:space-between;align-items:center;position:relative;margin-bottom:16px;padding:0 20px;">
                    @foreach($stages as $key => $stage)
                        @php
                            $idx = array_search($key, $statusOrder);
                            $isDone = $idx <= ($currentIndex ?? -1);
                            $isCurrent = $key === $order->status;
                        @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;position:relative;z-index:2;">
                            <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all 0.15s;
                                {{ $isCurrent ? 'background:#1d4ed8;color:white;box-shadow:0 0 0 3px #dbeafe;' : ($isDone ? 'background:#16a34a;color:white;' : 'background:#f1f5f9;color:#94a3b8;') }}">
                                {{ $stage['icon'] }}
                            </div>
                            <span style="font-size:11px;font-weight:800;margin-top:6px;
                                {{ $isCurrent ? 'color:#1d4ed8;' : ($isDone ? 'color:#16a34a;' : 'color:#94a3b8;') }}">
                                {{ $stage['label'] }}
                            </span>
                        </div>
                    @endforeach

                    <!-- Progress bar line behind steps -->
                    <div style="position:absolute;left:15%;right:15%;top:15px;height:2px;background:#e2e8f0;z-index:1;">
                        <div style="height:100%;background:#1d4ed8;transition:width 0.2s;
                            width: {{ $currentIndex == 1 ? '50%' : ($currentIndex == 2 ? '100%' : '0%') }}"></div>
                    </div>
                </div>

                <!-- Update Status Form -->
                @if($order->status !== 'delivered' && $order->status !== 'cancelled')
                    <div style="border-top:1px solid #f1f5f9;padding-top:12px;">
                        <form id="statusForm" action="{{ route('orders.updateStatus', $order->id) }}" method="POST" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;margin:0;">
                            @csrf
                            <div style="display:flex;flex-direction:column;gap:4px;">
                                <label style="font-size:10px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">New Status</label>
                                <select id="statusSelect" name="status" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:600;outline:none;background:white;color:#334155;">
                                    @if($order->status === 'received')
                                        <option value="done">✅ Done</option>
                                        <option value="cancelled">❌ Cancel</option>
                                    @elseif($order->status === 'done')
                                        <option value="delivered">🚚 Delivered</option>
                                        <option value="cancelled">❌ Cancel</option>
                                    @endif
                                </select>
                            </div>

                            @if($cubbies->count())
                            <div id="cubby-wrapper" style="display:none;flex-direction:column;gap:4px;">
                                <label style="font-size:10px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">Cubbet</label>
                                <select name="cubby_id" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:600;outline:none;background:white;color:#334155;">
                                    <option value="">— Select —</option>
                                    @foreach($cubbies as $cubby)
                                        <option value="{{ $cubby->id }}">{{ $cubby->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div id="payment-method-wrapper" style="display:none;flex-direction:column;gap:4px;">
                                <label style="font-size:10px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:0.05em;">Payment</label>
                                <select name="payment_method" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:12px;font-weight:600;outline:none;background:white;color:#334155;">
                                    <option value="cash">💵 Cash</option>
                                    <option value="card">💳 Card / Transfer</option>
                                    <option value="credit">⏳ Credit</option>
                                </select>
                            </div>

                            <button type="submit" style="padding:7px 14px;background:#1d4ed8;color:white;font-size:12px;font-weight:700;border:none;border-radius:8px;cursor:pointer;transition:background 0.15s;"
                                onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='#1d4ed8'">
                                Update
                            </button>
                        </form>
                        <script>
                            (function() {
                                var sel = document.getElementById('statusSelect');
                                var cubbyWrap = document.getElementById('cubby-wrapper');
                                var payWrap = document.getElementById('payment-method-wrapper');
                                function updateVisibility() {
                                    var val = sel ? sel.value : '';
                                    if (cubbyWrap) cubbyWrap.style.display = val === 'done' ? 'flex' : 'none';
                                    if (payWrap) payWrap.style.display = val === 'delivered' ? 'flex' : 'none';
                                }
                                if (sel) {
                                    sel.addEventListener('change', updateVisibility);
                                    updateVisibility(); // run on load
                                }
                            })();
                        </script>
                    </div>
                @endif
            </div>

            <!-- Laundry Items Card -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);overflow:hidden;">
                <div style="padding:10px 16px;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
                    <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0;text-transform:uppercase;letter-spacing:0.05em;">Laundry Items</h3>
                </div>
                <table style="width:100%;border-collapse:collapse;text-align:left;font-size:12px;">
                    <thead>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <th style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;">Service Type</th>
                            <th style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:center;">Qty / Weight</th>
                            <th style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:right;">Unit Price</th>
                            <th style="padding:10px 16px;font-weight:800;color:#475569;font-size:10px;text-transform:uppercase;letter-spacing:0.04em;text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px 16px;font-weight:700;color:#0f172a;">{{ $item->serviceType->name ?? $item->item_description }}</td>
                            <td style="padding:10px 16px;text-align:center;font-weight:700;color:#334155;">{{ $item->quantity }}</td>
                            <td style="padding:10px 16px;text-align:right;font-weight:600;color:#475569;">Rs.{{ number_format($item->unit_price, 2) }}</td>
                            <td style="padding:10px 16px;text-align:right;font-weight:800;color:#0f172a;">Rs.{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">Subtotal</td>
                            <td style="padding:8px 16px;text-align:right;font-weight:700;color:#334155;">Rs.{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->discount > 0)
                        <tr>
                            <td colspan="3" style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">Discount</td>
                            <td style="padding:8px 16px;text-align:right;font-weight:700;color:#dc2626;">- Rs.{{ number_format($order->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="background:#f8fafc;border-top:1.5px solid #e2e8f0;">
                            <td colspan="3" style="padding:12px 16px;text-align:right;font-size:12px;font-weight:800;color:#0f172a;">Total</td>
                            <td style="padding:12px 16px;text-align:right;font-size:15px;font-weight:900;color:#1d4ed8;">Rs.{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Activity Log Card -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.05em;">Activity Log</h3>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($order->statusHistory as $log)
                        <div style="display:flex;align-items:flex-start;gap:10px;">
                            <div style="width:6px;height:6px;border-radius:50%;background:#3b82f6;margin-top:5px;"></div>
                            <div>
                                <p style="font-size:12px;font-weight:700;color:#0f172a;margin:0;">Status changed to <span style="color:#1d4ed8;text-transform:uppercase;font-size:10.5px;">{{ $log->status }}</span></p>
                                <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">
                                    By {{ $log->changedBy->name ?? 'System' }} &bull; {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ======= RIGHT COLUMN: Payment & Customer ======= -->
        <div class="w-full md:w-1/3 flex flex-col gap-4">

            <!-- Dates Card -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Timeline</h3>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Order Date</span>
                        <p style="font-weight:700;color:#0f172a;margin:0;">{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}</p>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup Date</span>
                        <p style="font-weight:700;color:#0f172a;margin:0;">{{ $order->due_date ? \Carbon\Carbon::parse($order->due_date)->format('M d, Y h:i A') : 'Not specified' }}</p>
                    </div>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Customer</h3>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                    <div>
                        <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Full Name</span>
                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">{{ $order->customer->name }}</p>
                    </div>
                    <div>
                        <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Phone</span>
                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">{{ $order->customer->phone }}</p>
                    </div>
                    @if($order->customer->address)
                    <div>
                        <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Address</span>
                        <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">{{ $order->customer->address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Details Card -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Payment</h3>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#64748b;font-weight:600;">Total Bill</span>
                        <span style="font-weight:800;color:#0f172a;">Rs.{{ number_format($order->total, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#64748b;font-weight:600;">Paid Amount</span>
                        <span style="font-weight:800;color:#16a34a;">Rs.{{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:6px;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;font-weight:600;">Balance Due</span>
                        <span style="font-weight:900;color:#dc2626;">Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:2px;">
                        <span style="color:#64748b;font-weight:600;">Status</span>
                        <span style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;
                            {{ $order->payment_status === 'paid' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;' }}">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                    @if($order->payment_method)
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#64748b;font-weight:600;">Method</span>
                        <span style="font-weight:700;color:#334155;text-transform:capitalize;">{{ $order->payment_method }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Storage Location / Cubbet -->
            @if($order->cubbies->count())
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Storage</h3>
                @foreach($order->cubbies as $oc)
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">
                            {{ $oc->cubby->name ?? '?' }}
                        </div>
                        <div>
                            <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubbet {{ $oc->cubby->name ?? 'N/A' }}</p>
                            <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">Assigned {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            <!-- Order Notes Card -->
            @if($order->notes)
            <div style="background:#fffbeb;border:1.5px solid #fef3c7;border-radius:12px;padding:12px;">
                <span style="font-size:10px;font-weight:800;color:#d97706;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:2px;">Order Notes</span>
                <p style="font-size:12px;color:#b45309;margin:0;font-weight:600;line-height:1.4;">{{ $order->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
