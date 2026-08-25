<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Categories ---\n";
echo json_encode(App\Models\ServiceCategory::all(), JSON_PRETTY_PRINT);
echo "\n--- Services ---\n";
echo json_encode(App\Models\ServiceType::all(), JSON_PRETTY_PRINT);
echo "\n--- Customers ---\n";
echo json_encode(App\Models\Customer::all(), JSON_PRETTY_PRINT);
