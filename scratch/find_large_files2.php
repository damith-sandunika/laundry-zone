<?php

$dir = 'resources/views';
$results = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') continue;
    $lines = count(file($file->getPathname()));
    if ($lines > 100) {
        $results[] = [$lines, str_replace('\\', '/', $file->getPathname())];
    }
}

usort($results, fn($a, $b) => $b[0] - $a[0]);

foreach ($results as [$lines, $path]) {
    echo str_pad($lines, 5) . " lines  " . $path . "\n";
}
