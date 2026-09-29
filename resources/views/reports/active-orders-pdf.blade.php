<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Orders Report — {{ $label }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { padding: 40px; color: #333; font-size: 11px; background: #fff; }

        /* Header / Letterhead */
        .letterhead { text-align: center; border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; }
        .laundry-name { font-size: 28px; font-weight: 800; color: #1e3a8a; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px; }
        .laundry-details { font-size: 11px; color: #64748b; line-height: 1.5; }

        .report-title { text-align: center; margin-bottom: 20px; }
        .report-title h1 { font-size: 16px; font-weight: bold; letter-spacing: 2px; color: #475569; margin-bottom: 5px; }
        .report-title p  { font-size: 12px; color: #94a3b8; font-weight: 500; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; page-break-inside: auto; }
        th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px 12px; font-size: 10px; text-transform: uppercase; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        th.text-right, td.text-right { text-align: right; }
        th.text-center, td.text-center { text-align: center; }
        td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 11px; }
        tr:nth-child(even) td { background: #fdfdfd; }

        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }

        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body>

    <!-- Print / Close Buttons -->
    <div class="no-print" style="margin-bottom:20px; text-align: right;">
        <button onclick="window.print()" style="padding:10px 20px;background:#2563eb;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;">🖨 Print / Save PDF</button>
        <button onclick="window.close()" style="padding:10px 20px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;font-size:12px;font-weight:bold;margin-left:10px;">Close</button>
    </div>

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="laundry-name">{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }}</div>
        <div class="laundry-details">{{ $isWorkshop ? 'Workshop Active Items Report' : 'Branch Active Orders Report' }}</div>
    </div>

    <!-- Title & Period -->
    <div class="report-title">
        <h1>{{ $isWorkshop ? 'ACTIVE WORKSHOP ITEMS' : 'ACTIVE ORDERS' }}</h1>
        <p style="margin-top:4px;">Period: <strong>{{ $label }}</strong></p>
    </div>

    <!-- Orders Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th style="width:70px;">Order No</th>
                <th>Customer</th>
                <th class="text-center">Type</th>
                <th class="text-center">Created At</th>
                <th class="text-center">Due Date</th>
                <th class="text-right">Price</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 0; @endphp
            @foreach($orders as $orderOrDispatch)
                @php
                    $count++;
                    $orderNumber = $isWorkshop ? ($orderOrDispatch->order->order_number ?? 'N/A') : $orderOrDispatch->order_number;
                    $customerName = $isWorkshop ? ($orderOrDispatch->order->customer->name ?? 'N/A') : ($orderOrDispatch->customer->name ?? 'N/A');
                    $dueDate = $isWorkshop ? ($orderOrDispatch->order->due_date ?? null) : $orderOrDispatch->due_date;
                    $status = $orderOrDispatch->status;
                    $isOverdue = $dueDate && \Carbon\Carbon::parse($dueDate)->isPast();
                @endphp
            <tr>
                <td class="text-center">{{ $count }}</td>
                <td style="font-weight:bold;color:#1e40af;">{{ $orderNumber }}</td>
                <td style="font-weight:bold;">
                    {{ $customerName }}
                    @if(isset($orderOrDispatch->order->customer->phone) || isset($orderOrDispatch->customer->phone))
                        <div style="font-weight:normal;color:#64748b;font-size:9px;">
                            {{ $isWorkshop ? ($orderOrDispatch->order->customer->phone ?? '') : ($orderOrDispatch->customer->phone ?? '') }}
                        </div>
                    @endif
                </td>
                <td class="text-center" style="color:#64748b;font-weight:bold;">
                    @php 
                        $isDelivery = $isWorkshop ? ($orderOrDispatch->order->is_delivery_order ?? 0) : ($orderOrDispatch->is_delivery_order ?? 0);
                    @endphp
                    {{ $isDelivery ? 'Delivery' : 'Pickup' }}
                </td>
                <td class="text-center">
                    @php
                        $createdAt = $isWorkshop ? ($orderOrDispatch->order->created_at ?? null) : ($orderOrDispatch->created_at ?? null);
                    @endphp
                    {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, y') : 'N/A' }}
                </td>
                <td class="text-center" style="{{ $isOverdue ? 'color:#ef4444;font-weight:bold;' : '' }}">
                    {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, y') : 'N/A' }}
                </td>
                <td class="text-right" style="font-weight:bold;">
                    @php
                        $total = $isWorkshop ? ($orderOrDispatch->order->total ?? 0) : ($orderOrDispatch->total ?? 0);
                    @endphp
                    Rs.{{ number_format($total, 2) }}
                </td>
                <td class="text-center">
                    <span style="background:#f1f5f9;color:#475569;padding:3px 7px;border-radius:4px;font-size:10px;font-weight:bold;">
                        {{ ucfirst(str_replace('_',' ',$status)) }}
                    </span>
                </td>
            </tr>
            @endforeach
            @if($count === 0)
            <tr>
                <td colspan="5" class="text-center" style="color:#94a3b8;padding:20px;">No orders found for this filter.</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        {{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} &bull; Active Orders Report &bull; {{ $label }} &bull; Generated on {{ now()->format('M d, Y h:i A') }}
    </div>

</body>
</html>
