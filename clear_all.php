<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Clear all test data
\DB::statement('SET FOREIGN_KEY_CHECKS=0');
\DB::table('order_status_history')->truncate();
\DB::table('payment_transactions')->truncate();
\DB::table('workshop_dispatches')->truncate();
\DB::table('delivery_tasks')->truncate();
\DB::table('order_items')->truncate();
\DB::table('orders')->truncate();
\DB::table('delivery_appointments')->truncate();
\DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "✅ All test data cleared!\n";
echo "Now test a fresh delivery order from scratch.\n";
