<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::find(54);
$action = app(\App\Actions\Order\UpdateOrderAction::class);

$payload = [
    'cart' => [
        [
            'service_type_id' => 1,
            'name' => 'Wash & Iron',
            'base_price' => 150.00,
            'qty' => 5,
            'turnaround_type_id' => 2, // SDC
            'selected_extras' => [
                ['id' => 1, 'qty' => 4] // Hangers
            ]
        ]
    ],
    'discount_percentage' => 10,
    'delivery_fee' => 300,
    'requested_advance' => 500,
];

try {
    $action->execute($order, $payload, 1);
    echo "Success!\n";
    $order->refresh();
    echo "Subtotal: " . $order->subtotal . "\n";
    echo "Discount: " . $order->discount . "\n";
    echo "Delivery: " . $order->delivery_fee . "\n";
    echo "Total: " . $order->total . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
