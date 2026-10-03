<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 6;
$month = 9;
$year = 2026;

$attendances = \App\Models\Attendance::where('user_id', $userId)
    ->whereMonth('date', $month)
    ->whereYear('date', $year)
    ->get();

echo "ATTENDANCES FOR USER $userId IN $month/$year:\n";
$totalMins = 0;
foreach($attendances as $att) {
    echo "Date: {$att->date}, Status: {$att->status}, Normal Mins: {$att->normal_minutes}, OT Mins: {$att->ot_minutes}\n";
    if ($att->status === 'present') {
        $totalMins += $att->normal_minutes > 0 ? $att->normal_minutes : (8 * 60);
    }
}
echo "Total Computed Mins: $totalMins\n";
