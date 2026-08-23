<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$customer = App\Models\Customer::first();
if ($customer) {
    echo "Customer: {$customer->id}\n";
    echo "Orders count: " . $customer->orders()->count() . "\n";
    $orders = $customer->orders()->get();
    foreach ($orders as $order) {
        echo "Order {$order->id} Status: {$order->status} Branch: {$order->branch_id}\n";
    }
} else {
    echo "No customer found.\n";
}
