<?php
$filePath = 'resources/views/customer/dashboard/show.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/customer/dashboard/partials';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Timeline
$tlStart = "    <!-- Horizontal Status Timeline -->";
$tlEnd = "    <!-- Action Required: Payment Banner -->";
$startPos = strpos($content, $tlStart);
$endPos = strpos($content, $tlEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/timeline.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('customer.dashboard.partials.timeline')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Payment Banner
$bannerStart = "    <!-- Action Required: Payment Banner -->";
$bannerEnd = "    <div class=\"grid grid-cols-1 lg:grid-cols-3 gap-6\">";
$startPos = strpos($content, $bannerStart);
$endPos = strpos($content, $bannerEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/payment-banner.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('customer.dashboard.partials.payment-banner')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Order Items
$itemsStart = "            <!-- Order Items -->";
$itemsEnd = "            <!-- Notes -->";
$startPos = strpos($content, $itemsStart);
$endPos = strpos($content, $itemsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/order-items.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('customer.dashboard.partials.order-items')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Notes
$notesStart = "            <!-- Notes -->";
$notesEnd = "        </div>\n\n        <!-- Right Column: Timeline & Branch -->";
$startPos = strpos($content, $notesStart);
$endPos = strpos($content, $notesEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/notes.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('customer.dashboard.partials.notes')\n", $startPos, $endPos - $startPos);
}

// 5. Extract Branch Info
$branchStart = "            <!-- Branch Info -->";
$branchEnd = "        </div>\n    </div>\n\n    <!-- Payment Modal -->";
$startPos = strpos($content, $branchStart);
$endPos = strpos($content, $branchEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/branch-info.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('customer.dashboard.partials.branch-info')\n", $startPos, $endPos - $startPos);
}

// 6. Extract Payment Modal
$modalStart = "    <!-- Payment Modal -->";
$modalEnd = "</x-customer-layout>";
$startPos = strpos($content, $modalStart);
$endPos = strpos($content, $modalEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/payment-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('customer.dashboard.partials.payment-modal')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Customer dashboard refactored!\n";
