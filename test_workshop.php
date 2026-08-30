<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dispatch = \App\Models\WorkshopDispatch::latest()->first();
if (!$dispatch) {
    echo "No dispatches found.\n";
    exit;
}

echo "Testing startProcessing on dispatch " . $dispatch->id . "\n";

$controller = app(\App\Http\Controllers\WorkshopController::class);

try {
    $response = $controller->startProcessing($dispatch);
    echo "Response:\n";
    print_r($response->getContent());
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
