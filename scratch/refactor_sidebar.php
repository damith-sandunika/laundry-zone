<?php
$filePath = 'resources/views/layouts/partials/sidebar.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/layouts/partials/sidebar';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Header
$hStart = "            <!-- Brand & Logo -->";
$hEnd = "            <!-- Nav Links -->";
$startPos = strpos($content, $hStart);
$endPos = strpos($content, $hEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/header.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('layouts.partials.sidebar.header')\n\n", $startPos, $endPos - $startPos);
}

// 2. Extract Super Admin
$saStart = "                {{-- â”€â”€ SUPER ADMIN â”€â”€ --}}";
$saEnd = "                {{-- â”€â”€ RIDER â”€â”€ --}}";
$startPos = strpos($content, $saStart);
$endPos = strpos($content, $saEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/menu-super-admin.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                @include('layouts.partials.sidebar.menu-super-admin')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Rider
$rStart = "                {{-- â”€â”€ RIDER â”€â”€ --}}";
$rEnd = "                {{-- ── WORKSHOP ADMIN / STAFF ── --}}";
$startPos = strpos($content, $rStart);
$endPos = strpos($content, $rEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/menu-rider.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                @include('layouts.partials.sidebar.menu-rider')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Workshop Admin / Staff
$wStart = "                {{-- ── WORKSHOP ADMIN / STAFF ── --}}";
$wEnd = "                {{-- ── BRANCH ADMIN / ADMIN / STAFF ── --}}";
$startPos = strpos($content, $wStart);
$endPos = strpos($content, $wEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/menu-workshop.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                @include('layouts.partials.sidebar.menu-workshop')\n\n", $startPos, $endPos - $startPos);
}

// 5. Extract Branch Admin / Admin / Staff
$bStart = "                {{-- ── BRANCH ADMIN / ADMIN / STAFF ── --}}";
$bEnd = "            </nav>\n\n            <!-- Footer -->";
$startPos = strpos($content, $bStart);
$endPos = strpos($content, $bEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/menu-branch.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "                @include('layouts.partials.sidebar.menu-branch')\n", $startPos, $endPos - $startPos);
}

// 6. Extract Footer
$fStart = "            <!-- Footer -->";
$fEnd = "        </aside>";
$startPos = strpos($content, $fStart);
$endPos = strpos($content, $fEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/footer.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('layouts.partials.sidebar.footer')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Sidebar refactored!\n";
