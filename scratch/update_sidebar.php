<?php

$file = 'e:\Projects\Done Projects\360 Laundry\resources\views\layouts\app.blade.php';
$content = file_get_contents($file);

// Replace body x-data
$content = str_replace(
    'x-data="{ sidebarOpen: false, notifOpen: false, profileOpen: false }"',
    'x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem(\'sidebarCollapsed\') === \'true\', notifOpen: false, profileOpen: false }" x-init="$watch(\'sidebarCollapsed\', val => localStorage.setItem(\'sidebarCollapsed\', val))"',
    $content
);

// Replace aside classes
$content = preg_replace(
    '/<aside :class="sidebarOpen \? \'translate-x-0\' : \'-translate-x-full\'" class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 transition-transform duration-300 ease-in-out bg-white\/80 backdrop-blur-xl border-r border-slate-200\/60 lg:static lg:translate-x-0">/',
    '<aside :class="[sidebarOpen ? \'translate-x-0\' : \'-translate-x-full\', sidebarCollapsed ? \'w-20\' : \'w-64\']" class="fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out bg-white/80 backdrop-blur-xl border-r border-slate-200/60 lg:static lg:translate-x-0 group">',
    $content
);

// Update Logo to be clickable
$content = preg_replace(
    '/<div class="w-20 h-20 rounded-\[1.25rem\] bg-white flex items-center justify-center shadow-xl shadow-blue-500\/10 mb-3 ring-4 ring-white transition-transform hover:scale-105 duration-300">/',
    '<button @click="sidebarCollapsed = !sidebarCollapsed" :class="sidebarCollapsed ? \'w-12 h-12 rounded-xl\' : \'w-20 h-20 rounded-[1.25rem]\'" class="bg-white flex items-center justify-center shadow-xl shadow-blue-500/10 mb-3 ring-4 ring-white transition-all hover:scale-105 duration-300 overflow-hidden">',
    $content
);
$content = str_replace(
    'class="max-w-[52px] max-h-[52px] object-contain" onerror="this.style.display=\'none\'">
                </div>',
    'class="object-contain transition-all duration-300" :class="sidebarCollapsed ? \'max-w-[32px] max-h-[32px]\' : \'max-w-[52px] max-h-[52px]\'" onerror="this.style.display=\'none\'">
                </button>',
    $content
);

// Hide text next to logo
$content = str_replace(
    '<div>
                    <div class="text-xl font-black text-slate-900 tracking-tight leading-tight">{{ \App\Models\Setting::get(\'company_name\', \'365laundry.lk\') }}</div>',
    '<div x-show="!sidebarCollapsed" class="transition-opacity duration-300 whitespace-nowrap">
                    <div class="text-xl font-black text-slate-900 tracking-tight leading-tight">{{ \App\Models\Setting::get(\'company_name\', \'365laundry.lk\') }}</div>',
    $content
);

// Update all links in nav
// Currently links are like: <a href="..." class="..."> <svg ...></svg> Text </a>
// We want to capture the text after </svg> and wrap it in <span x-show="!sidebarCollapsed" class="whitespace-nowrap transition-opacity">Text</span>
// Let's use a regex that matches: </svg>\s*([A-Za-z0-9 &_-]+)\s*</a>
// Wait, some links have <div class="flex items-center gap-3"> around the svg and text.
// Let's just wrap any text node that directly follows an SVG or is inside a flex container in the nav.
// A simpler way: Find all text that appears right before </a> or </div> inside the nav and wrap it.
// Actually, I can use regex to find `</svg>\s*(.*?)\s*(?:</a>|</div>)` but that\'s brittle.
// Let's just use CSS!

// Better yet, just inject CSS at the top that handles the `.collapsed` state on `<aside>`!

// Wait, I already changed aside to use dynamic width. Let\'s use Alpine class for `.collapsed-sidebar`
',
    $content
);

file_put_contents($file, $content);

echo "Done\n";
