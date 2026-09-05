<?php
$files = glob('app/Http/Controllers/*.php');
$sizes = [];
foreach ($files as $f) {
    $sizes[] = ['name' => basename($f), 'kb' => round(filesize($f)/1024, 1)];
}
usort($sizes, function($a, $b) { return $b['kb'] <=> $a['kb']; });
foreach (array_slice($sizes, 0, 15) as $s) {
    echo $s['name'] . ': ' . $s['kb'] . "KB\n";
}

echo "\n--- Actions ---\n";
$actions = glob('app/Actions/**/*.php');
foreach ($actions as $f) {
    $sizes2[] = ['name' => basename($f), 'kb' => round(filesize($f)/1024, 1)];
}
usort($sizes2, function($a, $b) { return $b['kb'] <=> $a['kb']; });
foreach (array_slice($sizes2, 0, 8) as $s) {
    echo $s['name'] . ': ' . $s['kb'] . "KB\n";
}
