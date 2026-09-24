<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\DeliveryAppointment;

// Check delivery appointment 262
$appt = DeliveryAppointment::withoutGlobalScopes()->find(262);
if ($appt) {
    echo "=== DELIVERY APPOINTMENT #262 ===\n";
    echo "  branch_id={$appt->branch_id}\n";
    echo "  order_id={$appt->order_id}\n";
    echo "  order_number={$appt->order_number}\n";
    echo "  status={$appt->status}\n";
    echo "  customer_id={$appt->customer_id}\n";
}

// What dcsdc (branch_id=3) sees in delivery appointments
echo "\n=== Delivery Appointments dcsdc (branch_id=3) sees ===\n";
$appts = DeliveryAppointment::withoutGlobalScopes()
    ->where('branch_id', 3)
    ->latest()->limit(5)
    ->get(['id', 'branch_id', 'order_id', 'order_number', 'status']);
foreach ($appts as $a) {
    echo "  appt_id={$a->id} | branch_id={$a->branch_id} | order_id={$a->order_id} | order_number={$a->order_number}\n";
}

// What Kandy (branch_id=2) sees in delivery appointments
echo "\n=== Delivery Appointments Kandy (branch_id=2) sees ===\n";
$appts2 = DeliveryAppointment::withoutGlobalScopes()
    ->where('branch_id', 2)
    ->latest()->limit(5)
    ->get(['id', 'branch_id', 'order_id', 'order_number', 'status']);
foreach ($appts2 as $a) {
    echo "  appt_id={$a->id} | branch_id={$a->branch_id} | order_id={$a->order_id} | order_number={$a->order_number}\n";
}
