<?php
$filePath = 'resources/views/reports/index.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/reports/partials';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Filters
$filtersStart = "    <!-- Filters Section -->";
$filtersEnd = "    <!-- Key Metrics Cards -->";
$startPos = strpos($content, $filtersStart);
$endPos = strpos($content, $filtersEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/filters.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('reports.partials.filters')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Key Metrics
$metricsStart = "    <!-- Key Metrics Cards -->";
$metricsEnd = "    <!-- Charts Section -->";
$startPos = strpos($content, $metricsStart);
$endPos = strpos($content, $metricsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/key-metrics.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('reports.partials.key-metrics')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Charts
$chartsStart = "    <!-- Charts Section -->";
$chartsEnd = "    <!-- Reports Content Wrapper -->";
$startPos = strpos($content, $chartsStart);
$endPos = strpos($content, $chartsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/charts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('reports.partials.charts')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Tab Header
$tabsStart = "        <!-- Premium Horizontally Scrollable Tab Header -->";
$tabsEnd = "        <div>\n            <!-- CREDIT ORDERS LOG -->";
$startPos = strpos($content, $tabsStart);
$endPos = strpos($content, $tabsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tabs.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('reports.partials.tabs')\n\n", $startPos, $endPos - $startPos);
}

// 5. Extract Credit Orders
$creditStart = "            <!-- CREDIT ORDERS LOG -->";
$creditEnd = "            <!-- PETTY CASH LOG -->";
$startPos = strpos($content, $creditStart);
$endPos = strpos($content, $creditEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tab-credit-orders.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('reports.partials.tab-credit-orders')\n\n", $startPos, $endPos - $startPos);
}

// 6. Extract Petty Cash Log
$pettyStart = "            <!-- PETTY CASH LOG -->";
$pettyEnd = "            <!-- INVENTORY USAGE LOG -->";
$startPos = strpos($content, $pettyStart);
$endPos = strpos($content, $pettyEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tab-expenses.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('reports.partials.tab-expenses')\n\n", $startPos, $endPos - $startPos);
}

// 7. Extract Inventory Usage
$invStart = "            <!-- INVENTORY USAGE LOG -->";
$invEnd = "            <!-- PETTY CASH SUMMARY (Super Admin) -->";
$startPos = strpos($content, $invStart);
$endPos = strpos($content, $invEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tab-inventory.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('reports.partials.tab-inventory')\n\n", $startPos, $endPos - $startPos);
}

// 8. Extract Petty Cash Summary
$summaryStart = "            <!-- PETTY CASH SUMMARY (Super Admin) -->";
$summaryEnd = "            <!-- BANK DEPOSITS LOG -->";
$startPos = strpos($content, $summaryStart);
$endPos = strpos($content, $summaryEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tab-petty-summary.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('reports.partials.tab-petty-summary')\n\n", $startPos, $endPos - $startPos);
}

// 9. Extract Bank Deposits
$depStart = "            <!-- BANK DEPOSITS LOG -->";
$depEnd = "        </div>\n\n        <!-- Pending Orders Modal -->";
$startPos = strpos($content, $depStart);
$endPos = strpos($content, $depEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/tab-bank-deposits.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('reports.partials.tab-bank-deposits')\n", $startPos, $endPos - $startPos);
}

// 10. Extract Modals
$modalsStart = "        <!-- Pending Orders Modal -->";
$modalsEnd = "    </div>\n\n    <script>";
$startPos = strpos($content, $modalsStart);
$endPos = strpos($content, $modalsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/modals.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('reports.partials.modals')\n", $startPos, $endPos - $startPos);
}

// 11. Extract Scripts
$scStart = "    <script>";
$scEnd = "</x-app-layout>";
$startPos = strpos($content, $scStart);
$endPos = strpos($content, $scEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/scripts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('reports.partials.scripts')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Reports index refactored!\n";
