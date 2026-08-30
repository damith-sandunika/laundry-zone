<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(\App\Models\SmsLog::latest()->take(10)->get() as $log) { 
    echo $log->created_at . ' - ' . $log->type . ' - ' . $log->status . ' - ' . $log->response . "\n";
}
