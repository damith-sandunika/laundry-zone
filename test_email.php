<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get latest order with a customer email
$order = App\Models\Order::whereHas('customer', function($q) {
    $q->whereNotNull('email')->where('email', '!=', '');
})->latest()->first();

if (!$order) {
    echo "No order with customer email found. Create a test order first.\n";
    exit;
}

echo "Testing emails for Order #{$order->id} - Customer: {$order->customer->name} ({$order->customer->email})\n\n";

$comm = new App\Services\CommunicationService();

$types = [
    'order_confirmation'           => [$comm, 'sendOrderConfirmation'],
    'bill_finalize'                => [$comm, 'sendFinalizedBill'],
    'order_ready'                  => [$comm, 'sendOrderReady'],
    'order_delivered'              => [$comm, 'sendOrderDelivered'],
    'delivery_confirmation'        => [$comm, 'sendDeliveryConfirmation'],
    'delivery_thank_you'           => [$comm, 'sendDeliveryThankYou'],
    'order_dispatched_to_workshop' => [$comm, 'sendOrderDispatchedToWorkshop'],
    'order_processing'             => [$comm, 'sendOrderProcessing'],
    'order_returned_from_workshop' => [$comm, 'sendOrderReturnedFromWorkshop'],
    'order_cancelled'              => [$comm, 'sendOrderCancelled'],
];

foreach ($types as $type => $callable) {
    try {
        call_user_func($callable, $order);
        echo "✅ SUCCESS - {$type}\n";
    } catch (\Exception $e) {
        echo "❌ ERROR - {$type}: " . $e->getMessage() . "\n";
    }
    sleep(1); // avoid Gmail rate limiting
}

echo "\nDone! Check email_debug.txt for detailed logs.\n";
