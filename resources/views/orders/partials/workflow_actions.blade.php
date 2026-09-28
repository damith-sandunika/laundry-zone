@php
    $dispatch = \App\Models\WorkshopDispatch::with(['order.paymentTransactions', 'order.cubby.cubby', 'order.deliveryAppointment', 'branch'])->where('order_id', $order->id)->first();
    $hasPayment = $order->paymentTransactions()->whereIn('status', ['completed', 'pending'])->exists() || $order->receipt_path || ($order->deliveryAppointment && $order->deliveryAppointment->receipt_path);
    
    // Convert to JSON for Alpine
    $dispatchJson = $dispatch ? $dispatch->toJson() : 'null';
    $riders = \App\Models\User::where('role', 'rider')->get();
    $cubbies = \App\Models\Cubby::where('branch_id', auth()->user()->branch_id)->where('is_active', true)->get();
@endphp

@if(!in_array($order->status, ['delivered', 'completed']))
<div x-data="orderWorkflowManager()" x-init="initData({{ $dispatchJson }})">
    @include('orders.partials.workflow_actions.banner')

    @include('orders.partials.workflow_actions.modals')

    @include('orders.partials.workflow_actions.scripts')
</div>
@else
{{-- Order is {{ $order->status }}. No further actions needed. --}}
@endif
