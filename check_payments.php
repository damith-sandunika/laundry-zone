<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \DB::table('orders')->select('id', 'total', 'paid_amount', 'requested_advance')->get();
$payments = \DB::table('payment_transactions')->get();

echo "Orders:\n";
print_r($orders->toArray());
echo "\nPayments:\n";
print_r($payments->toArray());
