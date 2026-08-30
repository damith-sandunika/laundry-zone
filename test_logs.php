<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ORDERS 11-15 STATUS ===\n";
$orders = \App\Models\Order::whereIn('id', [11,12,13,14,15])->get(['id','order_number','status']);
foreach ($orders as $o) {
    echo "Order " . $o->id . " (" . $o->order_number . "): status='" . $o->status . "'\n";
}

echo "\n=== STATUS HISTORY FOR ORDERS 12-14 ===\n";
$hist = \App\Models\OrderStatusHistory::whereIn('order_id',[12,13,14])
    ->orderBy('id','asc')
    ->get();
foreach ($hist as $h) {
    echo $h->created_at . ' | order=' . $h->order_id . ' | status="' . $h->status . '" | note="' . $h->note . '" | by=' . $h->changed_by . "\n";
}

echo "\n=== EMAIL LOGS TODAY ===\n";
$emails = \App\Models\EmailLog::whereDate('created_at', today())->orderBy('id','asc')->get(['created_at','type','status','response']);
foreach ($emails as $e) {
    echo $e->created_at . ' | ' . $e->type . ' | ' . $e->status . ' | ' . substr($e->response ?? '', 0, 60) . "\n";
}

echo "\n=== LARAVEL LOG (last updateStatus entries) ===\n";
$log = file_get_contents(storage_path('logs/laravel.log'));
preg_match_all('/\[.*?\] local\.(INFO|ERROR): (updateStatus|Email|Triggering)[^\n]*/', $log, $m);
foreach ($m[0] as $line) {
    echo $line . "\n";
}
