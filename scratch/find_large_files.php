<?php
$dirs = ['app', 'resources/views', 'routes'];
$projectRoot = $argv[1] ?? getcwd();

$largeFiles = [];

function scanDirRecursive($dir, &$largeFiles) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            scanDirRecursive($path, $largeFiles);
        } else {
            if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $lines = count(file($path));
                if ($lines > 500) {
                    $largeFiles[$path] = $lines;
                }
            }
        }
    }
}

foreach ($dirs as $d) {
    $path = $projectRoot . DIRECTORY_SEPARATOR . $d;
    if (file_exists($path)) {
        scanDirRecursive($path, $largeFiles);
    }
}

arsort($largeFiles);

if (empty($largeFiles)) {
    echo "✅ No files over 500 lines found!\n";
} else {
    echo "Files with > 500 lines:\n";
    foreach ($largeFiles as $file => $lines) {
        $relPath = str_replace($projectRoot . DIRECTORY_SEPARATOR, '', $file);
        echo str_pad($lines, 5) . " lines : " . $relPath . "\n";
    }
}
