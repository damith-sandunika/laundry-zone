<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenant Company Not Found - Laundry Zone SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center shadow-2xl shadow-amber-500/10">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Company Portal Not Found</h1>
            <div class="mt-2 text-amber-400 text-xs font-mono font-bold">{{ $host }}</div>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">
                We could not locate any active laundry tenant associated with this domain or subdomain. Please check your spelling or register a new company on the platform.
            </p>
        </div>

        <div class="pt-2">
            <a href="http://{{ $centralDomain }}/super-admin/login"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg transition">
                <span>Go to Platform Admin</span>
            </a>
        </div>
    </div>
</body>
</html>
