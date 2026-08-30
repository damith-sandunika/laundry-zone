<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$smsTemplate = App\Models\SmsTemplate::where('type', 'order_ready')->first();
if ($smsTemplate) {
    echo "SMS Template 'order_ready' found.\n";
    echo "is_active: " . ($smsTemplate->is_active ? 'true' : 'false') . "\n";
    echo "Message: " . $smsTemplate->message . "\n";
} else {
    echo "SMS Template 'order_ready' NOT found in DB.\n";
}

$allSms = App\Models\SmsTemplate::all();
echo "\nAll SMS Templates:\n";
foreach ($allSms as $t) {
    echo " - {$t->type} (active: {$t->is_active})\n";
}

