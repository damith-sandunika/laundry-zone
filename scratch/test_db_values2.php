<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$app2 = App\Models\DeliveryAppointment::find(8);
if ($app2) {
    echo "App 8 Status: {$app2->status}, order_id: {$app2->order_id}\n";
}
