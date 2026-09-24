<?php
$html = file_get_contents(__DIR__ . '/test_customer_book.html');
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches);
foreach ($matches[1] as $index => $script) {
    if (trim($script) === '') continue;
    $filename = __DIR__ . '/script_' . $index . '.js';
    file_put_contents($filename, $script);
    exec("node -c \"$filename\" 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        echo "Syntax error in script $index:\n";
        echo implode("\n", $output) . "\n";
    }
}
echo "Done checking scripts.\n";
