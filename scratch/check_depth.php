<?php
$c = file_get_contents('resources/views/delivery/index.blade.php');
$d = 0;
foreach(explode(PHP_EOL, $c) as $i => $l) {
    $d += substr_count($l, '<div') - substr_count($l, '</div');
    if (strpos($l, 'deliveryOrders') !== false) echo "START $i $d\n";
    if (strpos($l, 'Preview Modal') !== false) echo "MODAL $i $d\n";
}
echo "END $d\n";
