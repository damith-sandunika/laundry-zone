<?php
/**
 * Full Refactoring Test Suite
 * Tests all blade @include paths created during the refactoring session.
 */

$projectRoot = 'e:\\Projects\\Done Projects\\360 Laundry';
$viewsRoot   = $projectRoot . '/resources/views';

$pass = 0;
$fail = 0;
$errors = [];

function ok($label) {
    global $pass;
    $pass++;
    echo "  ✅  $label\n";
}

function fail($label, $reason = '') {
    global $fail, $errors;
    $fail++;
    $msg = "  ❌  $label" . ($reason ? " — $reason" : '');
    echo $msg . "\n";
    $errors[] = $msg;
}

function assertFileExists($path, $label = null) {
    $label = $label ?? basename($path);
    if (file_exists($path)) {
        ok($label);
        return true;
    } else {
        fail($label, "File not found: $path");
        return false;
    }
}

function assertIncludesExist($parentFile, $viewsRoot) {
    if (!file_exists($parentFile)) {
        fail(basename($parentFile), "Parent not found: $parentFile");
        return;
    }
    $content = file_get_contents($parentFile);
    preg_match_all("/@include\(['\"]([^'\"]+)['\"]/", $content, $matches);
    foreach ($matches[1] as $viewDot) {
        $viewPath = $viewsRoot . '/' . str_replace('.', '/', $viewDot) . '.blade.php';
        if (file_exists($viewPath)) {
            ok("@include('$viewDot')");
        } else {
            fail("@include('$viewDot')", "Missing: $viewPath");
        }
    }
}

function checkNoBrokenIncludes($dir, $viewsRoot) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getRealPath());
        preg_match_all("/@include\(['\"]([^'\"]+)['\"]/", $content, $matches);
        foreach ($matches[1] as $viewDot) {
            // Skip component includes (x-...) and partials that use variables
            if (strpos($viewDot, '$') !== false) continue;
            $viewPath = $viewsRoot . '/' . str_replace('.', '/', $viewDot) . '.blade.php';
            if (!file_exists($viewPath)) {
                fail("@include('$viewDot')", "Missing partial: $viewPath\n     Referenced in: " . str_replace($viewsRoot, '', $file->getRealPath()));
            }
        }
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║         BLADE REFACTORING FULL TEST SUITE                ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

