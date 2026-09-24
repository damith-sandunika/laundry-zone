<?php

$file = 'resources/views/orders/partials/maps.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

function extractSection($lines, $start, $end) {
    return implode("\n", array_slice($lines, $start - 1, $end - $start + 1));
}

$orderInfo = extractSection($lines, 1, 116);
file_put_contents('resources/views/orders/partials/details/order_info.blade.php', $orderInfo);

$laundryItems = extractSection($lines, 117, 353);
file_put_contents('resources/views/orders/partials/details/laundry_items.blade.php', $laundryItems);

$paymentSlips = extractSection($lines, 354, 710);
file_put_contents('resources/views/orders/partials/details/payment_slips.blade.php', $paymentSlips);

$mapIntegration = extractSection($lines, 711, 862);
file_put_contents('resources/views/orders/partials/details/map_integration.blade.php', $mapIntegration);

// Reconstruct maps.blade.php
$newLines = [
    "@include('orders.partials.details.order_info')",
    "@include('orders.partials.details.laundry_items')",
    "@include('orders.partials.details.payment_slips')",
    "@include('orders.partials.details.map_integration')"
];

file_put_contents($file, implode("\n", $newLines));

echo "maps.blade.php extraction done.\n";
