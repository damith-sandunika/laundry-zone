<table class="items-table">
    <thead>
        <tr>
            <th style="width: 50%;">Item / Service</th>
            <th class="center" style="width: 10%;">Qty</th>
            <th class="right" style="width: 20%;">Unit Price</th>
            <th class="right" style="width: 20%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @php $total_qty = 0; @endphp
        @php
            $showMain = request('show_main', 'true') === 'true';
            $showSub = request('show_sub', 'true') === 'true';
            $showService = request('show_service', 'true') === 'true';
        @endphp
        @foreach($order->items as $item)
        @php
            $total_qty += $item->quantity;
        @endphp
        <tr>
            <td>
                @if($item->serviceType && $item->serviceType->category)
                    @if($showMain || $showSub)
                    <div style="font-size: 13px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; font-weight: 900;">
                        @if($showMain && $item->serviceType->category->parent)
                            {{ $item->serviceType->category->parent->name }}
                            @if($showSub) > @endif
                        @endif
                        @if($showSub)
                            {{ $item->serviceType->category->name }}
                        @endif
                    </div>
                    @endif
                @endif
                <div style="display: flex; align-items: center; gap: 5px;">
                    @if($showService)
                        {{ $item->serviceType->name ?? $item->item_description }}
                    @endif
                    @if($item->turnaroundType && !$item->turnaroundType->is_default)
                        <small style="color:#64748b;">[{{ $item->turnaroundType->name }}]</small>
                    @elseif((float)$item->custom_turnaround_percentage > 0)
                        <small style="color:#64748b;">[Express ({{ (float)$item->custom_turnaround_percentage }}%)]</small>
                    @endif
                </div>
            </td>
            <td class="center">{{ $item->quantity == floor($item->quantity) ? (int)$item->quantity : rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
            <td class="right">Rs.{{ number_format($item->unit_price, 2) }}</td>
            <td class="right" style="color: #0f172a; font-weight: 800;">Rs.{{ number_format($item->unit_price * max(1, $item->quantity), 2) }}</td>
        </tr>
        @if($item->turnaround_extra_charge > 0)
        <tr style="background:#fffbeb;">
            <td style="padding-left: 30px; font-size:13px; color:#d97706;">
                ⚡ Express Surcharge
            </td>
            <td class="center" style="color:#d97706;">{{ $item->quantity == floor($item->quantity) ? (int)$item->quantity : rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
            <td class="right" style="color:#d97706;">Rs.{{ number_format($item->turnaround_extra_charge / max(1, $item->quantity), 2) }}</td>
            <td class="right" style="color: #d97706; font-weight: 800;">+ Rs.{{ number_format($item->turnaround_extra_charge, 2) }}</td>
        </tr>
        @endif
        @if($item->extras->count() > 0)
            @foreach($item->extras as $e)
            @php $total_qty += $e->quantity; @endphp
            <tr style="background:#f8fafc;">
                <td style="padding-left: 30px; font-size:13px; color:#475569;">
                    + {{ $e->extra->name }}
                </td>
                <td class="center">{{ $e->quantity == floor($e->quantity) ? (int)$e->quantity : rtrim(rtrim(number_format($e->quantity, 2), '0'), '.') }}</td>
                <td class="right">Rs.{{ number_format($e->unit_price, 2) }}</td>
                <td class="right" style="color: #0f172a; font-weight: 800;">Rs.{{ number_format($e->unit_price * $e->quantity, 2) }}</td>
            </tr>
            @endforeach
        @endif
        @endforeach
        <tr style="background:white; color:#0f172a; border-top:2px solid #e2e8f0; border-bottom:2px solid #e2e8f0;">
            <td style="text-align:right; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; padding:12px 16px;">Total Items / Qty:</td>
            <td class="center" style="font-weight:900; font-size:15px; padding:12px 16px; color:{{ $accentColor }};">{{ $total_qty == floor($total_qty) ? (int)$total_qty : rtrim(rtrim(number_format($total_qty, 2), '0'), '.') }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>

