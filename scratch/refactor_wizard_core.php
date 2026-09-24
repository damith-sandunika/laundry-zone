<?php
$filePath = 'resources/views/components/wizard/scripts/core.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/components/wizard/scripts';

// core.blade.php is 388 lines. Split into:
// 1. header.blade.php: the <script>, function signature, initial props (lines 1-93)
// 2. init.blade.php: fetchBranchServices, init(), initData() (lines 94-327)
// 3. navigation.blade.php: goToStep() (lines 328-389)

$lines = explode("\n", $content);

$initStart = "                    async fetchBranchServices(branchId) {";
$navStart = "                    async goToStep(n) {";

$initStartPos = false;
$navStartPos = false;

foreach ($lines as $i => $line) {
    if (rtrim($line) === rtrim($initStart) && $initStartPos === false) {
        $initStartPos = $i;
    }
    if (rtrim($line) === rtrim($navStart) && $navStartPos === false) {
        $navStartPos = $i;
    }
}

$headerBlock = implode("\n", array_slice($lines, 0, $initStartPos));
$initBlock = implode("\n", array_slice($lines, $initStartPos, $navStartPos - $initStartPos));
$navBlock = implode("\n", array_slice($lines, $navStartPos));

file_put_contents("$dir/header.blade.php", rtrim($headerBlock) . "\n");
file_put_contents("$dir/init.blade.php", rtrim($initBlock) . "\n");
file_put_contents("$dir/navigation.blade.php", rtrim($navBlock) . "\n");

// Rewrite core.blade.php
$newCore = "@include('components.wizard.scripts.header')\n"
         . "@include('components.wizard.scripts.init')\n"
         . "@include('components.wizard.scripts.navigation')\n";
file_put_contents($filePath, $newCore);

echo "header.blade.php: " . count(file("$dir/header.blade.php")) . " lines\n";
echo "init.blade.php: " . count(file("$dir/init.blade.php")) . " lines\n";
echo "navigation.blade.php: " . count(file("$dir/navigation.blade.php")) . " lines\n";
echo "Core split done!\n";
