<?php
$filePath = 'resources/views/orders/partials/workflow_actions.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/orders/partials/workflow_actions';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Banner
$bStart = "    <!-- Status Banner -->";
$bEnd = "    <!-- Modals -->";
$startPos = strpos($content, $bStart);
$endPos = strpos($content, $bEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/banner.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('orders.partials.workflow_actions.banner')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Modals
$mStart = "    <!-- Modals -->";
$mEnd = "    <!-- Script logic -->";
$startPos = strpos($content, $mStart);
$endPos = strpos($content, $mEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/modals.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('orders.partials.workflow_actions.modals')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Scripts
$sStart = "    <!-- Script logic -->";
$sEnd = "</div>";
$startPos = strpos($content, $sStart);
$endPos = strrpos($content, $sEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/scripts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('orders.partials.workflow_actions.scripts')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Workflow actions refactored!\n";
