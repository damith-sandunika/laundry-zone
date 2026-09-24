<?php
$filePath = 'resources/views/workshop/finalize_bill.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/workshop/partials/finalize_bill';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Styles
$sStart = "    <style>";
$sEnd = "    <!-- Wrapping to override default app layout padding for a full height POS -->";
$startPos = strpos($content, $sStart);
$endPos = strpos($content, $sEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/styles.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('workshop.partials.finalize_bill.styles')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Services Left
$slStart = "            <!-- ===================== Left: Services ===================== -->";
$slEnd = "            <!-- ===================== Right: Order Panel ===================== -->";
$startPos = strpos($content, $slStart);
$endPos = strpos($content, $slEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/services-left.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('workshop.partials.finalize_bill.services-left')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Order Panel Right
$oprStart = "            <!-- ===================== Right: Order Panel ===================== -->";
$oprEnd = "        </div>\n    </div>\n\n    <script>";
$startPos = strpos($content, $oprStart);
$endPos = strpos($content, $oprEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/order-panel-right.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('workshop.partials.finalize_bill.order-panel-right')\n", $startPos, $endPos - $startPos);
}

// 4. Extract Scripts
$scStart = "    <script>";
$scEnd = "</x-app-layout>";
$startPos = strpos($content, $scStart);
$endPos = strpos($content, $scEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/scripts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('workshop.partials.finalize_bill.scripts')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Workshop finalize bill refactored!\n";
