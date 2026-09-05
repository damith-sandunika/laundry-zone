<?php
$files = glob('app/Http/Controllers/*.php');
$search = 'auth()->user()->branch_id ?? \App\Models\Branch::first()->id';
$replace = '$this->getActiveBranchId()';

foreach ($files as $f) {
    if (basename($f) === 'Controller.php') continue;
    $content = file_get_contents($f);
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($f, $content);
        echo "Updated " . basename($f) . "\n";
    }
}
