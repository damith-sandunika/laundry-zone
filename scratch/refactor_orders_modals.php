<?php
$filePath = 'resources/views/orders/partials/modals.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/orders/partials/modals';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Ensure the first modal has its opening tag if it's missing
if (str_starts_with(trim($content), 'class="fixed')) {
    $content = "<div x-cloak x-show=\"showBulkModal\"\n" . $content;
}

// 1. Extract Bulk Dispatch Modal
$bdStart = "<div x-cloak x-show=\"showBulkModal\"";
$bdEnd = "            <!-- ── Reverse Authorization Modal ── -->";
$startPos = strpos($content, $bdStart);
$endPos = strpos($content, $bdEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/bulk-dispatch.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('orders.partials.modals.bulk-dispatch')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Reverse Auth Modal
$raStart = "            <!-- ── Reverse Authorization Modal ── -->";
$raEnd = "            <!-- ── Mark as Done (Cubbet Assign) Modal ── -->";
$startPos = strpos($content, $raStart);
$endPos = strpos($content, $raEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/reverse-authorization.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('orders.partials.modals.reverse-authorization')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Cubbet Assign Modal
$caStart = "            <!-- ── Mark as Done (Cubbet Assign) Modal ── -->";
$caEnd = "            <!-- ── Delivery & Payment Modal ── -->";
$startPos = strpos($content, $caStart);
$endPos = strpos($content, $caEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/mark-done-cubbet.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('orders.partials.modals.mark-done-cubbet')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Delivery & Payment Modal
$dpStart = "            <!-- ── Delivery & Payment Modal ── -->";
$dpEnd = "        </div>\n\n        <!-- ── Inline Edit / Correction Modal ── -->";
$startPos = strpos($content, $dpStart);
$endPos = strpos($content, $dpEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/delivery-payment.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('orders.partials.modals.delivery-payment')\n", $startPos, $endPos - $startPos);
}

// 5. Extract Inline Edit / Correction Modal
$ieStart = "        <!-- ── Inline Edit / Correction Modal ── -->";
$ieEnd = "        <!-- ── Dispatch Delivery Modal ── -->";
$startPos = strpos($content, $ieStart);
$endPos = strpos($content, $ieEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/inline-edit.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('orders.partials.modals.inline-edit')\n\n", $startPos, $endPos - $startPos);
}

// 6. Extract Dispatch Delivery Modal
$ddStart = "        <!-- ── Dispatch Delivery Modal ── -->";
$ddEnd = "        <x-modals.request-payment />";
$startPos = strpos($content, $ddStart);
$endPos = strpos($content, $ddEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/dispatch-delivery.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('orders.partials.modals.dispatch-delivery')\n\n", $startPos, $endPos - $startPos);
}

// Remove the dangling `        </div>\n\n` from between Delivery & Payment and Inline Edit, which was probably part of a wrapper that no longer exists properly.
// But wait, the `</div>` before `<!-- ── Inline Edit / Correction Modal ── -->` might be closing some wrapper in `orders/index.blade.php`.
// Let's leave it as is to be safe, I just extracted the modal blocks exactly as they are.

file_put_contents($filePath, $content);
echo "Modals refactored!\n";
