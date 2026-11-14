<div class="items">
    @php $total_qty = 0; @endphp
    @foreach($order->items as $item)
        @php
            $catTrail = '';
            if ($item->serviceType && $item->serviceType->category) {
                $cat = $item->serviceType->category;
                $catTrail = $cat->parent ? $cat->parent->name . ' > ' . $cat->name : $cat->name;
            }
            $itemName = $item->serviceType ? $item->serviceType->name : $item->item_description;
            $total_qty += $item->quantity;
        @endphp
        <div style="margin-bottom: 8px;">
            @if($catTrail)
                <div style="font-size: 0.6em; font-weight: bold; text-transform: uppercase; margin-bottom: 1px;">&bull;
                    {{ $catTrail }}
                </div>
                <div class="item-row" style="margin-bottom: 2px;">
                    <span style="margin-left: 8px;">{{ $itemName }}</span>
                    <span>x{{ $item->quantity }}</span>
                </div>
            @else
                <div class="item-row" style="margin-bottom: 2px;">
                    <span>&bull; {{ $itemName }}</span>
                    <span>x{{ $item->quantity }}</span>
                </div>
            @endif
            @if($item->turnaroundType && !$item->turnaroundType->is_default)
                <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">[{{ $item->turnaroundType->name }}]</div>
            @elseif((float) $item->custom_turnaround_percentage > 0)
                <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">[Express ({{ (float) $item->custom_turnaround_percentage }}%)]</div>
            @endif
            @if($item->extras->count() > 0)
                @foreach($item->extras as $e)
                    @php $total_qty += $e->quantity; @endphp
                    <div style="font-size:0.65em; margin-left:12px; margin-bottom: 2px;">+ {{ $e->extra->name }} (x{{ $e->quantity }})</div>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="total-row" style="border-top: 1px solid #000; margin-top: 8px; padding-top: 8px;">
        <span>Total Qty</span>
        <span>x{{ fmod($total_qty, 1) == 0 ? (int) $total_qty : number_format($total_qty, 2) }}</span>
    </div>
</div>

{{-- Payment Totals --}}
@if($showTotals)
<div style="border-top: 1px solid #000; margin-top: 15px; padding-top: 10px; padding-bottom: 15px;">
    @if($order->discount > 0)
    <div class="item-row" style="font-size: 0.72em;">
        <span>Subtotal</span>
        <span>Rs.{{ number_format($order->subtotal, 2) }}</span>
    </div>
    @php $discountPct = ($order->subtotal > 0) ? round(($order->discount / $order->subtotal) * 100, 1) : 0; @endphp
    <div class="item-row" style="font-size: 0.72em;">
        <span>Discount{{ $discountPct > 0 ? ' (' . $discountPct . '%)' : '' }}</span>
        <span>- Rs.{{ number_format($order->discount, 2) }}</span>
    </div>
    @endif
    @if($order->delivery_fee > 0)
    <div class="item-row" style="font-size: 0.72em;">
        <span>Delivery Fee</span>
        <span>Rs.{{ number_format($order->delivery_fee, 2) }}</span>
    </div>
    @endif
    <div class="total-row" style="border-top: 1px solid #000; border-bottom: none; margin-bottom: 5px; font-size: 0.82em;">
        <span>TOTAL</span>
        <span>Rs.{{ number_format($order->total, 2) }}</span>
    </div>
    <div class="item-row" style="font-size: 0.72em;">
        <span>Paid</span>
        <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
    </div>
    @php $balance = $order->total - $order->paid_amount; @endphp
    @if($balance > 0)
    <div class="item-row" style="font-size: 0.78em; font-weight: 900; border-top: 1px solid #000; padding-top: 4px; margin-top: 4px;">
        <span>Balance Due</span>
        <span>Rs.{{ number_format($balance, 2) }}</span>
    </div>
    @else
    <div class="item-row" style="font-size: 0.72em; font-weight: 900;">
        <span>✓ Fully Paid</span>
        <span>Rs.{{ number_format($order->paid_amount, 2) }}</span>
    </div>
    @endif
</div>
@endif

@if($showSpecialNotes)
<div class="special-note" style="border-top: 1px solid #000; padding-top: 15px;">
    <div>{{ $notesLabel }}</div>
    @for($i = 0; $i < $notesLineCount; $i++)
        <div class="note-line"></div>
    @endfor
</div>
@endif

@if($showProcessList && !empty($processItems))
<div class="process-list">
    @foreach($processItems as $proc)
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $proc['name'] ?? '' }} :</span>
            <span style="font-family: monospace;">{{ $proc['suffix'] ?? '________' }}</span>
        </div>
    @endforeach
</div>
@endif

