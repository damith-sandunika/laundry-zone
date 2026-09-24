<?php
$filePath = 'resources/views/pos/partials/scripts/pos-system.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/pos/partials/scripts';

// Split the pos-system.blade.php (587 lines) into 3 logical sub-sections:
// 1. data-init.blade.php   - The window.posSystemData + posSystem function start + state props + init()
// 2. search-customer.blade.php - fast search methods + customer methods + cart methods
// 3. order.blade.php       - placeOrder, placePackageOrder, openDeliveryModal, closing tags

$lines = explode("\n", $content);

// Find key anchors
$searchStart = null;
$orderStart = null;

foreach ($lines as $i => $line) {
    if ($searchStart === null && str_contains($line, 'filterCustomers()')) {
        $searchStart = $i;
    }
    if ($orderStart === null && str_contains($line, 'placePackageOrder()')) {
        $orderStart = $i;
    }
}

$block1 = implode("\n", array_slice($lines, 0, $searchStart));
$block2 = implode("\n", array_slice($lines, $searchStart, $orderStart - $searchStart));
$block3 = implode("\n", array_slice($lines, $orderStart));

// We need to be careful: the <script> and </script> tags are in block1 and block3.
// Let's keep them there. Block2 and block3 are just partial JS inside the object literal.

file_put_contents("$dir/pos-data-init.blade.php", rtrim($block1) . "\n");
file_put_contents("$dir/pos-search-customer.blade.php", rtrim($block2) . "\n");
file_put_contents("$dir/pos-order.blade.php", rtrim($block3) . "\n");

// Rewrite pos-system.blade.php
$newContent = "@include('pos.partials.scripts.pos-data-init')\n"
            . "@include('pos.partials.scripts.pos-search-customer')\n"
            . "@include('pos.partials.scripts.pos-order')\n";
file_put_contents($filePath, $newContent);

echo "pos-data-init.blade.php: " . count(file("$dir/pos-data-init.blade.php")) . " lines\n";
echo "pos-search-customer.blade.php: " . count(file("$dir/pos-search-customer.blade.php")) . " lines\n";
echo "pos-order.blade.php: " . count(file("$dir/pos-order.blade.php")) . " lines\n";
echo "POS system split done!\n";
