<?php

use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$expenses = \App\Models\Expense::where('category', 'package_sale')->get();
foreach ($expenses as $exp) {
    $cpId = null;
    $cp = \App\Models\CustomerPackage::latest()->first();
    if ($cp) $cpId = $cp->id;
    
    if ($cpId) {
        \App\Models\PackagePayment::create([
            'customer_package_id' => $cpId,
            'amount' => $exp->amount,
            'payment_method' => 'cash',
            'created_by' => $exp->created_by,
            'created_at' => $exp->created_at,
            'updated_at' => $exp->updated_at
        ]);
    }
    $exp->delete();
}
echo "Migrated " . count($expenses) . " records.\n";
