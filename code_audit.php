<?php
// Code Quality Audit script

$dirs = [
    'app/Http/Controllers',
    'app/Actions',
    'app/Services',
    'app/Jobs',
];

echo "=== Try-Catch Coverage ===\n";
foreach ($dirs as $dir) {
    $files = glob("$dir/*.php");
    if (empty($files)) {
        $files = glob("$dir/**/*.php");
    }
    foreach ($files as $f) {
        $code = file_get_contents($f);
        $tries = substr_count($code, 'try {') + substr_count($code, 'try{');
        $methods = substr_count($code, 'public function ');
        $dbTransaction = substr_count($code, 'DB::transaction');
        $size = round(filesize($f)/1024, 1);
        if ($methods > 0) {
            echo basename($f) . ": {$size}KB | {$methods} methods | {$tries} try-catch | {$dbTransaction} DB::transaction\n";
        }
    }
}

echo "\n=== Duplicate branchId Resolution Pattern ===\n";
$allControllers = glob('app/Http/Controllers/*.php');
$count = 0;
foreach ($allControllers as $f) {
    $code = file_get_contents($f);
    if (str_contains($code, 'branch_id ?? \App\Models\Branch::first()->id')) {
        $occurrences = substr_count($code, 'branch_id ?? \App\Models\Branch::first()->id');
        echo basename($f) . ": {$occurrences} occurrences\n";
        $count++;
    }
}
echo "Total files with fallback pattern: $count\n";

echo "\n=== N+1 Query Risk (missing eager loads) ===\n";
foreach ($allControllers as $f) {
    $code = file_get_contents($f);
    // Look for ->get() without ->with()
    if (preg_match_all('/->(?:orderBy|where|whereBetween)[^;]+->get\(\)/', $code, $matches)) {
        foreach ($matches[0] as $m) {
            if (!str_contains($m, '->with(')) {
                echo basename($f) . ": Possible missing eager load\n";
                break;
            }
        }
    }
}
