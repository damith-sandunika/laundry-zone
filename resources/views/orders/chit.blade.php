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
            border-bottom: 2px dashed #000;
            padding-bottom: 5px;
        }

        .text-bold {
            font-weight: 900;
            font-size: 16px;
            margin: 3px 0;
        }

        .text-center {
            text-align: center;
        }

        .date {
            font-weight: 900;
            font-size: 16px;
            margin: 5px 0 10px 0;
            text-align: center;
            text-transform: uppercase;
        }

        .items {
            margin-top: 10px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-weight: 900;
            font-size: 16px;
            margin-bottom: 5px;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
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
        <div class="text-bold text-center">{{ strtoupper(($order->customer->title ? $order->customer->title . ' ' : '') . ($order->customer->name ?? 'Walk-in')) }}</div>
    </div>

    <div class="date">{{ $order->due_date ? $order->due_date->format('d M Y h:i A') : 'N/A' }}</div>

    <div class="items">
        @foreach($order->items as $item)
            <div class="item-row">
                <span>{{ $item->item_description }}</span>
                <span>x{{ $item->quantity }}</span>
            </div>
        @endforeach
    </div>

    <script>
        window.onload = function () {
            window.print();
            setTimeout(() => { window.close(); }, 500);
        }
    </script>
</body>

</html>