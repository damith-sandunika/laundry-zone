<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kandy = \App\Models\Branch::firstOrCreate(['name' => 'Kandy Branch'], ['address' => 'Peradeniya Rd, Kandy', 'phone' => '0812345678', 'is_active' => true]);
\App\Models\User::firstOrCreate(['email' => 'kandy@laundry.com'], ['name' => 'Kandy Admin', 'password' => bcrypt('password'), 'role' => 'branch_admin', 'branch_id' => $kandy->id]);
\App\Models\User::firstOrCreate(['email' => 'kandy.workshop@laundry.com'], ['name' => 'Kandy Workshop', 'password' => bcrypt('password'), 'role' => 'workshop', 'branch_id' => $kandy->id]);
\App\Models\User::firstOrCreate(['email' => 'kandy.rider@laundry.com'], ['name' => 'Kandy Rider', 'password' => bcrypt('password'), 'role' => 'rider', 'branch_id' => $kandy->id]);

echo "Kandy Branch and Users Created!";
