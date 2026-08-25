<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(1);
$request = Illuminate\Http\Request::create('/pos', 'GET');
$response = app()->handle($request);
file_put_contents('pos_output.html', $response->getContent());
echo "Done";
