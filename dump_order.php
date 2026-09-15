<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = \App\Models\Order::find(9);
echo "Method: " . $o->payment_method . "\n";
echo "Paid: " . $o->paid_amount . "\n";
echo "Total: " . $o->total . "\n";
