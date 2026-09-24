<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\User::where('email', 'admin@360laundry.lk')->first();
if($user) {
    Auth::login($user);
}

$request = Illuminate\Http\Request::create('/orders', 'GET');
$response = $kernel->handle($request);
file_put_contents(__DIR__.'/test_orders_booted.html', $response->getContent());
echo "Saved HTML\n";
