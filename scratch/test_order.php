<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = App\Models\Order::latest()->first();
echo "Latest Order ID: {$o->id}, Status: {$o->status}, Customer: {$o->customer_id}\n";
