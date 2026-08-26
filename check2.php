<?php
echo "Workshop Dispatches:\n" . json_encode(App\Models\WorkshopDispatch::all(), JSON_PRETTY_PRINT) . "\n";
