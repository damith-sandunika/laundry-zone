<?php

$file = 'resources/views/workshop/finalize_bill_modal.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

function extractSection($lines, $start, $end) {
    return implode("\n", array_slice($lines, $start - 1, $end - $start + 1));
}

// Extract sections
$leftPanel  = extractSection($lines, 79, 244);    // Left: Services
$rightPanel = extractSection($lines, 247, 537);   // Right: Order Panel
$jsBlock    = extractSection($lines, 542, 753);   // Script block

file_put_contents('resources/views/workshop/partials/finalize-services-panel.blade.php', $leftPanel);
file_put_contents('resources/views/workshop/partials/finalize-order-panel.blade.php', $rightPanel);
file_put_contents('resources/views/workshop/partials/finalize-scripts.blade.php', $jsBlock);

// Rebuild finalize_bill_modal.blade.php
// Keep lines 1-77 (styles + x-data wrapper opening)
$header = implode("\n", array_slice($lines, 0, 77));

// Keep lines 538-541 (closing divs of x-data wrapper)
$closing = implode("\n", array_slice($lines, 537, 4));

$newContent = $header . "\n"
    . "            @include('workshop.partials.finalize-services-panel')\n\n"
    . "            @include('workshop.partials.finalize-order-panel')\n"
    . "        </div>\n"   // close flex container
    . "    </div>\n"      // close pos-wrapper
    . "\n"
    . "    @include('workshop.partials.finalize-scripts')\n"
    . "\n</x-app-layout>\n";

file_put_contents($file, $newContent);

echo "workshop/finalize_bill_modal.blade.php extraction done.\n";
