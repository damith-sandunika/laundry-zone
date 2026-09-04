<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "SMS LOGS:\n";
echo json_encode(App\Models\SmsLog::latest('id')->take(5)->get(), JSON_PRETTY_PRINT);
echo "\n\nEMAIL LOGS:\n";
echo json_encode(App\Models\EmailLog::latest('id')->take(5)->get(), JSON_PRETTY_PRINT);
