<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$branchId = \App\Models\Branch::first()->id ?? 1;
$customerId = \App\Models\Customer::first()->id ?? 1;

for ($i=0; $i<50; $i++) {
    $order = \App\Models\Order::create([
        'order_number' => 'TEST-' . now()->format('YmdHis') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
        'branch_id' => $branchId,
        'customer_id' => $customerId,
        'status' => 'received',
        'subtotal' => rand(1000, 5000),
        'delivery_fee' => 0,
        'discount_amount' => 0,
        'total' => 0,
        'paid_amount' => 0,
        'due_date' => now()->addDays(rand(1, 2))->format('Y-m-d H:i:s'),
        'payment_status' => 'unpaid',
        'order_type' => 'regular',
        'created_by' => 1,
        'source' => 'pos'
    ]);
    
    $order->total = $order->subtotal;
    $order->save();

    \App\Models\WorkshopDispatch::create([
        'order_id' => $order->id,
        'branch_id' => $branchId,
        'workshop_branch_id' => \App\Models\Branch::where('type', 'workshop')->first()->id ?? $branchId,
        'status' => 'processing',
        'dispatched_by' => 1,
        'dispatched_at' => now(),
        'received_at_workshop_at' => now()
    ]);
}
echo "Created 50 test orders successfully!\n";
