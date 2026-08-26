<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                    <a href="{{ route('orders.index') }}" style="font-size:12px;color:#94a3b8;text-decoration:none;font-weight:700;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#94a3b8'">← Orders</a>
                    <span style="color:#cbd5e1;font-size:11px;">/</span>
                    <span style="font-size:12px;color:#64748b;font-weight:700;">{{ $order->order_number }}</span>
                </div>
                <h2 class="ph-title">Order Details</h2>
                <p style="font-size:13px;color:#94a3b8;font-weight:500;margin:0;">
                    Customer: <span style="font-weight:700;color:#334155;">{{ $order->customer->name ?? 'Unknown Customer' }}</span> &bull; {{ $order->created_at->format('M d, Y h:i A') }}
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
                <div class="mb-4 px-2">
                    <h3 class="text-sm font-black text-slate-800 mb-4">Order Journey</h3>
                    <div class="relative">
                        <div class="absolute left-[15px] top-4 bottom-4 w-[2px] bg-slate-100"></div>
                        <div class="space-y-5">
                            @php
                                $steps = collect();
                                $history = $order->statusHistory->sortBy('created_at')->values();
                                $dispatch = \App\Models\WorkshopDispatch::where('order_id', $order->id)->first();
                                
                                function getLog($history, $status) {
                                    return $history->firstWhere('status', $status);
                                }
                                
                                if ($order->is_delivery_order) {
                                    // 1. Customer Place Order (Appointment)
                                    $steps->push([
                                        'label' => 'Customer Order Placed',
                                        'icon' => '📅',
                                        'time' => $order->deliveryAppointment?->created_at,
                                        'user' => $order->customer->name,
                                        'active' => true
                                    ]);
                                    
                                    // 2. Order Confirm (Received)
                                    $steps->push([
                                        'label' => 'Order Confirmed',
                                        'icon' => '✅',
                                        'time' => $order->created_at,
                                        'user' => $order->createdBy?->name ?? 'System',
                                        'active' => true
                                    ]);
                                    
                                    // 3. Rider Assign (Task)
                                    $pickupTask = \App\Models\DeliveryTask::where('delivery_appointment_id', $order->delivery_appointment_id)->where('task_type', 'pickup')->first();
                                    $steps->push([
                                        'label' => 'Rider Assigned',
                                        'icon' => '🛵',
                                        'time' => $pickupTask?->created_at,
                                        'user' => $pickupTask?->rider?->name,
                                        'active' => $pickupTask != null || $order->deliveryAppointment?->delivery_method === 'pickme_flash'
                                    ]);
                                    
                                    // 4. Workshop Received
                                    $steps->push([
                                        'label' => 'Workshop Received',
                                        'icon' => '📥',
                                        'time' => $dispatch?->received_at_workshop_at,
                                        'user' => $dispatch?->receivedBy?->name,
                                        'active' => $dispatch?->received_at_workshop_at != null
                                    ]);
                                    
                                    // 5. Payment & Start (Processing)
                                    $firstPayment = $order->paymentTransactions()->orderBy('created_at')->first();
                                    $processingLog = getLog($history, 'processing');
                                    
                                    $payLabel = 'Payment & Processing Started';
                                    if ($firstPayment) {
                                        $payLabel .= ' (Paid Rs.' . number_format($firstPayment->amount, 0) . ' at ' . $firstPayment->created_at->format('H:i') . ')';
                                    }
                                    
                                    $steps->push([
                                        'label' => $payLabel,
                                        'icon' => '⚙️',
                                        'time' => $processingLog?->created_at ?? $firstPayment?->created_at,
                                        'user' => $processingLog?->changedBy?->name ?? 'Workshop Admin',
                                        'active' => $processingLog != null || $order->status === 'processing' || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                    ]);
                                    
                                    // 6. Done (Workshop Completed)
                                    $doneWorkshopLog = getLog($history, 'completed_at_workshop');
                                    $steps->push([
                                        'label' => 'Done',
                                        'icon' => '✨',
                                        'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                        'user' => $doneWorkshopLog?->changedBy?->name ?? 'Workshop Admin',
                                        'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                    ]);
                                    
                                    // 7. Pay Full Payment & Completed (Delivered)
                                    $completedLog = getLog($history, 'completed');
                                    $deliveredLog = getLog($history, 'delivered');
                                    $finalLog = $completedLog ?? $deliveredLog;

                                    $steps->push([
                                        'label' => 'Full Payment & Completed',
                                        'icon' => '🤝',
                                        'time' => $finalLog?->created_at ?? $order->delivered_at,
                                        'user' => $finalLog?->changedBy?->name ?? 'System',
                                        'active' => in_array($order->status, ['delivered', 'completed'])
                                    ]);
                                    
                                } else {
                                    // WALK-IN ORDER FLOW
                                    
                                    // 1. Order Received
                                    $steps->push([
                                        'label' => 'Order Received',
                                        'icon' => '📥',
                                        'time' => $order->created_at,
                                        'user' => $order->createdBy?->name ?? 'System',
                                        'active' => true
                                    ]);
                                    
                                    // 2. Transfer to Workshop
                                    $dispatchLog = getLog($history, 'dispatched_to_workshop');
                                    $steps->push([
                                        'label' => 'Transfer to Workshop',
                                        'icon' => '📤',
                                        'time' => $dispatchLog?->created_at ?? $dispatch?->dispatched_at,
                                        'user' => $dispatchLog?->changedBy?->name ?? $dispatch?->dispatchedBy?->name,
                                        'active' => $dispatch != null
                                    ]);
                                    
                                    // 3. Arrive Workshop & Washing (Processing)
                                    $arriveLog = getLog($history, 'received_at_workshop');
                                    $processingLog = getLog($history, 'processing');
                                    $steps->push([
                                        'label' => 'Arrive Workshop & Washing',
                                        'icon' => '⚙️',
                                        'time' => $arriveLog?->created_at ?? $processingLog?->created_at ?? $dispatch?->received_at_workshop_at,
                                        'user' => $arriveLog?->changedBy?->name ?? $processingLog?->changedBy?->name ?? $dispatch?->receivedBy?->name,
                                        'active' => $arriveLog != null || $processingLog != null || $dispatch?->received_at_workshop_at != null || in_array($order->status, ['processing', 'returned_from_workshop', 'done', 'delivered'])
                                    ]);
                                    
                                    // 4. Done (at Workshop)
                                    $doneWorkshopLog = getLog($history, 'completed_at_workshop');
                                    $steps->push([
                                        'label' => 'Done',
                                        'icon' => '✨',
                                        'time' => $doneWorkshopLog?->created_at ?? $dispatch?->completed_at_workshop_at,
                                        'user' => $doneWorkshopLog?->changedBy?->name ?? 'Workshop Admin',
                                        'active' => $doneWorkshopLog != null || $dispatch?->completed_at_workshop_at != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                    ]);
                                    
                                    // 5. Delivery to Branch (Returning)
                                    $returnLog = getLog($history, 'returned_from_workshop');
                                    $steps->push([
                                        'label' => 'Delivery to Branch',
                                        'icon' => '🚚',
                                        'time' => $returnLog?->created_at ?? $dispatch?->returned_at,
                                        'user' => $returnLog?->changedBy?->name ?? $dispatch?->returnedBy?->name,
                                        'active' => $returnLog != null || in_array($order->status, ['returned_from_workshop', 'done', 'delivered'])
                                    ]);
                                    
                                    // 6. Received to Branch (Done state)
                                    $doneLog = getLog($history, 'done');
                                    $steps->push([
                                        'label' => 'Received to Branch',
                                        'icon' => '🏢',
                                        'time' => $doneLog?->created_at ?? $dispatch?->received_at_branch_at,
                                        'user' => $doneLog?->changedBy?->name ?? 'Branch Admin',
                                        'active' => $doneLog != null || in_array($order->status, ['done', 'delivered'])
                                    ]);
                                    
                                    // 7. Delivered to Customer
                                    $completedLog = getLog($history, 'completed');
                                    $deliveredLog = getLog($history, 'delivered');
                                    $finalLog = $completedLog ?? $deliveredLog;
                                    
                                    $steps->push([
                                        'label' => 'Delivered to Customer',
                                        'icon' => '🤝',
                                        'time' => $finalLog?->created_at ?? $order->delivered_at,
                                        'user' => $finalLog?->changedBy?->name ?? 'System',
                                        'active' => in_array($order->status, ['delivered', 'completed'])
                                    ]);
                                }
                            @endphp

                            @foreach($steps as $step)
                            <div class="flex items-start gap-4 relative {{ !$step['active'] ? 'opacity-40 grayscale' : '' }}">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm z-10 flex-shrink-0 shadow-sm {{ $step['active'] ? 'bg-blue-600 text-white border-2 border-white ring-4 ring-blue-50' : 'bg-slate-200 text-slate-500 border-2 border-white' }}">
                                    {{ $step['icon'] }}
                                </div>
                                <div class="flex-1 pt-1.5 pb-5">
                                    <div class="flex items-baseline justify-between gap-2">
                                        <div class="text-[13px] font-black {{ $step['active'] ? 'text-slate-800' : 'text-slate-500' }}">{{ $step['label'] }}</div>
                                        @if($step['time'])
                                            <div class="text-[11px] font-bold whitespace-nowrap {{ $step['active'] ? 'text-blue-600' : 'text-slate-400' }}">{{ \Carbon\Carbon::parse($step['time'])->format('d M, H:i') }}</div>
                                        @endif
                                    </div>
                                    @if($step['active'] && $step['user'])
                                    <div class="text-[11px] text-slate-500 font-semibold mt-0.5">By {{ $step['user'] }}</div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Update Status Form -->
                @if($order->status !== 'completed' && $order->status !== 'cancelled')
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
                                        <option value="delivered">🚚 Delivered (Walk-in)</option>
                                        <option value="cancelled">❌ Cancel</option>
                                    @elseif($order->status === 'delivered')
                                        <option value="completed">💰 Settle & Complete</option>
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
                                    <option value="card">💳 Card</option>
                                    <option value="bank">🏦 Bank Transfer</option>
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
                                    if (payWrap) payWrap.style.display = (val === 'delivered' || val === 'completed') ? 'flex' : 'none';
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
                            <td style="padding:12px 16px;text-align:right;font-size:15px;font-weight:900;color:#1d4ed8;">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
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
                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">{{ $order->customer->name ?? 'Unknown Customer' }}</p>
                    </div>
                    <div>
                        <span style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Phone</span>
                        <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">{{ $order->customer->phone ?? 'N/A' }}</p>
                    </div>
                    @if(optional($order->customer)->address)
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
                        <span style="font-weight:800;color:#0f172a;">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#64748b;font-weight:600;">Paid Amount</span>
                        <span style="font-weight:800;color:#16a34a;">Rs.{{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    @php
                        $deliveryCost = 0;
                        $deliveryExpense = \App\Models\Expense::whereIn('category', ['delivery', 'PickMe', 'PickMe Flash'])
                            ->where(function($q) use ($order) {
                                $q->where('description', 'like', '%Order #' . $order->order_number . '%')
                                  ->orWhere('description', 'like', '%delivery dropoff #' . optional($order->deliveryAppointment)->id . '%');
                            })
                            ->latest()
                            ->first();
                        
                        if ($deliveryExpense) {
                            $deliveryCost = $deliveryExpense->amount;
                        }
                    @endphp
                    @if($deliveryCost > 0)
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#64748b;font-weight:600;">Delivery Cost (PickMe)</span>
                        <span style="font-weight:800;color:#0f172a;">Rs.{{ number_format($deliveryCost, 2) }}</span>
                    </div>
                    @endif
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

            <!-- Storage Location / Cubby -->
            @php
                $receipts = collect();
                if ($order->receipt_path) {
                    $receipts->push($order->receipt_path);
                }
                if ($order->deliveryAppointment && $order->deliveryAppointment->receipt_path) {
                    $receipts->push($order->deliveryAppointment->receipt_path);
                }
                $txReceipts = \App\Models\PaymentTransaction::where('order_id', $order->id)
                    ->whereNotNull('receipt_path')
                    ->pluck('receipt_path');
                
                foreach ($txReceipts as $tr) {
                    $receipts->push($tr);
                }
                
                $receipts = $receipts->unique()->values();
            @endphp

            @if($receipts->count() > 0)
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Payment Receipts</h3>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($receipts as $index => $path)
                    <a href="{{ asset('storage/' . $path) }}" target="_blank" style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;text-decoration:none;transition:all 0.15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                        <div style="width:32px;height:32px;background:#eef2ff;color:#4f46e5;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;">
                            <i class="fa-solid fa-file-invoice"></i>
                        </div>
                        <div style="flex:1;">
                            <p style="font-size:11px;font-weight:800;color:#0f172a;margin:0;">Receipt #{{ $index + 1 }}</p>
                            <p style="font-size:10px;color:#64748b;margin:0;font-weight:600;">Click to view</p>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square" style="color:#94a3b8;font-size:12px;"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($order->cubbies->count())
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 4px rgba(0,0,0,0.02);padding:16px;">
                <h3 style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">Storage</h3>
                @foreach($order->cubbies->sortByDesc('assigned_at') as $oc)
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;{{ $oc->released_at ? 'opacity:0.5;' : '' }}">
                        <div style="width:30px;height:30px;background:#eef2ff;color:#4f46e5;border-radius:8px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;">
                            {{ $oc->cubby->name ?? '?' }}
                        </div>
                        <div style="flex:1;">
                            <p style="font-size:12px;font-weight:800;color:#0f172a;margin:0;">Cubby: {{ $oc->cubby->name ?? 'N/A' }}</p>
                            <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:600;">
                                Assigned: {{ \Carbon\Carbon::parse($oc->assigned_at)->format('M d, h:i A') }}
                                &bull; Loc: {{ $oc->cubby->branch->name ?? 'Unknown' }}
                            </p>
                        </div>
                        @if($oc->released_at)
                            <span style="font-size:9px;font-weight:700;color:#94a3b8;background:#f1f5f9;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Released</span>
                        @else
                            <span style="font-size:9px;font-weight:800;color:#16a34a;background:#f0fdf4;padding:2px 6px;border-radius:4px;text-transform:uppercase;">Active</span>
                        @endif
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
