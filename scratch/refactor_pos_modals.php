<?php
$filePath = 'resources/views/pos/partials/modals.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/pos/partials/modals';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Checkout
$coStart = "<!-- ===================== Checkout Modal ===================== -->";
$coEnd = "        <!-- ===================== Add New Customer Modal ===================== -->";
$startPos = strpos($content, $coStart);
$endPos = strpos($content, $coEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/checkout.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "@include('pos.partials.modals.checkout')\n\n", $startPos, $endPos - $startPos);
}

// 2. Add New Customer
$ncStart = "<!-- ===================== Add New Customer Modal ===================== -->";
$ncEnd = "        <!-- ===================== Order Success Modal ===================== -->";
$startPos = strpos($content, $ncStart);
$endPos = strpos($content, $ncEnd);
if ($startPos !== false && $endPos !== false) {
    // Need to handle indentation if $ncStart is indented, but I search without indentation in $ncStart.
    // Let me find the exact position
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/new-customer.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('pos.partials.modals.new-customer')\n\n", $startPos, $endPos - $startPos);
}

// 3. Order Success
$osStart = "<!-- ===================== Order Success Modal ===================== -->";
$osEnd = "        <!-- ===================== Package Order Modal ===================== -->";
$startPos = strpos($content, $osStart);
$endPos = strpos($content, $osEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/order-success.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('pos.partials.modals.order-success')\n\n", $startPos, $endPos - $startPos);
}

// 4. Package Order
$poStart = "<!-- ===================== Package Order Modal ===================== -->";
$poEnd = "    </div>\n\n    <!-- ===================== Delivery Request Modal ===================== -->";
$startPos = strpos($content, $poStart);
$endPos = strpos($content, $poEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/package-order.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('pos.partials.modals.package-order')\n", $startPos, $endPos - $startPos);
}

// 5. Delivery Request Modal
$drStart = "<!-- ===================== Delivery Request Modal ===================== -->";
// Since it's the last one, we just take until the end.
$startPos = strpos($content, $drStart);
if ($startPos !== false) {
    $block = substr($content, $startPos);
    file_put_contents("$dir/delivery-request.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('pos.partials.modals.delivery-request')\n", $startPos);
}

file_put_contents($filePath, $content);
echo "POS Modals refactored!\n";
