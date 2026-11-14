<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #{{ $order->order_number }}</title>
    @php
        if (empty($order->payment_token)) {
            $order->payment_token = 'inv_' . bin2hex(random_bytes(16));
            $order->saveQuietly();
        }

        $layout = $layout ?? \App\Models\Central\TenantPrintLayout::getForTenant(tenant('id'), 'invoice');
        $cfg = $layout->custom_fields ?? [];
        $showLogo = $layout->show_logo;
        $logoSize = $cfg['logo_size'] ?? '60';
        $companyNameSize = $cfg['company_name_size'] ?? '24px';
        $invoiceTitle = $cfg['invoice_title'] ?? 'INVOICE';
        $invoiceTitleSize = $cfg['invoice_title_size'] ?? '28px';
        $primaryColor = $layout->primary_color ?: '#0f172a';
        $accentColor = $layout->accent_color ?: '#3b82f6';
        $fontFamily = $layout->font_family ?: "'Inter', sans-serif";
        $fontSize = $layout->font_size ?: '13px';

        $headerTitle = $layout->header_title ?: (\App\Models\Setting::get('company_name', 'Laundry Zone'));
        $headerSubtitle = $layout->header_subtitle ?: ($order->branch->name ?? 'Main Branch');
        $contactPhone = $cfg['contact_phone'] ?? ($order->branch->phone ?? '011 234 5678');
        $contactEmail = $cfg['contact_email'] ?? ($mainBranch->email ?? 'contact@company.com');
        $contactAddress = $cfg['contact_address'] ?? ($order->branch->address ?? 'Colombo, Sri Lanka');
        
        $badgeTextPaid = $cfg['badge_text_paid'] ?? 'FULLY PAID';
        $badgeTextPending = $cfg['badge_text_pending'] ?? 'PAYMENT PENDING';
        $showPayOnlineBtn = ($cfg['show_pay_online_btn'] ?? '1') == '1';
        $payBtnText = $cfg['pay_btn_text'] ?? 'PAY NOW - CLICK HERE';
        $termsText = $layout->terms_and_conditions ?: ($mainBranch?->invoice_terms ?? '');
        $footerText = $layout->footer_text ?: ($mainBranch?->invoice_footer ?? 'Thank you for your business! 🤍');
        $footerNote = $cfg['footer_note'] ?? 'Please visit our website for full digital terms & conditions.';
    @endphp
    @include('orders.partials.invoice.styles', [
        'fontFamily' => $fontFamily,
        'fontSize' => $fontSize,
        'primaryColor' => $primaryColor,
        'accentColor' => $accentColor,
        'companyNameSize' => $companyNameSize,
        'invoiceTitleSize' => $invoiceTitleSize
    ])
