<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$extras = \App\Models\OrderItemExtra::all();
foreach($extras as $e) {
    $ex = $e->extra;
    if ($ex) {
        $e->unit_price = $ex->price;
        $e->total_price = $ex->price * $e->quantity;
        $e->save();
    }
}
echo "Done";
