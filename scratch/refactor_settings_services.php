<?php
$filePath = 'resources/views/settings/services.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/settings/partials/services';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Categories List
$catStart = "            <div id=\"categories-sortable\" class=\"space-y-4\">";
$catEnd = "        </div>\n    </div>    <!-- Add Service Modal -->";
$startPos = strpos($content, $catStart);
$endPos = strpos($content, $catEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/categories-list.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('settings.partials.services.categories-list')\n", $startPos, $endPos - $startPos);
}

// 2. Extract Add Service Modal
$asmStart = "    <!-- Add Service Modal -->";
$asmEnd = "    <!-- Edit Service Modal -->";
$startPos = strpos($content, $asmStart);
$endPos = strpos($content, $asmEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/add-service-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('settings.partials.services.add-service-modal')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Edit Service Modal
$esmStart = "    <!-- Edit Service Modal -->";
$esmEnd = "    <!-- Add Category Modal -->";
$startPos = strpos($content, $esmStart);
$endPos = strpos($content, $esmEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/edit-service-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('settings.partials.services.edit-service-modal')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Add Category Modal
$acmStart = "    <!-- Add Category Modal -->";
$acmEnd = "    <!-- Edit Category Modal -->";
$startPos = strpos($content, $acmStart);
$endPos = strpos($content, $acmEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/add-category-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('settings.partials.services.add-category-modal')\n\n", $startPos, $endPos - $startPos);
}

// 5. Extract Edit Category Modal
$ecmStart = "    <!-- Edit Category Modal -->";
$ecmEnd = "    <script>";
$startPos = strpos($content, $ecmStart);
$endPos = strpos($content, $ecmEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/edit-category-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('settings.partials.services.edit-category-modal')\n", $startPos, $endPos - $startPos);
}

// 6. Extract Scripts
$scStart = "    <script>";
$scEnd = "</x-app-layout>";
$startPos = strpos($content, $scStart);
$endPos = strpos($content, $scEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/scripts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('settings.partials.services.scripts')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Settings services refactored!\n";
