<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Receipt #{{ $customerPackage->id }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 10px;
            width: 80mm;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .mt-2 { margin-top: 10px; }
        .mb-2 { margin-bottom: 10px; }
        .flex { display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 2px 0; }
        th.right, td.right { text-align: right; }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 500);">
    <div class="center bold" style="font-size:18px; letter-spacing:-0.03em; margin-bottom: 2px;">
        {{ \App\Models\Branch::first()->name ?? 'SMART LAUNDRY' }}
    </div>
    
    @if($branch && $branch->id !== (\App\Models\Branch::first()->id ?? 1))
        <div class="center bold" style="font-size:14px; margin-bottom: 2px;">{{ $branch->name }}</div>
    @endif
    <div class="text-center" style="font-size: 10px;">
        {{ $branch->address ?? '' }}<br>
        {{ $branch->phone ?? '' }}
    </div>

    <div class="divider mt-2"></div>

    <div class="text-center font-bold mt-2 mb-2">PACKAGE RECEIPT</div>

    <div>
        <div class="flex"><span>Date:</span> <span>{{ now()->format('Y-m-d H:i') }}</span></div>
        <div class="flex"><span>Receipt #:</span> <span>PKG-{{ $customerPackage->id }}</span></div>
        <div class="flex"><span>Customer:</span> <span>{{ $customerPackage->customer->name }}</span></div>
        <div class="flex"><span>Phone:</span> <span>{{ $customerPackage->customer->phone }}</span></div>
    </div>

    <div class="divider mt-2 mb-2"></div>

    <div>
        <div class="font-bold">Package: {{ $customerPackage->package->name }}</div>
        <div class="flex" style="font-size: 10px;">
            <span>Valid Until:</span> 
            <span>{{ $customerPackage->end_date->format('Y-m-d') }}</span>
        </div>
    </div>

    <div class="divider mt-2 mb-2"></div>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="right">Limit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customerPackage->services as $svc)
            <tr>
                <td>{{ $svc->serviceType->name }}</td>
                <td class="right">{{ floatval($svc->total_quantity) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider mt-2 mb-2"></div>

    <div class="flex font-bold" style="font-size: 14px;">
        <span>TOTAL:</span>
        <span>Rs.{{ number_format($customerPackage->package->price, 2) }}</span>
    </div>

    <div class="flex mt-2" style="font-size: 11px;">
        <span>Payment Status:</span>
        <span class="font-bold">{{ strtoupper($customerPackage->payment_status) }}</span>
    </div>

    <div class="divider mt-2 mb-2"></div>

    <div class="text-center mt-2" style="font-size: 10px;">
        Thank you for choosing us!<br>
        Powered by DSRZone
    </div>
</body>
</html>


