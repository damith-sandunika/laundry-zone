<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $order->order_number }}</title>
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
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 12px;
            width: 74mm;
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
            font-weight: 800;
        }

        .uppercase {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 11px;
        }

        .divider {
            border-top: 1.5px dashed #000;
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
            font-weight: 900;
            font-size: 15px;
            border-top: 2px solid #000;
            padding-top: 6px;
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            border: 1.5px solid #000;
            padding: 4px 10px;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 800;
            border-radius: 6px;
        }

        .item-name {
            font-weight: 700;
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
                width: 74mm;
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
    <div class="center" style="margin-bottom: 12px;">
        @if($mainBranch && $mainBranch->logo_path)
            <img src="{{ asset($mainBranch->logo_path) }}" alt="Logo"
                style="max-width: {{ $mainBranch->logo_size ?? 140 }}px; height: auto;" onerror="this.style.display='none'">
        @endif
    </div>

    @if($branch)
        <div class="center bold" style="font-size:18px; letter-spacing:-0.03em; margin-bottom: 2px;">{{ $branch->name }}
        </div>
    @endif

    @if($mainBranch && $mainBranch->tagline)
        <div class="center" style="font-size:12px; margin-bottom:2px;">{{ $mainBranch->tagline }}</div>
    @endif
    @if($branch && $branch->phone)
        <div class="center" style="font-size:12px;">{{ $branch->phone }}</div>
    @endif
    @if($branch && $branch->address)
        <div class="center" style="font-size:12px;">{{ $branch->address }}</div>
    @endif

    @if($mainBranch && $mainBranch->invoice_header)
        <div class="center" style="font-size:12px; margin-top:4px; font-style:italic;">
            {!! nl2br(e($mainBranch->invoice_header)) !!}
        </div>
    @endif

    <div class="divider"></div>

    <div class="row" style="font-size: 12px;"><span>Bill No:</span><span class="bold">{{ $order->order_number }}</span>
    </div>
    <div class="row" style="font-size: 12px;"><span>Date:</span><span
            class="bold">{{ $order->created_at->format('d M Y h:i A') }}</span></div>
    @if($order->due_date)
        <div class="row" style="font-size: 12px;"><span>Pickup Date:</span><span
                class="bold">{{ \Carbon\Carbon::parse($order->due_date)->format('d M Y h:i A') }}</span></div>
    @endif
    <div class="row" style="font-size: 12px;"><span>Customer:</span><span
            class="bold">{{ $order->customer->title ? $order->customer->title . ' ' : '' }}{{ $order->customer->name }}</span>
    </div>
    <div class="row" style="font-size: 12px;"><span>Phone:</span><span class="bold">{{ $order->customer->phone }}</span>
    </div>

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
            <span class="bold">Rs.{{ number_format($item->total_price, 2) }}</span>
        </div>
        <div style="font-size: 11px; margin-bottom: 2px; color:#475569;">
            {{ $item->quantity }} x Rs.{{ number_format($unit_with_turnaround, 2) }}
            @if($item->turnaroundType && !$item->turnaroundType->is_default)
                ({{ $item->turnaroundType->name }})
            @endif
        </div>
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

    @if($order->discount > 0 || $order->delivery_fee > 0 || $order->additional > 0)
        <div class="row"><span>Subtotal</span><span>Rs.{{ number_format($order->subtotal, 2) }}</span></div>
        @if($order->discount > 0)
            <div class="row"><span>Discount</span><span>- Rs.{{ number_format($order->discount, 2) }}</span></div>
        @endif
        @if($order->delivery_fee > 0)
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

    <div class="row" style="margin-top:4px;">
        <span>Paid</span>
        <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
    </div>
    @if($order->total - $order->paid_amount > 0)
        <div class="row bold">
            <span>Balance Due</span>
            <span>Rs.{{ number_format($order->total - $order->paid_amount, 2) }}</span>
        </div>
    @endif

    <div class="divider"></div>

    <div class="center" style="margin: 8px 0;">
        <span class="badge">
            @if($order->paid_amount >= $order->total) ✓ PAID IN FULL @elseif($order->payment_method === 'credit') ⏳
            CREDIT @else 🕒 PAY ON PICKUP @endif
        </span>
    </div>

    <div class="divider"></div>

    @if($mainBranch && $mainBranch->invoice_terms)
        <div style="font-size:10px; margin-top:8px; color:#333; text-align:justify;">
            <div class="bold center" style="margin-bottom:2px;">Terms & Conditions</div>
            {!! nl2br(e($mainBranch->invoice_terms)) !!}
        </div>
        <div class="divider"></div>
    @endif

    <div style="font-size:10px; margin-top:8px; margin-bottom:8px; color:#333; text-align:center;">
        <div class="bold center" style="margin-bottom:2px;">Terms & Conditions</div>
        Read our full terms at <br><strong style="font-size:11px;">365laundry.lk/terms</strong>
    </div>
    <div class="divider"></div>

    @if($mainBranch && $mainBranch->invoice_footer)
        <div class="center bold" style="font-size:12px; margin-top:8px;">{!! nl2br(e($mainBranch->invoice_footer)) !!}</div>
    @else
        <div class="center bold" style="font-size:12px; margin-top:8px;">Thank you for choosing 365laundry.lk.</div>
        <div class="center" style="font-size:10px; margin-top:3px; color:#333;">If you are happy with our service, please
            leave us a Google Review!</div>
    @endif

    <div class="center" style="font-size:10px; margin-top:12px; color:#666;">
        Software by DSRZone
    </div>

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
    <script>window.onload = function () { window.print(); }</script>
</body>

</html>