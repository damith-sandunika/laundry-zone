<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = \DB::select("SHOW COLUMNS FROM payment_transactions WHERE Field = 'payment_method'");
print_r($cols);
