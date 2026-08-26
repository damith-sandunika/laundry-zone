<?php
foreach(App\Models\OrderItem::all() as $item) {
    $st = $item->serviceType;
    if($st) {
        $item->update(['unit_price' => $st->base_price, 'total_price' => $st->base_price * $item->quantity]);
    }
}
$order = App\Models\Order::first();
if ($order) {
    $sum = $order->items()->sum('total_price');
    $order->update(['subtotal' => $sum, 'total' => $sum]);
}
echo "Fixed DB items.\n";
