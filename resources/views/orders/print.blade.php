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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 0;
            size: 80mm auto;
        }

        body {
            font-family: {!! $fontFamily !!};
            font-size: {{ $fontSize }};
            width: {{ $paperWidth }};
            max-width: 100%;
            margin: 0 auto;
            padding: 4mm 3mm;
            color: #000;
            background: #fff;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 600;
        }

        .uppercase {
            text-transform: uppercase;
            letter-spacing: 0.03em;
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #64748b;
            margin: 8px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            gap: 4px;
        }

        .row span:first-child {
            flex: 1;
            text-align: left;
            word-break: break-word;
        }

        .row span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .total-row {
            font-weight: 700;
            font-size: 14px;
            border-top: 1.5px solid #0f172a;
            padding-top: 6px;
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            border: 1px solid #0f172a;
            padding: 4px 10px;
            margin-top: 6px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
        }

        .item-name {
            font-weight: 600;
            font-size: 12px;
            display: block;
            word-break: break-word;
        }

        .item-details {
            font-size: 11px;
            color: #475569;
            padding-left: 2px;
        }

        @media print {
            body {
                width: {{ $paperWidth }};
                padding: 2mm 3mm;
                margin: 0 auto;
            }

            .no-print {
                display: none;
            }
        }
    </style>
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

    <div class="divider"></div>
    @foreach($order->items as $item)
        @php
            $unit_with_turnaround = $item->quantity > 0 ? ($item->total_price / $item->quantity) : $item->total_price;
        @endphp
        <div class="row" style="margin-bottom: 2px;">
            <span class="item-name">
                @if($item->serviceType && $item->serviceType->name !== 'Special Service')
                    @if($item->serviceType->category)
                        <div style="font-size: 9px; color: #475569; font-weight: 500; margin-bottom: 1px;">
                            {{ $item->serviceType->category->parent ? $item->serviceType->category->parent->name . ' > ' : '' }}{{ $item->serviceType->category->name }}
                        </div>
                    @endif
                    <div style="font-size: 13px; font-weight: 800;">{{ $item->serviceType->name }}</div>
                @else
                    <div style="font-size: 13px; font-weight: 800;">{{ $item->item_description }}</div>
                @endif
            </span>
            <span class="bold">Rs.{{ number_format($item->unit_price * max(1, $item->quantity), 2) }}</span>
        </div>
        <div style="font-size: 11px; margin-bottom: 2px; color:#475569;">
            {{ $item->quantity }} x Rs.{{ number_format($item->unit_price, 2) }}
            @if($item->turnaroundType && !$item->turnaroundType->is_default)
                ({{ $item->turnaroundType->name }})
            @elseif((float) $item->custom_turnaround_percentage > 0)
                (Express ({{ (float) $item->custom_turnaround_percentage }}%))
            @endif
        </div>
        @if($item->turnaround_extra_charge > 0)
            <div class="row" style="margin-bottom: 2px; padding-left: 10px; font-size:11px; color:#d97706;">
                <span>⚡ Express Surcharge</span>
                <span class="bold">+Rs.{{ number_format($item->turnaround_extra_charge, 2) }}</span>
            </div>
        @endif
        @if($item->extras->count() > 0)
            @foreach($item->extras as $e)
                <div class="row" style="margin-bottom: 2px; padding-left: 10px; font-size:11px;">
                    <span>+ {{ $e->extra->name }} (x{{ $e->quantity }})</span>
                    <span class="bold">Rs.{{ number_format($e->unit_price * $e->quantity, 2) }}</span>
                </div>
            @endforeach
        @endif
        <div style="margin-bottom: 6px;"></div>
    @endforeach

    <div class="divider"></div>

    @if(($showDiscount && $order->discount > 0) || ($showDeliveryFee && $order->delivery_fee > 0) || $order->additional > 0)
        <div class="row"><span>Subtotal</span><span>Rs.{{ number_format($order->subtotal, 2) }}</span></div>
        @if($showDiscount && $order->discount > 0)
            @php $discountPct = ($order->subtotal > 0) ? round(($order->discount / $order->subtotal) * 100, 1) : 0; @endphp
            <div class="row"><span>Discount{{ $discountPct > 0 ? ' (' . $discountPct . '%)' : '' }}</span><span>-
                    Rs.{{ number_format($order->discount, 2) }}</span></div>
        @endif
        @if($showDeliveryFee && $order->delivery_fee > 0)
            <div class="row"><span>Delivery Fee</span><span>Rs.{{ number_format($order->delivery_fee, 2) }}</span></div>
        @endif
        @if($order->additional > 0)
            <div class="row"><span>Additional Charges</span><span>Rs.{{ number_format($order->additional, 2) }}</span></div>
        @endif
    @endif

    <div class="row total-row">
        <span>TOTAL</span>
        <span>{{ $order->total == 0 && $order->isPackageOrder() ? 'Package' : 'Rs.' . number_format($order->total, 2) }}</span>
    </div>

    @php
        $refundAmount = abs($order->paymentTransactions()->where('amount', '<', 0)->sum('amount') ?? 0);
        $totalPaidByCustomer = $order->paid_amount + $refundAmount;
    @endphp

    <div class="row" style="margin-top:4px;">
        <span>Amount Received</span>
        <span>Rs.{{ number_format($totalPaidByCustomer, 2) }}</span>
    </div>
    @if($order->total - $order->paid_amount > 0)
        <div class="row bold">
            <span>Balance Due</span>
            <span>Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</span>
        </div>
    @endif
    @if($totalPaidByCustomer > $order->total)
        @if($refundAmount > 0)
            <div class="row bold" style="color: #000000ff;">
                <span>Change Returned</span>
                <span>Rs.{{ number_format($refundAmount, 2) }}</span>
            </div>
        @else
            <div class="row bold" style="color: #000000ff;">
                <span>Change to Return</span>
                <span>Rs.{{ number_format($totalPaidByCustomer - $order->total, 2) }}</span>
            </div>
        @endif
    @endif

    <div class="divider"></div>

    <div class="center" style="margin: 8px 0;">
        <span class="badge">
            @if($order->paid_amount >= $order->total)
                {{ $badgeTextPaid }}
            @elseif($order->payment_method === 'credit')
                {{ $badgeTextCredit }}
            @else
                {{ $badgeTextPickup }}
            @endif
        </span>
    </div>

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
