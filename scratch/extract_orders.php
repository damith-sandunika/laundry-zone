<?php

$file = 'resources/views/orders/index.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// 1. Scripts (lines 38-294) -> index is 37 to 293
$scripts = implode("\n", array_slice($lines, 37, 294 - 38 + 1));
file_put_contents('resources/views/orders/partials/scripts.blade.php', $scripts);

// 2. Stats (lines 296-334) -> index 295 to 333
$stats = implode("\n", array_slice($lines, 295, 334 - 296 + 1));
file_put_contents('resources/views/orders/partials/stats-cards.blade.php', $stats);

// 3. Filters (lines 340-382) -> index 339 to 381
$filters = implode("\n", array_slice($lines, 339, 382 - 340 + 1));
file_put_contents('resources/views/orders/partials/filters.blade.php', $filters);

// 4. Table (lines 384-642) -> index 383 to 641
$table = implode("\n", array_slice($lines, 383, 642 - 384 + 1));
file_put_contents('resources/views/orders/partials/table.blade.php', $table);

// 5. Modals (lines 644 to end-4)
$modals = implode("\n", array_slice($lines, 643, count($lines) - 643 - 4));
file_put_contents('resources/views/orders/partials/modals.blade.php', $modals);

echo "Extraction done.";
