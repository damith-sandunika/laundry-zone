<?php
$filePath = 'resources/views/components/wizard/scripts.blade.php';
$content = file_get_contents($filePath);
$dir = 'resources/views/components/wizard/scripts';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// The file is one large <script> block with bookingWizard() function.
// Split into logical sections:
// 1. init-data.blade.php      - initData(), init()
// 2. auth.blade.php           - submitLogin(), submitRegister()
// 3. maps.blade.php           - initPickupMap(), initDeliveryMap(), drawRoute(), findMe(), calculateDistance(), calculateDeliveryFee(), workshop marker methods
// 4. cart.blade.php           - selectLevel1/2, goBackLevel, addToCart, qty methods, getters
// 5. booking.blade.php        - submitBooking(), submitFinalBooking(), formatTime(), fetchBranchServices(), viewOrder, viewLabels, goToStep()

// Strategy: Since this is all one large JS object, we can't split out the function bodies individually into separate blade files.
// Instead, we extract top-level comment sections as logical partials.
// The actual structure is: one <script>..function bookingWizard() { return { ... } }
// The SAFE strategy is to split the function body into Alpine data blocks with spread/mixin pattern
// BUT that changes JS behavior.
// 
// Better approach: Keep it as one file but split via @include of raw JS partials.
// Each partial just contains the property/method code (no <script> tags), 
// and the main file wraps them all in a single <script>.
//
// SAFEST: Just use distinct section comment markers and pull out into partial files, 
// concatenating in order. But that's complex because they all belong to one JS object.
//
// SIMPLEST SAFE: Split into 3-4 chunks by identifying clean function boundaries.
// We'll use @include partials that each contain a portion of the object body (properties + methods).
// The main file just wraps in <script>function bookingWizard(...) { return {[includes]};}</script>

// Let's identify the sections by line number:
// Line 1: <script>
// Line 2-33: init properties + viewOrder/viewLabels/currentView getters + submitBooking
// Line 50-94: submitFinalBooking, formatTime
// Line 95-169: fetchBranchServices, init categories state
// Line 170-326: Full initData() and some properties
// Line 327-431: goToStep, submitLogin, submitRegister
// Line 432-608: Map methods (addWorkshopMarkersToMap, makeWorkshopIcon, etc.)
// Line 609-688: calculateDeliveryFee()
// Line 689-863: Cart/checkout methods, grand total getters, proceedToDelivery

$lines = explode("\n", $content);
$totalLines = count($lines);

// Let's find the function boundary lines more precisely
$authStart = "                    async submitLogin() {";
$mapsStart = "                    workshopMarkers: {}, // { workshopId: { marker, mapType } }";
$calcStart = "                    calculateDistance(lat1, lon1, lat2, lon2) {";
$cartStart = "                    // Cart methods";
$closingLines = "                }\n            }";

$authStartPos = array_search($authStart, array_map('rtrim', $lines));
$mapsStartPos = array_search($mapsStart, array_map('rtrim', $lines));
$calcStartPos = array_search($calcStart, array_map('rtrim', $lines));
$cartStartPos = array_search($cartStart, array_map('rtrim', $lines));

// Find last 2 lines (closing braces)
$lastLine = $totalLines - 1;

// section 1: line 1 to before auth
$section1 = implode("\n", array_slice($lines, 0, $authStartPos));
// section 2: auth to before maps
$section2 = implode("\n", array_slice($lines, $authStartPos, $mapsStartPos - $authStartPos));
// section 3: maps to before calc
$section3 = implode("\n", array_slice($lines, $mapsStartPos, $calcStartPos - $mapsStartPos));
// section 4: calc to before cart
$section4 = implode("\n", array_slice($lines, $calcStartPos, $cartStartPos - $calcStartPos));
// section 5: cart to end
$section5 = implode("\n", array_slice($lines, $cartStartPos));

// Save partials
file_put_contents("$dir/core.blade.php", $section1 . "\n");
file_put_contents("$dir/auth.blade.php", $section2 . "\n");
file_put_contents("$dir/maps-markers.blade.php", $section3 . "\n");
file_put_contents("$dir/distance-fee.blade.php", $section4 . "\n");
file_put_contents("$dir/cart.blade.php", $section5 . "\n");

// Re-write main file with includes
$newContent = "@include('components.wizard.scripts.core')\n"
            . "@include('components.wizard.scripts.auth')\n"
            . "@include('components.wizard.scripts.maps-markers')\n"
            . "@include('components.wizard.scripts.distance-fee')\n"
            . "@include('components.wizard.scripts.cart')\n";

file_put_contents($filePath, $newContent);

// Now check the line counts for each partial
foreach ([$dir.'/core.blade.php', $dir.'/auth.blade.php', $dir.'/maps-markers.blade.php', $dir.'/distance-fee.blade.php', $dir.'/cart.blade.php'] as $partialPath) {
    $lineCount = count(file($partialPath));
    echo basename($partialPath) . ": $lineCount lines\n";
}
echo "Wizard scripts split complete!\n";
