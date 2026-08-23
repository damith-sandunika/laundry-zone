<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = App\Models\Order::latest()->take(5)->get();
echo "--- LATEST 5 ORDERS ---\n";
foreach($orders as $o) {
    echo "ID: {$o->id}, Num: {$o->order_number}, Status: {$o->status}, is_del: {$o->is_delivery_order}, app_id: {$o->delivery_appointment_id}\n";
}

$apps = App\Models\DeliveryAppointment::latest()->take(5)->get();
echo "\n--- LATEST 5 APPOINTMENTS ---\n";
foreach($apps as $a) {
    echo "ID: {$a->id}, Status: {$a->status}, order_id: {$a->order_id}\n";
}
