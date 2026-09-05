<?php

$files = glob(__DIR__ . "/app/Models/*.php");

foreach ($files as $file) {
    if (basename($file) === "User.php") continue;
    $content = file_get_contents($file);
    $newContent = preg_replace('/(?s)protected\s+\$fillable\s*=\s*\[.*?\];/', "protected \$guarded = ['id'];", $content);
    if ($newContent !== null && $newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Fixed: " . basename($file) . "\n";
    }
}
