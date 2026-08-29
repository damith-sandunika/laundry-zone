<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Categories: " . (Illuminate\Support\Facades\Schema::hasColumn('service_categories', 'branch_id') ? 'Yes' : 'No') . "\n";
echo "Packages: " . (Illuminate\Support\Facades\Schema::hasColumn('packages', 'branch_id') ? 'Yes' : 'No') . "\n";
echo "Services: " . (Illuminate\Support\Facades\Schema::hasColumn('service_types', 'branch_id') ? 'Yes' : 'No') . "\n";
