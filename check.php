<?php
$c = file_get_contents('resources/views/rider/dashboard.blade.php');
preg_match_all('/@(if|elseif|else|endif|forelse|empty|endforelse)/', $c, $m);
print_r($m[0]);
