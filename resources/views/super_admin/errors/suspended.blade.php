<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account Suspended - {{ $tenant->name ?? 'Laundry Portal' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 mx-auto flex items-center justify-center shadow-2xl shadow-rose-500/10">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">{{ $tenant->name }}</h1>
            <div class="mt-2 text-rose-400 text-xs font-bold uppercase tracking-wider">Account Temporarily Suspended</div>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">
                Access to this company portal has been temporarily paused. Please contact the platform administration or settle any outstanding subscription dues to reactivate your portal.
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 text-xs text-slate-400 space-y-1">
            <div>Support Contact: <strong class="text-white">{{ \App\Models\Central\PlatformSetting::get('contact_email', 'support@laundryzone.dsrzone.com') }}</strong></div>
            <div>Hotline: <strong class="text-white">{{ \App\Models\Central\PlatformSetting::get('contact_phone', '+94 11 234 5678') }}</strong></div>
        </div>
    </div>
</body>
</html>
