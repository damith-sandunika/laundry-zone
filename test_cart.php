<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$o = \App\Models\Order::find(56);
if ($o && $o->deliveryAppointment) {
    echo $o->deliveryAppointment->cart_json;
}
