<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dispatches = \App\Models\WorkshopDispatch::all();
echo "Total dispatches: " . $dispatches->count() . "\n";
foreach($dispatches as $d) {
    echo "ID: {$d->id}, Order ID: {$d->order_id}, WS ID: {$d->workshop_branch_id}, Status: {$d->status}\n";
}

$orders = \App\Models\Order::where('status', 'dispatched_to_workshop')->get();
echo "Orders with status dispatched_to_workshop: " . $orders->count() . "\n";
foreach($orders as $o) {
    echo "Order ID: {$o->id}, WS ID: {$o->workshop_id}\n";
}
