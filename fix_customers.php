<?php
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (Schema::hasColumn('customers', 'password')) {
    Schema::dropColumns('customers', ['password']);
}
if (Schema::hasColumn('customers', 'remember_token')) {
    Schema::dropColumns('customers', ['remember_token']);
}

DB::statement("UPDATE customers SET email = NULL WHERE email = ''");
echo "Done\n";
