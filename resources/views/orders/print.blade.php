<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $order->order_number }}</title>
    @php
        $layout = $layout ?? \App\Models\Central\TenantPrintLayout::getForTenant(tenant('id'), 'bill');
        $cfg = $layout->custom_fields ?? [];
        $paperWidth = $cfg['paper_width'] ?? '300px';
        $fontFamily = $layout->font_family ?: "'Inter', system-ui, -apple-system, sans-serif";
        $fontSize = $layout->font_size ?: '12px';
        $companyNameSize = $cfg['company_name_size'] ?? '18px';
        $showLogo = $layout->show_logo;
        $logoSize = $cfg['logo_size'] ?? '50';
        $headerTitle = $layout->header_title ?: ($branch?->name ?? 'Laundry Zone');
        $headerSubtitle = $layout->header_subtitle ?: ($mainBranch?->tagline ?? '');
        $branchPhone = $cfg['branch_phone'] ?? ($branch?->phone ?? '');
        $branchAddress = $cfg['branch_address'] ?? ($branch?->address ?? '');
        $showDueDate = ($cfg['show_due_date'] ?? '1') == '1';
        $showDeliveryFee = ($cfg['show_delivery_fee'] ?? '1') == '1';
        $showDiscount = ($cfg['show_discount'] ?? '1') == '1';
        $badgeTextPaid = $cfg['badge_text_paid'] ?? '✓ PAID IN FULL';
        $badgeTextCredit = $cfg['badge_text_credit'] ?? '⏳ CREDIT';
        $badgeTextPickup = $cfg['badge_text_pickup'] ?? '🕒 PAY ON PICKUP';
        $termsText = $layout->terms_and_conditions ?: ($mainBranch?->invoice_terms ?? '');
        $footerText = $layout->footer_text ?: ($mainBranch?->invoice_footer ?? 'Thank you for choosing ' . ($branch?->name ?? 'Laundry Zone') . '.');
        $reviewText = $cfg['review_text'] ?? 'If you are happy with our service, please leave us a Google Review!';
        $softwareBranding = $cfg['software_branding'] ?? 'Software by DSRZone';
        $billNotice = $cfg['bill_notice'] ?? '';
        $customExtraTexts = $cfg['custom_extra_texts'] ?? [];
        if (!is_array($customExtraTexts)) {
            $customExtraTexts = [];
        }
    @endphp
    @include('orders.partials.print.styles', [
        'fontFamily' => $fontFamily,
        'fontSize' => $fontSize,
        'paperWidth' => $paperWidth
    ])
</head>

<body>
    @if($showLogo)
        <div class="center" style="margin-bottom: 8px;">
            @php
                $tenant = \App\Services\Tenancy\TenantManager::getTenant();
                $logoUrl = $tenant && $tenant->logo_path ? asset($tenant->logo_path) : ($mainBranch && $mainBranch->logo_path ? asset($mainBranch->logo_path) : asset('images/laundry_main_logo.png'));
            @endphp
            <img src="{{ $logoUrl }}" alt="Logo"
                style="max-height: {{ $logoSize }}px; max-width: {{ $logoSize }}px; object-fit: contain;" onerror="this.style.display='none'">
        </div>
    @endif

    <div class="center bold" style="font-size: {{ $companyNameSize }}; letter-spacing:-0.03em; margin-bottom: 2px;">
        {{ $headerTitle }}
    </div>

    @if($headerSubtitle)
        <div class="center" style="font-size:12px; margin-bottom:2px;">{{ $headerSubtitle }}</div>
    @endif
    @if($branchPhone)
        <div class="center" style="font-size:12px;">{{ $branchPhone }}</div>
    @endif
    @if($branchAddress)
        <div class="center" style="font-size:12px;">{{ $branchAddress }}</div>
    @endif

    <div class="divider"></div>

    <div class="row" style="font-size: 12px;"><span>Bill No:</span><span class="bold">{{ $order->order_number }}</span>
    </div>
    <div class="row" style="font-size: 12px;"><span>Order Create Date:</span><span
            class="bold">{{ $order->created_at->format('d M Y h:i A') }}</span></div>
    @if($showDueDate && $order->due_date)
        <div class="row" style="font-size: 12px;"><span>Delivery
                Date:</span><span>{{ \Carbon\Carbon::parse($order->due_date)->format('M d, Y h:i A') }}</span></div>
    @endif
    <div class="row" style="font-size: 12px;"><span>Customer:</span><span
            class="bold">{{ $order->customer->title ? $order->customer->title . ' ' : '' }}{{ $order->customer->name }}</span>
    </div>
    <div class="row" style="font-size: 12px;"><span>Phone:</span><span class="bold">{{ $order->customer->phone }}</span>
    </div>

    @if($billNotice)
        <div class="center bold" style="margin: 6px 0; padding: 4px; border: 1px dashed #000; font-size: 11px;">
            {{ $billNotice }}
        </div>
    @endif

    @include('orders.partials.print.items_list', ['order' => $order])

    @include('orders.partials.print.totals_badge', [
        'order' => $order,
        'showDiscount' => $showDiscount,
        'showDeliveryFee' => $showDeliveryFee,
        'badgeTextPaid' => $badgeTextPaid,
        'badgeTextCredit' => $badgeTextCredit,
        'badgeTextPickup' => $badgeTextPickup
    ])

    @if(!empty($customExtraTexts))
        <div class="divider"></div>
        <div style="margin: 6px 0; font-size: 11px;">
            @foreach($customExtraTexts as $extraLine)
                @if(!empty($extraLine['text']))
                    <div class="center bold" style="margin-bottom: 3px;">{{ $extraLine['text'] }}</div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="divider"></div>

    @if($termsText)
        <div style="font-size:10px; margin-top:8px; color:#333; text-align:justify;">
            <div class="bold center" style="margin-bottom:2px;">Terms & Conditions</div>
            {!! nl2br(e($termsText)) !!}
        </div>
        <div class="divider"></div>
    @endif

    <div class="center bold" style="font-size:12px; margin-top:8px;">{!! nl2br(e($footerText)) !!}</div>
    @if($reviewText)
        <div class="center" style="font-size:10px; margin-top:3px; color:#333;">{{ $reviewText }}</div>
    @endif

    @if($softwareBranding)
        <div class="center" style="font-size:10px; margin-top:12px; color:#666;">
            {{ $softwareBranding }}
        </div>
    @endif

    <div class="no-print" style="margin-top:16px; text-align:center;">
        <button onclick="window.print()"
            style="padding:8px 20px; background:#2563eb; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:13px;">
            🖨 Print
        </button>
        <button onclick="window.close()"
            style="padding:8px 20px; background:#e5e7eb; color:#374151; border:none; border-radius:4px; cursor:pointer; font-size:13px; margin-left:8px;">
            Close
        </button>
    </div>
    <script>
        window.onload = function () {
            if (window.location.search.includes('auto_print=1')) {
                setTimeout(function () { window.print(); }, 500);
            }
        }
    </script>
</body>

</html>
