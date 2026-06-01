<?php
$path = 'routes/web.php';
$raw = file_get_contents($path);

// Remove null bytes and BOM
$clean = str_replace("\x00", '', $raw);
$clean = preg_replace('/^\xEF\xBB\xBF/', '', $clean);
$clean = ltrim($clean);

// Ensure it starts with <?php
if (!str_starts_with(ltrim($clean), '<?php')) {
    // Try to find where <?php starts
    $pos = strpos($clean, '<?php');
    if ($pos !== false) {
        $clean = substr($clean, $pos);
    }
}

file_put_contents($path, $clean);
echo "Fixed! Length: " . strlen($clean) . "\n";
echo "Starts with: " . substr($clean, 0, 10) . "\n";
