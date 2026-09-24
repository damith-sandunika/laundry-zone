<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('orders');
echo "Columns:\n" . implode(", ", $cols) . "\n\n";

// Check last 5 delivery orders
$orders = \App\Models\Order::withoutGlobalScopes()
    ->where('is_delivery_order', true)
    ->latest()->limit(5)
    ->select('id', 'order_number', 'branch_id', 'workshop_id', 'status', 'is_delivery_order')
    ->get();

echo "Last 5 delivery orders:\n";
foreach ($orders as $o) {
    echo "  #{$o->order_number} | branch_id={$o->branch_id} | workshop_id={$o->workshop_id} | status={$o->status}\n";
}
