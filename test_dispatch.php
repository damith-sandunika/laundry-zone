<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dispatch = \App\Models\WorkshopDispatch::latest('id')->first();
echo json_encode($dispatch);
