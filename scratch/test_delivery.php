<?php
$customer = \App\Models\Customer::first();
$user = \App\Models\User::first();
auth()->login($user);

// Test CreatePosDeliveryOrderAction with turnaround + extras
$data = [
    'customer_id' => $customer->id,
    'pickup_date' => now()->addDay()->format('Y-m-d H:i:s'),
    'pickup_address' => '123 Test St',
    'cart' => [
        [
            'service_type_id' => 1,
            'name' => 'Shirt',
            'base_price' => 100,
            'qty' => 2,
            'turnaround_type_id' => null,
            'selected_extras' => [
                ['id' => 1, 'qty' => 1]
            ]
        ]
    ]
];

$action1 = new \App\Actions\POS\CreatePosDeliveryOrderAction();
$result = $action1->execute($user, $data);
$order = \App\Models\Order::find($result['order_id']);

echo "=== POS Delivery Order Test ===" . PHP_EOL;
echo "Order Subtotal: " . $order->subtotal . PHP_EOL;
echo "Order Total: " . $order->total . PHP_EOL;
echo "Order Items:" . PHP_EOL;
foreach ($order->items as $item) {
    echo "  - " . $item->item_description . " x" . $item->quantity . " @ " . $item->unit_price . " = " . $item->total_price . PHP_EOL;
}
echo "Total Items Count: " . $order->items()->count() . PHP_EOL;
