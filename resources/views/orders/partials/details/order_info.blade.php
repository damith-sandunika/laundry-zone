@if($order->is_delivery_order && $order->deliveryAppointment)
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                                <div>
                                    <h3
                                        style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                        Logistics & Delivery</h3>
                                    <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;">
                                        <div>
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup
                                                Address</span>
                                            <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                {{ $order->deliveryAppointment->pickup_address ?? 'N/A' }}
                                            </p>
                                        </div>
                                        @if($order->deliveryAppointment->pickup_scheduled_at)
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Pickup
                                                    Scheduled Date</span>
                                                <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                    {{ \Carbon\Carbon::parse($order->deliveryAppointment->pickup_scheduled_at)->format('M d, Y h:i A') }}
                                                </p>
                                            </div>
                                        @endif
                                        <div style="margin-top:4px;">
                                            <span
                                                style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                Preference</span>
                                            <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                @if(in_array($order->deliveryAppointment->delivery_preference, ['pickup_from_branch', 'pickup_from_store', 'self_pickup']))
                                                    Customer Pickup from Branch
                                                @else
                                                    Deliver to Address
                                                @endif
                                            </p>
                                        </div>
                                        @if(!in_array($order->deliveryAppointment->delivery_preference, ['pickup_from_branch', 'pickup_from_store', 'self_pickup']))
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                    Address</span>
                                                <p style="font-weight:600;color:#334155;margin:1px 0 0;line-height:1.3;">
                                                    {{ $order->deliveryAppointment->delivery_address ?: ($order->deliveryAppointment->pickup_address ?? 'N/A') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if($order->deliveryAppointment->delivery_scheduled_at)
                                            <div>
                                                <span
                                                    style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">Delivery
                                                    Scheduled</span>
                                                <p style="font-weight:700;color:#0f172a;margin:1px 0 0;">
                                                    {{ \Carbon\Carbon::parse($order->deliveryAppointment->delivery_scheduled_at)->format('M d, Y h:i A') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                            @else
                                <hr style="border:0;border-top:1px solid #f1f5f9;margin:4px 0;">
                            @endif

                            <!-- Payment Info -->
                            <div>
                                <h3
                                    style="font-size:12px;font-weight:800;color:#0f172a;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                                    Payment</h3>
                                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span style="color:#64748b;font-weight:600;">Total Bill</span>
                                        <span
                                            style="font-weight:800;color:#0f172a;">{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;">
                                        <span style="color:#64748b;font-weight:600;">Paid Amount</span>
                                        <span
                                            style="font-weight:800;color:#16a34a;">Rs.{{ number_format($order->paid_amount, 2) }}</span>
                                    </div>
                                    @if($pickmeCost > 0)
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <span style="color:#64748b;font-weight:600;">PickMe Expense (Shop Paid)</span>
                                            <span style="font-weight:800;color:#dc2626;">-
                                                Rs.{{ number_format($pickmeCost, 2) }}</span>
                                        </div>
                                    @endif
                                    <div
                                        style="display:flex;justify-content:space-between;align-items:center;padding-bottom:6px;border-bottom:1px solid #f1f5f9;">
                                        <span style="color:#64748b;font-weight:600;">Balance Due</span>
                                        <span
                                            style="font-weight:900;color:#dc2626;">Rs.{{ number_format(max(0, $order->total - $order->paid_amount), 2) }}</span>
                                    </div>
                                    <div
                                        style="display:flex;justify-content:space-between;align-items:center;margin-top:2px;">
                                        <span style="color:#64748b;font-weight:600;">Status</span>
                                        <span
                                            style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;
                                            {{ $order->payment_status === 'paid' ? 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;' : 'background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;' }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </div>
                                    @if($order->payment_method)
                                        <div style="display:flex;justify-content:space-between;align-items:center;">
                                            <span style="color:#64748b;font-weight:600;">Method</span>
                                            <span
                                                style="font-weight:700;color:#334155;text-transform:capitalize;">{{ $order->payment_method }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
