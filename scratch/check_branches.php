<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Branch;
use App\Models\User;
use App\Models\Order;

echo "=== ALL BRANCHES ===\n";
$branches = Branch::all(['id', 'name', 'type', 'is_active', 'is_delivery_enabled']);
foreach ($branches as $b) {
    echo "  ID={$b->id} | name={$b->name} | type={$b->type} | active={$b->is_active} | delivery={$b->is_delivery_enabled}\n";
}

echo "\n=== USERS & THEIR BRANCHES ===\n";
$users = User::all(['id', 'name', 'role', 'branch_id']);
foreach ($users as $u) {
    echo "  ID={$u->id} | role={$u->role} | branch_id={$u->branch_id} | name={$u->name}\n";
}

echo "\n=== ORDER #1315 full detail ===\n";
$o = Order::withoutGlobalScopes()->find(1315);
if ($o) {
    echo "  branch_id={$o->branch_id} | workshop_id={$o->workshop_id} | status={$o->status}\n";
}

echo "\n=== BranchScope simulation: what does dcsdc workshop admin see? ===\n";
// Find a workshop_admin user
$wsAdmin = User::where('role', 'workshop_admin')->first();
if ($wsAdmin) {
    echo "Workshop admin: {$wsAdmin->name} | branch_id={$wsAdmin->branch_id}\n";
    $orders = Order::withoutGlobalScopes()
        ->where(function($q) use ($wsAdmin) {
            $q->where('branch_id', $wsAdmin->branch_id)
              ->orWhere('workshop_id', $wsAdmin->branch_id);
        })
        ->latest()->limit(5)
        ->select('id', 'order_number', 'branch_id', 'workshop_id', 'is_delivery_order')
        ->get();
    echo "Orders this workshop admin sees:\n";
    foreach ($orders as $o) {
        echo "  #{$o->order_number} | branch_id={$o->branch_id} | workshop_id={$o->workshop_id} | delivery={$o->is_delivery_order}\n";
    }
}
