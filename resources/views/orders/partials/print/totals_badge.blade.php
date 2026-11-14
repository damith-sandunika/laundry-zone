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

