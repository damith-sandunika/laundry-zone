<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\Mail::raw('Test Email from Local', function($msg) {
        $msg->to('damithsandunika965@gmail.com')->subject('Local RapidLaundromat Test');
    });
    echo "SUCCESS: Email sent successfully using local SMTP config!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
