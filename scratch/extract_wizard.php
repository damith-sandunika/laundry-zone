<?php

$file = 'resources/views/components/booking-wizard.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// 1. Step 1 (Services): lines 88-333 -> index 87 to 332
$step1 = implode("\n", array_slice($lines, 87, 333 - 88 + 1));
file_put_contents('resources/views/components/wizard/step-services.blade.php', $step1);

// 2. Step 2 (Account): lines 335-430 -> index 334 to 429
$step2 = implode("\n", array_slice($lines, 334, 430 - 335 + 1));
file_put_contents('resources/views/components/wizard/step-account.blade.php', $step2);

// 3. Step 3 (Pickup): lines 432-542 -> index 431 to 541
$step3 = implode("\n", array_slice($lines, 431, 542 - 432 + 1));
file_put_contents('resources/views/components/wizard/step-pickup.blade.php', $step3);

// 4. Step 4 (Delivery): lines 544-738 -> index 543 to 737
$step4 = implode("\n", array_slice($lines, 543, 738 - 544 + 1));
file_put_contents('resources/views/components/wizard/step-delivery.blade.php', $step4);

// 5. Scripts: lines 744 to end-2 -> index 743 to count-3
$scripts = implode("\n", array_slice($lines, 743, count($lines) - 743 - 2));
file_put_contents('resources/views/components/wizard/scripts.blade.php', $scripts);

echo "Wizard extraction done.\n";
