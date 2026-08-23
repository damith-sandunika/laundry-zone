<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Customer::find(11);
if($c) { 
    $q = $c->orders()->with(['branch', 'items.serviceType'])->whereNotIn('status', ['delivered', 'cancelled'])->latest()->toSql(); 
    echo $q; 
}
