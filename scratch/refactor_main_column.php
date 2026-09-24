<?php
$filePath = 'resources/views/orders/partials/main_column.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/orders/partials/main_column';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Order Journey
$ojStart = "            <!-- Status Progress -->";
$ojEnd = "                        <!-- Right Side: Details -->";
$startPos = strpos($content, $ojStart);
$endPos = strpos($content, $ojEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/order-journey.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('orders.partials.main_column.order-journey')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Order Details
$odStart = "                        <!-- Right Side: Details -->";
$odEnd = "                <!-- Laundry Items Card -->";
$startPos = strpos($content, $odStart);
$endPos = strpos($content, $odEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    
    // The details block is inside the same flex row as Order Journey, so wait, let me look at the structure.
    // Order Journey opens a div with padding 16px, then flex-col md:flex-row gap-8, then flex-1 relative (journey).
    // Right side details is inside the flex-col md:flex-row.
    // So if I extract them, the wrapper remains in main_column.blade.php? 
    // Wait, the $ojEnd is `<!-- Right Side: Details -->`. That means I extracted both the wrapper start and the journey.
    // That's fine, I can just extract the inner block for details.
    
    file_put_contents("$dir/order-details.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                        @include('orders.partials.main_column.order-details')\n", $startPos, $endPos - $startPos);
}

// 3. Extract Laundry Items
$liStart = "                <!-- Laundry Items Card -->";
$liEnd = "        </div>"; // end of main column wrapper
$startPos = strpos($content, $liStart);
$endPos = strrpos($content, $liEnd); // find last occurrence just to be safe
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/laundry-items.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                @include('orders.partials.main_column.laundry-items')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Main column refactored!\n";
