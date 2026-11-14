<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\Setting::get('company_name', 'Laundry Zone') }} - Premium Laundry & Dry Cleaning Service</title>
    <link rel="icon" type="image/png" href="{{ asset('images/laundry_main_logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.site_styles')
    @include('layouts.partials.gtm')
</head>

<body class="antialiased text-gray-800 selection:bg-blue-200 selection:text-blue-900 overflow-x-hidden">
    @include('layouts.partials.gtm-body')
    @include('layouts.partials.site_loader_nav')

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.site_footer_scripts')
</body>
</html>
