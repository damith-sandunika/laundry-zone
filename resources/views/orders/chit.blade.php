<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Print Bag Chit</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 10px;
            width: 58mm;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .text-bold {
            font-weight: 900;
            font-size: 18px;
            margin: 3px 0;
        }

        .text-small {
            font-weight: bold;
            font-size: 11px;
            margin: 3px 0;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .date {
            font-weight: 900;
            font-size: 14px;
            margin: 5px 0 10px 0;
            text-align: center;
            text-transform: uppercase;
        }

        .items {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 12px;
            margin-top: 5px;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin-bottom: 15px;
        }

        .process-list {
            margin-top: 15px;
            font-size: 12px;
            font-weight: bold;
            line-height: 2;
        }

        .special-note {
            margin-top: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .note-line {
            border-bottom: 1px dotted #000;
            height: 18px;
            margin-top: 5px;
        }

        @media print {
            @page {
                margin: 0;
            }

            body {
                margin: 0;
                padding: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="text-bold text-center">{{ $order->order_number }}</div>
        <div class="text-small">
            {{ strtoupper(($order->customer->title ? $order->customer->title . ' ' : '') . ($order->customer->name ?? 'Walk-in')) }}
            @if($order->customer && $order->customer->phone)
                <br>{{ $order->customer->phone }}
            @endif
        </div>
    </div>

    <div class="date">{{ $order->due_date ? $order->due_date->format('d M Y h:i A') : 'N/A' }}</div>

    <div class="items">
        @foreach($order->items as $item)
            @php
                $catName = $item->serviceType && $item->serviceType->category ? $item->serviceType->category->name : 'General';
                $itemName = $item->serviceType ? $item->serviceType->name : $item->item_description;
            @endphp
            <div class="item-row" style="margin-bottom: 2px;">
                <span>&bull; {{ $catName }} - {{ $itemName }}</span>
                <span>x{{ $item->quantity }}</span>
            </div>
            @if($item->turnaroundType)
                <div style="font-size:10px; margin-left:10px; margin-bottom: 2px;">[{{ $item->turnaroundType->name }}]</div>
            @endif
            @if($item->extras->count() > 0)
                @foreach($item->extras as $e)
                <div style="font-size:10px; margin-left:10px; margin-bottom: 2px;">+ {{ $e->extra->name }} (x{{ $e->quantity }})</div>
                @endforeach
            @endif
        @endforeach
    </div>

    <div class="total-row">
        <span>TOTAL</span>
        <span>Rs. {{ number_format($order->total, 2) }}</span>
    </div>

    <div class="special-note">
        <div>Special Note :</div>
        <div class="note-line"></div>
        <div class="note-line"></div>
    </div>

    <div class="process-list">
        <div>Collected :</div>
        <div>Wash :</div>
        <div>Iron :</div>
        <div>Quality check :</div>
        <div>Final inspection :</div>
    </div>

    <script>
        window.onload = function () {
            window.print();
            setTimeout(() => { window.close(); }, 500);
        }
    </script>
</body>

</html>