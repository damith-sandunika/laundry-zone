<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = \App\Models\Order::create([
    'order_number' => 'ORD-CREDIT-TEST', 
    'customer_id' => 1, 
    'branch_id' => 1, 
    'subtotal' => 500, 
    'total' => 500, 
    'paid_amount' => 0, 
    'payment_method' => 'credit', 
    'payment_status' => 'pending', 
    'status' => 'received', 
    'created_by' => 1
]);
echo $o->id;
