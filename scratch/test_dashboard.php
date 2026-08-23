<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Assuming customer ID 11 based on previous test_order.php
$customer = App\Models\Customer::find(11);
if(!$customer) {
    echo "Customer 11 not found\n";
    $customer = App\Models\Customer::first();
}

echo "Customer: {$customer->name}\n";

$activeOrders = $customer->orders()
    ->with(['branch', 'items.serviceType'])
    ->whereNotIn('status', ['delivered', 'cancelled'])
    ->latest()
    ->get();

$appointments = $customer->deliveryAppointments()
    ->with('branch')
    ->whereNotIn('status', ['completed', 'cancelled', 'order_created', 'delivered'])
    ->whereNull('order_id')
    ->latest()
    ->get();

echo "Appointments Count: " . $appointments->count() . "\n";
echo "Active Orders Count: " . $activeOrders->count() . "\n";

foreach ($activeOrders as $order) {
    echo "Order {$order->id} - Status: {$order->status} - Number: {$order->order_number}\n";
}
