<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LAST 15 EMAIL LOGS ===\n";
$logs = \App\Models\EmailLog::latest()->take(15)->get();
foreach ($logs as $l) {
    echo $l->created_at . ' | ' . $l->type . ' | ' . $l->status . ' | ' . substr($l->response ?? '', 0, 80) . "\n";
}

echo "\n=== ORDER STATUS HISTORY (last 10) ===\n";
$hist = \App\Models\OrderStatusHistory::latest()->take(10)->get();
foreach ($hist as $h) {
    echo $h->created_at . ' | order_id=' . $h->order_id . ' | ' . $h->status . ' | changed_by=' . $h->changed_by . "\n";
}
