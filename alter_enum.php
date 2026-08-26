<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\DB::statement("ALTER TABLE delivery_tasks MODIFY COLUMN status ENUM('assigned','accepted','on_way','arrived','waiting_for_payment','completed','cancelled') NOT NULL DEFAULT 'assigned'");
echo "ENUM updated\n";
