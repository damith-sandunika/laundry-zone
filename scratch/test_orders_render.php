<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/orders', 'GET');
$user = \App\Models\User::where('role', 'super_admin')->first();

if ($user) {
    auth()->login($user);
}

$response = $kernel->handle($request);
echo "Status Code: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() != 200) {
    echo "Exception: " . $response->exception . "\n";
}
