<?php
$url = 'http://127.0.0.1:8000/';
$html = @file_get_contents($url);
// Check for unbalanced script tags
preg_match_all('/<script\b[^>]*>/i', $html, $opens);
preg_match_all('/<\/script>/i', $html, $closes);
echo "Open <script> tags: " . count($opens[0]) . "\n";
echo "Close </script> tags: " . count($closes[0]) . "\n";
if (count($opens[0]) !== count($closes[0])) {
    echo "MISMATCH! Script tags not balanced.\n";
} else {
    echo "All script tags balanced OK\n";
}
