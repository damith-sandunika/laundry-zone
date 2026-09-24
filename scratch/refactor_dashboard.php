<?php
$filePath = 'resources/views/dashboard.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/dashboard/partials';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Stat Cards
$statCardsStart = "{{-- ── STAT CARDS ── --}}";
$statCardsEnd = "        </div>\n\n        {{-- Pending Approvals --}}";
$startPos = strpos($content, $statCardsStart);
$endPos = strpos($content, $statCardsEnd) + strlen("        </div>\n");
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/stat-cards.blade.php", $block);
    $content = substr_replace($content, "@include('dashboard.partials.stat-cards')\n", $startPos, $endPos - $startPos);
}

// 2. Extract Pending Approvals
$pendingApprovalsStart = "{{-- Pending Approvals --}}";
$pendingApprovalsEnd = "        @endif\n\n        {{-- ✨ MAIN GRID ✨ --}}";
$startPos = strpos($content, $pendingApprovalsStart);
$endPos = strpos($content, $pendingApprovalsEnd) + strlen("        @endif\n");
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/pending-approvals.blade.php", $block);
    $content = substr_replace($content, "@include('dashboard.partials.pending-approvals')\n", $startPos, $endPos - $startPos);
}

// 3. Extract Active Orders
$activeOrdersStart = "{{-- Recent Orders --}}";
$activeOrdersEnd = "            {{-- Chart --}}";
$startPos = strpos($content, $activeOrdersStart);
$endPos = strpos($content, $activeOrdersEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/active-orders.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('dashboard.partials.active-orders')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Performance Chart
$chartStart = "{{-- Chart --}}";
$chartEnd = "        </div>\n\n        {{-- ── BOTTOM GRID ── --}}";
$startPos = strpos($content, $chartStart);
$endPos = strpos($content, $chartEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/performance-chart.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('dashboard.partials.performance-chart')\n", $startPos, $endPos - $startPos);
}

// 5. Extract Inventory Alerts
$invStart = "{{-- Inventory Alerts --}}";
$invEnd = "            {{-- Shift Register (Dark Card) --}}";
$startPos = strpos($content, $invStart);
$endPos = strpos($content, $invEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/inventory-alerts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('dashboard.partials.inventory-alerts')\n\n", $startPos, $endPos - $startPos);
}

// 6. Extract Shift Register
$srStart = "{{-- Shift Register (Dark Card) --}}";
$srEnd = "        </div>\n\n        {{-- Active Staff Attendance & Assignment --}}";
$startPos = strpos($content, $srStart);
$endPos = strpos($content, $srEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/shift-register.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "            @include('dashboard.partials.shift-register')\n", $startPos, $endPos - $startPos);
}

// 7. Extract Staff Attendance
$saStart = "{{-- Active Staff Attendance & Assignment --}}";
$saEnd = "    @endif\n\n    <!-- Bank Deposit Modal -->";
$startPos = strpos($content, $saStart);
$endPos = strpos($content, $saEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/staff-attendance.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('dashboard.partials.staff-attendance')\n\n", $startPos, $endPos - $startPos);
}

// 8. Extract Deposit Modal
$dmStart = "    <!-- Bank Deposit Modal -->";
$dmEnd = "    <x-slot name=\"scripts\">";
$startPos = strpos($content, $dmStart);
$endPos = strpos($content, $dmEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/deposit-modal.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('dashboard.partials.deposit-modal')\n\n", $startPos, $endPos - $startPos);
}

// 9. Extract Scripts
$scStart = "    <x-slot name=\"scripts\">";
$scEnd = "</x-app-layout>";
$startPos = strpos($content, $scStart);
$endPos = strpos($content, $scEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/scripts.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('dashboard.partials.scripts')\n", $startPos, $endPos - $startPos);
}

// 10. Extract Shift Closed
$scldStart = "        {{-- ── Shift Closed Card ── --}}";
$scldEnd = "    @else\n\n        @include('dashboard.partials.stat-cards')";
$startPos = strpos($content, $scldStart);
$endPos = strpos($content, $scldEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/shift-closed.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('dashboard.partials.shift-closed')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Dashboard refactored!\n";
