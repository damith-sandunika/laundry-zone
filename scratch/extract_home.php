<?php

$file = 'resources/views/website/home.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

function extractSection($lines, $start, $end) {
    return implode("\n", array_slice($lines, $start - 1, $end - $start + 1));
}

$hero = extractSection($lines, 8, 338);
file_put_contents('resources/views/website/partials/hero.blade.php', $hero);

$services = extractSection($lines, 340, 364);
file_put_contents('resources/views/website/partials/services.blade.php', $services);

$promotions = extractSection($lines, 366, 500);
file_put_contents('resources/views/website/partials/promotions.blade.php', $promotions);

$howItWorks = extractSection($lines, 501, 554);
file_put_contents('resources/views/website/partials/how-it-works.blade.php', $howItWorks);

$whyChooseUs = extractSection($lines, 556, 596);
file_put_contents('resources/views/website/partials/why-choose-us.blade.php', $whyChooseUs);

$googleReviews = extractSection($lines, 598, 824);
file_put_contents('resources/views/website/partials/google-reviews.blade.php', $googleReviews);

// Reconstruct home.blade.php
$newLines = array_slice($lines, 0, 7); // 1 to 7

$newLines[] = "    @include('website.partials.hero')";
$newLines[] = "    @include('website.partials.services')";
$newLines[] = "    @include('website.partials.promotions')";
$newLines[] = "    @include('website.partials.how-it-works')";
$newLines[] = "    @include('website.partials.why-choose-us')";
$newLines[] = "    @include('website.partials.google-reviews')";

$newLines = array_merge($newLines, array_slice($lines, 824)); // from 825 to end

file_put_contents($file, implode("\n", $newLines));

echo "website/home.blade.php extraction done.\n";
