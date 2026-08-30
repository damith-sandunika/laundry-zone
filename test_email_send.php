<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate a logged in user
$user = \App\Models\User::find(2);
auth()->login($user);
echo "Logged in as: " . auth()->user()->name . " (ID: " . auth()->id() . ")\n";

$dispatch = \App\Models\WorkshopDispatch::with(['order.customer'])->latest()->first();
if (!$dispatch) {
    echo "No dispatches found.\n";
    exit;
}
echo "Dispatch ID: " . $dispatch->id . " | Order: " . $dispatch->order->order_number . " | Status: " . $dispatch->status . "\n";
echo "Order status: " . $dispatch->order->status . "\n";
echo "Customer email: " . $dispatch->order->customer->email . "\n\n";

$commService = new \App\Services\CommunicationService();

echo "=== Testing sendOrderProcessing ===\n";
try {
    $commService->sendOrderProcessing($dispatch->order);
    echo "Done - check email_logs table\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Check latest email log
$log = \App\Models\EmailLog::latest()->first();
echo "\nLatest email log: type=" . $log->type . " | status=" . $log->status . " | response=" . $log->response . "\n";
