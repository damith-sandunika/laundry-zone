<?php
$filePath = 'resources/views/pos/partials/scripts.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/pos/partials/scripts';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Ensure we don't duplicate <script> tags, but since they will be included sequentially, 
// we will just wrap each part in its own <script> tag and remove the outer one from the main file.
// Or we can just extract the blocks and wrap them.

$content = str_replace('<script>', '', $content);
$content = str_replace('</script>', '', $content);
$content = trim($content);

// Find blocks
$deliveryStart = "// Delivery Modal Component";
$printStart = "function printPopup(url) {";

$posSystemCode = substr($content, 0, strpos($content, $deliveryStart));
$deliveryCode = substr($content, strpos($content, $deliveryStart), strpos($content, $printStart) - strpos($content, $deliveryStart));

// The comment for print might have been handled differently, but we can search for the printPopup function
$printCode = substr($content, strpos($content, $printStart));

// Also the delivery code has a comment about the save-customer-btn, let's include it in the delivery code or print code?
// Let's just find the exact positions.

$delStartPos = strpos($content, $deliveryStart);
$printStartPos = strpos($content, $printStart);

$posSystemCode = substr($content, 0, $delStartPos);
// we need to include the comment above print in delivery or somewhere.
$deliveryCode = substr($content, $delStartPos, $printStartPos - $delStartPos);

// Actually, there's a comment `// The save-customer-btn listener is mostly handled...` just before `function printPopup(url)`.
// Let's find that comment.
$commentStart = "// The save-customer-btn";
$commentPos = strpos($content, $commentStart);
if ($commentPos !== false && $commentPos < $printStartPos) {
    $deliveryCode = substr($content, $delStartPos, $commentPos - $delStartPos);
    $printCode = substr($content, $commentPos);
} else {
    $printCode = substr($content, $printStartPos);
}

// Now we wrap each in <script> and save
file_put_contents("$dir/pos-system.blade.php", "<script>\n" . trim($posSystemCode) . "\n</script>\n");
file_put_contents("$dir/delivery-modal.blade.php", "<script>\n" . trim($deliveryCode) . "\n</script>\n");
file_put_contents("$dir/print.blade.php", "<script>\n" . trim($printCode) . "\n</script>\n");

// Re-write the main file to just include them
$newContent = "@include('pos.partials.scripts.pos-system')\n"
            . "@include('pos.partials.scripts.delivery-modal')\n"
            . "@include('pos.partials.scripts.print')\n";

file_put_contents($filePath, $newContent);

echo "POS scripts refactored!\n";
