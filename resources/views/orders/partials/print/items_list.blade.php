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

