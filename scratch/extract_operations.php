<?php

$file = 'resources/views/workshop/operations.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

function extractSection($lines, $start, $end) {
    return implode("\n", array_slice($lines, $start - 1, $end - $start + 1));
}

// Extract inner sections (inside x-data wrapper)
$summary    = extractSection($lines, 351, 375);
$tabsFilter = extractSection($lines, 377, 426);
$table      = extractSection($lines, 428, 555);
$modals     = extractSection($lines, 559, 748);

file_put_contents('resources/views/workshop/partials/summary.blade.php', $summary);
file_put_contents('resources/views/workshop/partials/tabs-filter.blade.php', $tabsFilter);
file_put_contents('resources/views/workshop/partials/orders-table.blade.php', $table);
file_put_contents('resources/views/workshop/partials/modals.blade.php', $modals);

// Rebuild operations.blade.php:
// Keep lines 1-349 (header + x-data open)
// Replace inner content with @includes
// Keep lines 749-754 (closing divs + x-app-layout)

$header   = implode("\n", array_slice($lines, 0, 349));  // lines 1-349
$closing  = implode("\n", array_slice($lines, 748));      // lines 749-end

$newContent = $header . "\n\n"
    . "        @include('workshop.partials.summary')\n"
    . "        @include('workshop.partials.tabs-filter')\n"
    . "        @include('workshop.partials.orders-table')\n"
    . "        @include('workshop.partials.modals')\n"
    . "\n" . $closing;

file_put_contents($file, $newContent);

echo "workshop/operations.blade.php extraction done.\n";
