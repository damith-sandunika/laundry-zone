                        <tfoot>
                            <tr>
                                <td colspan="3"
                                    style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">
                                    Subtotal</td>
                                <td style="padding:8px 16px;text-align:right;font-weight:700;color:#334155;">
                                    Rs.{{ number_format($cartSubtotal, 2) }}</td>
                            </tr>
                            @if($order->additional > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:12px;font-weight:700;color:#64748b;">
                                        Additional Charges</td>
                                    <td
                                        style="padding:8px 16px;text-align:right;font-size:13px;font-weight:800;color:#dc2626;">
                                        + Rs.{{ number_format($order->additional, 2) }}</td>
                                </tr>
                            @endif
                            @if($order->delivery_fee > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:12px;font-weight:700;color:#64748b;">
                                        Delivery Fee</td>
                                    <td
                                        style="padding:8px 16px;text-align:right;font-size:13px;font-weight:800;color:#0f172a;">
                                        + Rs.{{ number_format($order->delivery_fee, 2) }}</td>
                                </tr>
                            @endif
                            @if($order->discount > 0)
                                <tr>
                                    <td colspan="3"
                                        style="padding:8px 16px;text-align:right;font-size:11px;font-weight:600;color:#64748b;">
                                        Discount</td>
                                    <td style="padding:8px 16px;text-align:right;font-weight:700;color:#dc2626;">-
                                        Rs.{{ number_format($order->discount, 2) }}</td>
                                </tr>
                            @endif
                            <tr style="background:#f8fafc;border-top:1.5px solid #e2e8f0;">
                                <td colspan="3"
                                    style="padding:12px 16px;text-align:right;font-size:12px;font-weight:800;color:#0f172a;">
                                    Total</td>
                                @php
                                    $displayTotal = $order->total;
                                    if ($order->items->count() === 0 && $cartSubtotal > 0) {
                                        $displayTotal = $cartSubtotal + ($order->delivery_fee ?? 0) - ($order->discount ?? 0);
                                    }
                                @endphp
                                <td
                                    style="padding:12px 16px;text-align:right;font-size:15px;font-weight:900;color:#1d4ed8;">
                                    {{ $order->isPackageOrder() && $displayTotal == 0 ? 'Package' : 'Rs.' . number_format($displayTotal, 2) }}
                                </td>
                            </tr>
                        </tfoot>
