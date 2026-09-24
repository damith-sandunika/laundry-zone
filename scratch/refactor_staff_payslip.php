<?php
$filePath = 'resources/views/staff/payslip.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/staff/partials/payslip';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Extract Styles
$sStart = "    <style>";
$sEnd = "</head>";
$startPos = strpos($content, $sStart);
$endPos = strpos($content, $sEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/styles.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('staff.partials.payslip.styles')\n", $startPos, $endPos - $startPos);
}

// 2. Extract Header
$hStart = "    <div class=\"no-print\">";
$hEnd = "        <!-- Info Grid -->";
$startPos = strpos($content, $hStart);
$endPos = strpos($content, $hEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/header.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "    @include('staff.partials.payslip.header')\n\n", $startPos, $endPos - $startPos);
}

// 3. Extract Info Grid
$igStart = "        <!-- Info Grid -->";
$igEnd = "        <!-- Detailed Statement Table -->";
$startPos = strpos($content, $igStart);
$endPos = strpos($content, $igEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/info-grid.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('staff.partials.payslip.info-grid')\n\n", $startPos, $endPos - $startPos);
}

// 4. Extract Statement Table
$stStart = "        <!-- Detailed Statement Table -->";
$stEnd = "        <!-- Net Payable Salary Box -->";
$startPos = strpos($content, $stStart);
$endPos = strpos($content, $stEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/statement-table.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('staff.partials.payslip.statement-table')\n\n", $startPos, $endPos - $startPos);
}

// 5. Extract Net Salary
$nsStart = "        <!-- Net Payable Salary Box -->";
$nsEnd = "        <!-- Worked Days Breakdown Log -->";
$startPos = strpos($content, $nsStart);
$endPos = strpos($content, $nsEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/net-salary.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('staff.partials.payslip.net-salary')\n\n", $startPos, $endPos - $startPos);
}

// 6. Extract Worked Days
$wdStart = "        <!-- Worked Days Breakdown Log -->";
$wdEnd = "        <!-- Signatures -->";
$startPos = strpos($content, $wdStart);
$endPos = strpos($content, $wdEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/worked-days.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('staff.partials.payslip.worked-days')\n\n", $startPos, $endPos - $startPos);
}

// 7. Extract Footer
$fStart = "        <!-- Signatures -->";
$fEnd = "    </div>\n\n</body>";
$startPos = strpos($content, $fStart);
$endPos = strpos($content, $fEnd);
if ($startPos !== false && $endPos !== false) {
    $block = substr($content, $startPos, $endPos - $startPos);
    file_put_contents("$dir/footer.blade.php", rtrim($block) . "\n");
    $content = substr_replace($content, "        @include('staff.partials.payslip.footer')\n", $startPos, $endPos - $startPos);
}

file_put_contents($filePath, $content);
echo "Staff payslip refactored!\n";
