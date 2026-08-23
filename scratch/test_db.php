<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Customer::first();
echo "Appointments:\n";
foreach($c->deliveryAppointments()->get() as $a) {
    echo "ID: $a->id, Status: $a->status, OrderID: $a->order_id\n";
}
echo "\nOrders:\n";
foreach($c->orders()->get() as $o) {
    echo "ID: $o->id, Status: $o->status, OrderNumber: $o->order_number\n";
}
