<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\DeliveryAppointment;
use App\Models\User;

// Check order 1323
$o = Order::withoutGlobalScopes()->find(1323);
if (!$o) {
    // Try by order_number
    $o = Order::withoutGlobalScopes()->where('order_number', '1323')->first();
}

if ($o) {
    echo "=== ORDER #{$o->id} / order_number={$o->order_number} ===\n";
    echo "  branch_id={$o->branch_id}\n";
    echo "  workshop_id={$o->workshop_id}\n";
    echo "  status={$o->status}\n";
    echo "  is_delivery_order={$o->is_delivery_order}\n";
    echo "  delivery_appointment_id={$o->delivery_appointment_id}\n";
} else {
    echo "Order 1323 not found\n";
}

// Also check order_number=262
$o262 = Order::withoutGlobalScopes()->where('order_number', '262')->first();
if ($o262) {
    echo "\n=== ORDER order_number=262 ===\n";
    echo "  ID={$o262->id}\n";
    echo "  branch_id={$o262->branch_id}\n";
    echo "  workshop_id={$o262->workshop_id}\n";
    echo "  status={$o262->status}\n";
    echo "  is_delivery_order={$o262->is_delivery_order}\n";
}

// Check what dcsdc workshop admin sees - latest 10
echo "\n=== What dcsdc workshop admin (branch_id=3) sees - last 10 ===\n";
$orders = Order::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('branch_id', 3)
          ->orWhere('workshop_id', 3);
    })
    ->latest()->limit(10)
    ->select('id', 'order_number', 'branch_id', 'workshop_id', 'is_delivery_order', 'status')
    ->get();

foreach ($orders as $o) {
    echo "  id={$o->id} | #={$o->order_number} | branch={$o->branch_id} | ws={$o->workshop_id} | delivery={$o->is_delivery_order} | via=" . ($o->branch_id == 3 ? 'branch_id' : 'workshop_id') . "\n";
}

// Check what Kandy branch admin sees
echo "\n=== What Kandy admin (branch_id=2) sees - last 10 ===\n";
$orders2 = Order::withoutGlobalScopes()
    ->where(function($q) {
        $q->where('branch_id', 2)
          ->orWhere('workshop_id', 2);
    })
    ->latest()->limit(10)
    ->select('id', 'order_number', 'branch_id', 'workshop_id', 'is_delivery_order', 'status')
    ->get();

foreach ($orders2 as $o) {
    echo "  id={$o->id} | #={$o->order_number} | branch={$o->branch_id} | ws={$o->workshop_id} | delivery={$o->is_delivery_order}\n";
}