// ══════════════════════════════════════════════════════════════
// 1. ORDERS / MAIN COLUMN
// ══════════════════════════════════════════════════════════════
echo "── 1. orders/partials/main_column ───────────────────────────\n";
assertIncludesExist("$viewsRoot/orders/partials/main_column.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 2. ORDERS / MODALS
// ══════════════════════════════════════════════════════════════
echo "\n── 2. orders/partials/modals ────────────────────────────────\n";
assertIncludesExist("$viewsRoot/orders/partials/modals.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 3. ORDERS / WORKFLOW ACTIONS
// ══════════════════════════════════════════════════════════════
echo "\n── 3. orders/partials/workflow_actions ──────────────────────\n";
assertIncludesExist("$viewsRoot/orders/partials/workflow_actions.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 4. POS / MODALS
// ══════════════════════════════════════════════════════════════
echo "\n── 4. pos/partials/modals ───────────────────────────────────\n";
assertIncludesExist("$viewsRoot/pos/partials/modals.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 5. POS / SCRIPTS
// ══════════════════════════════════════════════════════════════
echo "\n── 5. pos/partials/scripts ──────────────────────────────────\n";
assertIncludesExist("$viewsRoot/pos/partials/scripts.blade.php", $viewsRoot);

// POS scripts sub-partials
echo "\n── 5b. pos/partials/scripts/pos-system ──────────────────────\n";
assertIncludesExist("$viewsRoot/pos/partials/scripts/pos-system.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 6. COMPONENTS / WIZARD / SCRIPTS
// ══════════════════════════════════════════════════════════════
echo "\n── 6. components/wizard/scripts ─────────────────────────────\n";
assertIncludesExist("$viewsRoot/components/wizard/scripts.blade.php", $viewsRoot);

// Wizard sub-partials (core → header/init/navigation)
echo "\n── 6b. components/wizard/scripts/core ───────────────────────\n";
assertIncludesExist("$viewsRoot/components/wizard/scripts/core.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 7. SIDEBAR
// ══════════════════════════════════════════════════════════════
echo "\n── 7. layouts/partials/sidebar ──────────────────────────────\n";
assertIncludesExist("$viewsRoot/layouts/partials/sidebar.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 8. STAFF PAYSLIP
// ══════════════════════════════════════════════════════════════
echo "\n── 8. staff/partials/payslip ────────────────────────────────\n";
assertIncludesExist("$viewsRoot/staff/payslip.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 9. WORKSHOP FINALIZE BILL
// ══════════════════════════════════════════════════════════════
echo "\n── 9. workshop/finalize_bill ────────────────────────────────\n";
assertIncludesExist("$viewsRoot/workshop/finalize_bill.blade.php", $viewsRoot);

// ══════════════════════════════════════════════════════════════
// 10. DEEP SCAN — ALL @includes across entire views folder
// ══════════════════════════════════════════════════════════════
echo "\n── 10. DEEP SCAN: All @include paths across all views ────────\n";
echo "    (Scanning every blade file for broken @include paths...)\n\n";

$deepErrors = [];
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsRoot));
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getRealPath());
    preg_match_all("/@include\(['\"]([^'\"]+)['\"]/", $content, $matches);
    foreach ($matches[1] as $viewDot) {
        if (strpos($viewDot, '$') !== false) continue; // skip dynamic includes
        $viewPath = $viewsRoot . '/' . str_replace('.', '/', $viewDot) . '.blade.php';
        if (!file_exists($viewPath)) {
            $relFile = str_replace($viewsRoot . '/', '', $file->getRealPath());
            $deepErrors[] = "  ❌  @include('$viewDot')\n       └─ Referenced in: $relFile";
        }
    }
}

if (empty($deepErrors)) {
    ok("All @include paths are valid across all " . iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsRoot))) . " view files!");
} else {
    echo "  Broken @include paths found:\n";
    foreach ($deepErrors as $e) {
        echo $e . "\n";
        $fail++;
        $errors[] = $e;
    }
}

// ══════════════════════════════════════════════════════════════
// 11. FILE SIZE CHECK — No file over 500 lines
// ══════════════════════════════════════════════════════════════
echo "\n── 11. FILE SIZE: No blade file > 500 lines ─────────────────\n";
$oversized = [];
$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsRoot));
foreach ($allFiles as $file) {
    if ($file->getExtension() !== 'php') continue;
    $lines = count(file($file->getRealPath()));
    if ($lines > 500) {
        $relFile = str_replace($viewsRoot . '\\', '', $file->getRealPath());
        $oversized[] = "  ❌  $relFile ($lines lines)";
    }
}

if (empty($oversized)) {
    ok("All blade files are under 500 lines ✓");
} else {
    foreach ($oversized as $o) {
        echo $o . "\n";
        $fail++;
        $errors[] = $o;
    }
}

// ══════════════════════════════════════════════════════════════
// FINAL RESULTS
// ══════════════════════════════════════════════════════════════
$total = $pass + $fail;
echo "\n";
echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║                    TEST RESULTS                          ║\n";
echo "╠══════════════════════════════════════════════════════════╣\n";
printf("║  Total Checks : %-4d                                    ║\n", $total);
printf("║  Passed       : %-4d ✅                                  ║\n", $pass);
printf("║  Failed       : %-4d %s                                  ║\n", $fail, $fail > 0 ? '❌' : '  ');
echo "╚══════════════════════════════════════════════════════════╝\n";

if (!empty($errors)) {
    echo "\n🔴 FAILURES:\n";
    foreach ($errors as $e) {
        echo $e . "\n";
    }
    exit(1);
} else {
    echo "\n🟢 ALL CHECKS PASSED — Refactoring is safe!\n";
    exit(0);
}