</head>
<body>
    
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; display: flex; gap: 10px;">
        <button onclick="window.print()" style="background: {{ $accentColor }}; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.3);">🖨 Print / Save as PDF</button>
        <button onclick="window.close()" style="background: white; color: #475569; border: 1px solid #cbd5e1; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer;">Close</button>
    </div>

    <div class="invoice-box">
        <div class="header-shape"></div>
        
        <div class="header">
            <div class="logo-section">
                @if($showLogo)
                    @php
                        $tenant = \App\Services\Tenancy\TenantManager::getTenant();
                        $logoUrl = $tenant && $tenant->logo_path ? asset($tenant->logo_path) : ($mainBranch && $mainBranch->logo_path ? asset($mainBranch->logo_path) : asset('images/laundry_main_logo.png'));
                    @endphp
                    <img src="{{ $logoUrl }}" alt="Logo" style="max-height: {{ $logoSize }}px; max-width: {{ $logoSize }}px; height: auto; margin-bottom: 12px; display: block; object-fit: contain;" onerror="this.style.display='none'">
                @endif
                <h1>{{ $headerTitle }}</h1>
                @if($headerSubtitle)<p>{{ $headerSubtitle }}</p>@endif
                @if($contactPhone)<p>{{ $contactPhone }}</p>@endif
                @if($contactEmail)<p>{{ $contactEmail }}</p>@endif
                @if($contactAddress)<p>{{ $contactAddress }}</p>@endif
            </div>
            <div class="invoice-title">
                <h2>{{ $invoiceTitle }}</h2>
                <p style="color: {{ $accentColor }}; font-weight: 900; font-size: 16px;">Invoice Number: #{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h3>Bill To</h3>
                <p>{{ trim(($order->customer->title ? $order->customer->title . ' ' : '') . ($order->customer->name ?? 'Walk-in Customer')) }}</p>
                <span>{{ $order->customer->phone ?? 'No Phone' }}</span>
                <span>{{ $order->customer->email ?? '' }}</span>
                @if($order->is_delivery_order && $order->delivery_address)
                    <span>{{ $order->delivery_address }}</span>
                @elseif($order->customer && $order->customer->address)
                    <span>{{ $order->customer->address }}</span>
                @endif
            </div>
            <div class="info-box">
                <h3>Order Details</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color:#0f172a; font-weight:700;">Order Create Date:</span>
                    <span style="color:#475569;">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color:#0f172a; font-weight:700;">Status:</span>
                    <span style="color:#475569; text-transform: capitalize;">{{ str_replace('_', ' ', $order->status) }}</span>
                </div>
                @if($order->is_delivery_order && $order->deliveryAppointment)
                    @if($order->deliveryAppointment->pickup_scheduled_at)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color:#0f172a; font-weight:700;">Pickup Scheduled Date:</span>
                        <span style="color:#475569;">{{ \Carbon\Carbon::parse($order->deliveryAppointment->pickup_scheduled_at)->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif
                    @if($order->deliveryAppointment->delivery_scheduled_at)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="color:#0f172a; font-weight:700;">Delivery Date:</span>
                        <span style="color:#475569;">{{ \Carbon\Carbon::parse($order->deliveryAppointment->delivery_scheduled_at)->format('M d, Y h:i A') }}</span>
                    </div>
                    @endif
                @elseif($order->due_date)
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color:#0f172a; font-weight:700;">Delivery Date:</span>
                    <span style="color:#475569;">{{ \Carbon\Carbon::parse($order->due_date)->format('M d, Y h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>

        @include('orders.partials.invoice.items_table', [
            'order' => $order,
            'accentColor' => $accentColor
        ])

        <div class="totals-section">
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rs.{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->delivery_fee > 0)
                <div class="total-row">
                    <span>Delivery Fee</span>
                    <span>Rs.{{ number_format($order->delivery_fee, 2) }}</span>
                </div>
                @endif
                @if($order->additional > 0)
                <div class="total-row">
                    <span>Additional Charges</span>
                    <span>Rs.{{ number_format($order->additional, 2) }}</span>
                </div>
                @endif
                @if($order->discount > 0)
                <div class="total-row" style="color: #ef4444;">
                    @php
                        $discountPct = ($order->subtotal > 0) ? round(($order->discount / $order->subtotal) * 100, 1) : 0;
                    @endphp
                    <span>Discount {{ $discountPct > 0 ? '(' . $discountPct . '%)' : '' }}</span>
                    <span>- Rs.{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total Amount</span>
                    <span>Rs.{{ number_format($order->total, 2) }}</span>
                </div>
                <div class="total-row paid">
                    <span>Amount Paid</span>
                    <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
                </div>
                @if($order->total - $order->paid_amount > 0)
                <div class="total-row balance">
                    <span>Balance Due</span>
                    <span>Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <div style="margin-top: 30px; font-size: 11px; color: #64748b; line-height: 1.6;">
            @if($termsText)
                <strong style="color: #0f172a; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; display: block; margin-bottom: 4px;">Terms & Conditions</strong>
                {!! nl2br(e($termsText)) !!}
            @endif
        </div>

        <div class="footer">
            @if($order->paid_amount >= $order->total && $order->total > 0)
                <div class="badge" style="margin-bottom: 8px;">{{ $badgeTextPaid }}</div>
            @elseif($order->total - $order->paid_amount > 0)
                <div class="badge pending" style="margin-bottom: 8px;">{{ $order->payment_method === 'credit' ? 'CREDIT' : $badgeTextPending }}</div>
            @endif

            <p>{!! nl2br(e($footerText)) !!}</p>
            <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
                <strong style="text-transform: uppercase; letter-spacing: 0.1em; color: #64748b;">Terms & Conditions:</strong> 
                {{ $footerNote }} <a href="{{ url('/terms') }}" style="color: {{ $accentColor }}; text-decoration: none; font-weight: bold;">{{ str_replace(['http://', 'https://'], '', url('/terms')) }}</a>
            </div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
