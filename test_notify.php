<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::first();
echo "Testing with Order ID: " . $order->id . "\n";
echo "Customer Phone: " . $order->customer->phone . "\n";
echo "Customer Email: " . $order->customer->email . "\n";

$isSmsEnabled = App\Models\Setting::where('key', 'is_sms_enabled')->value('value') === 'true';
echo "is_sms_enabled: " . ($isSmsEnabled ? 'true' : 'false') . "\n";

$isEmailEnabled = App\Models\Setting::where('key', 'is_email_enabled')->value('value') === 'true';
echo "is_email_enabled: " . ($isEmailEnabled ? 'true' : 'false') . "\n";

echo "\n--- Sending sendOrderReady --- \n";
$commService = new \App\Services\CommunicationService();
$commService->sendOrderReady($order);

echo "\nLatest Email Log:\n";
$log = App\Models\EmailLog::latest()->first();
if ($log) {
    echo "Type: {$log->type}, Status: {$log->status}, Response: {$log->response}\n";
}

echo "\nLatest SMS Log:\n";
$slog = App\Models\SmsLog::latest()->first();
if ($slog) {
    echo "Type: {$slog->type}, Status: {$slog->status}, Response: {$slog->response}\n";
}
