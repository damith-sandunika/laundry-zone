<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::latest()->first();
if (!$order) {
    echo "No orders found.\n";
    exit;
}

echo "Testing updateStatus on order " . $order->id . "\n";

$request = new \Illuminate\Http\Request();
$request->replace(['status' => 'done']);

$controller = app(\App\Http\Controllers\OrderController::class);

try {
    $response = $controller->updateStatus($request, $order);
    
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "Returned RedirectResponse\n";
        $session = $response->getSession();
        if ($session) {
            echo "Session Error: " . $session->get('error') . "\n";
            echo "Session Success: " . $session->get('success') . "\n";
        }
    } else {
        echo "Returned something else.\n";
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation failed: \n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
