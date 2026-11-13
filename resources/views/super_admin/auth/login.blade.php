@php
    $logoUrl = file_exists(public_path('images/laundry_main_logo.jpeg'))
        ? asset('images/laundry_main_logo.jpeg')
        : asset('images/laundry_main_logo.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SaaS Super Admin Portal - Laundry Zone Platform</title>
    <link rel="icon" type="image/jpeg" href="{{ $logoUrl }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col justify-between selection:bg-blue-600 selection:text-white relative overflow-x-hidden p-4 sm:p-6">

    <!-- Subtle Background Pattern -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-blue-100/70 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-100/60 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:24px_24px] opacity-60"></div>
    </div>

    <!-- Main Content Container -->
    <main class="flex-1 flex items-center justify-center">
        <div class="w-full max-w-[420px]">

            <!-- Brand Identity (Centered Logo & Title) -->
            <div class="text-center mb-6">
                <div class="inline-flex w-20 h-20 rounded-2xl bg-white border border-slate-200/90 items-center justify-center shadow-md p-2.5 mb-3.5 hover:scale-105 transition-transform duration-300">
                    <img src="{{ $logoUrl }}" alt="Laundry Zone Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">Laundry Zone</h1>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">SaaS Central Control Portal</p>
            </div>

            <!-- Login Card (Clean Light Theme) -->
            <div class="bg-white border border-slate-200/90 rounded-3xl p-7 sm:p-9 shadow-xl shadow-slate-200/50">
                @if(session('info'))
                    <div class="mb-4 p-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('super_admin.login.submit') }}" method="POST" class="space-y-4" autocomplete="off" x-data="{ showPass: false }">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">SaaS Admin Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                autocomplete="off"
                                placeholder="name@laundryzone.com"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                id="adminPasswordInput"
                                class="w-full pl-11 pr-11 py-3 bg-slate-50 hover:bg-slate-50/80 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none placeholder:text-slate-400">
                            <button type="button" onclick="togglePassVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1">
                                <i id="passIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-semibold select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <span>Keep me signed in</span>
                        </label>
                        <span class="text-slate-400 font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">Central Auth</span>
                    </div>

                    <button type="submit"
                        class="w-full mt-2 py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-extrabold text-sm shadow-lg shadow-blue-600/25 transition-all flex items-center justify-center gap-2">
                        <span>Access Super Admin</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6 mb-2">
                <p class="text-xs font-semibold text-slate-400 flex items-center justify-center gap-1.5">
                    <span>Powered by</span>
                    <a href="https://dsrzone.com" target="_blank" rel="noopener" class="font-extrabold text-blue-600 hover:text-blue-700 transition-colors">
                        DSRZone
                    </a>
                </p>
            </div>

        </div>
    </main>

    <script>
        function togglePassVisibility() {
            const input = document.getElementById('adminPasswordInput');
            const icon = document.getElementById('passIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
