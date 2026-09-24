<?php

$file = 'resources/views/layouts/app.blade.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

// Sidebar: lines 171 to 679 (index 170 to 678)
// Topbar: lines 684 to 915 (index 683 to 914)

$sidebar = implode("\n", array_slice($lines, 170, 679 - 171 + 1));
file_put_contents('resources/views/layouts/partials/sidebar.blade.php', $sidebar);

$topbar = implode("\n", array_slice($lines, 683, 915 - 684 + 1));
file_put_contents('resources/views/layouts/partials/topbar.blade.php', $topbar);

// New app.blade.php
// Part 1: before sidebar (0 to 169)
$part1 = array_slice($lines, 0, 170);
// Part 2: after sidebar, before topbar (679 to 682) -> Actually 172 was start. So index 679 to 682
$part2 = array_slice($lines, 679, 683 - 679);
// Part 3: after topbar (915 to end)
$part3 = array_slice($lines, 915);

$newLines = array_merge(
    $part1,
    ["        @include('layouts.partials.sidebar')"],
    $part2,
    ["            @include('layouts.partials.topbar')"],
    $part3
);

file_put_contents($file, implode("\n", $newLines));

echo "app.blade.php extraction done.\n";
