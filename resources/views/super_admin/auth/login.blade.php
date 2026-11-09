<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SaaS Admin Login - Laundry Zone Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/laundry_main_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Figtree"', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Brand Identity -->
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 items-center justify-center shadow-lg shadow-blue-500/20 text-white mb-3">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">SaaS Admin Portal</h1>
            <p class="text-xs text-slate-500 mt-1">Laundry Zone Platform Central Control Center</p>
        </div>

        <!-- Login Card (Light Theme) -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-7 md:p-8 shadow-xl">
            @if(session('info'))
                <div class="mb-5 p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-semibold">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('super_admin.login.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">SaaS Admin Email</label>
                    <input type="email" name="email" value="{{ old('email', 'superadmin@laundryzone.com') }}" required autofocus
                        placeholder="superadmin@laundryzone.com"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-500">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Remember session</span>
                    </label>
                    <span class="text-slate-400 font-mono text-[11px]">Landlord Guard</span>
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-500/20 transition">
                    Access Platform Admin
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-[11px] text-slate-400 font-medium">
                Protected Multi-Tenant SaaS Engine &bull; {{ config('app.name') }}
            </div>
        </div>
    </div>
</body>
</html>
