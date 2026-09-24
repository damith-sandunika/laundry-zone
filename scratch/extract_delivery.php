<?php

$file = 'resources/views/delivery/index.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Function to extract and slice
function extractSection($lines, $start, $end) {
    // start and end are 1-based line numbers
    return implode("\n", array_slice($lines, $start - 1, $end - $start + 1));
}

// Extract sections based on line numbers
$statCards = extractSection($lines, 65, 88);
file_put_contents('resources/views/delivery/partials/stat-cards.blade.php', $statCards);

$pickupsTable = extractSection($lines, 97, 229);
file_put_contents('resources/views/delivery/partials/pickups-table.blade.php', $pickupsTable);

$returnsTable = extractSection($lines, 231, 371);
file_put_contents('resources/views/delivery/partials/returns-table.blade.php', $returnsTable);

$modals = extractSection($lines, 381, 706);
file_put_contents('resources/views/delivery/partials/modals.blade.php', $modals);

$scripts = extractSection($lines, 708, 901);
file_put_contents('resources/views/delivery/partials/scripts.blade.php', $scripts);

// Reconstruct the file
$newLines = [];
$newLines = array_merge($newLines, array_slice($lines, 0, 64)); // 1 to 64
$newLines[] = "        @include('delivery.partials.stat-cards')";
$newLines = array_merge($newLines, array_slice($lines, 88, 96 - 88)); // 89 to 96
$newLines[] = "                @include('delivery.partials.pickups-table')";
$newLines[] = "                @include('delivery.partials.returns-table')";
$newLines = array_merge($newLines, array_slice($lines, 371, 380 - 371)); // 372 to 380
$newLines[] = "        @include('delivery.partials.modals')";
$newLines[] = "        @include('delivery.partials.scripts')";
$newLines = array_merge($newLines, array_slice($lines, 901)); // 902 to end

file_put_contents($file, implode("\n", $newLines));

echo "delivery/index.blade.php extraction done.\n";
