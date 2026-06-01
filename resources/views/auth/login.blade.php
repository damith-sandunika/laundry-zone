<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:20px;">
        @csrf

        @if ($errors->any())
            <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>
                <span style="font-size:13px;font-weight:600;color:#dc2626;">{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Email -->
        <div>
            <label for="email" style="display:block;font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:8px;">Email Address</label>
            <div style="position:relative;">
                <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@laundry.com"
                    style="width:100%;padding:13px 16px 13px 42px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:500;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box;transition:all 0.2s;"
                    onfocus="this.style.borderColor='#3b82f6';this.style.background='#fff';this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.08)'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb';this.style.boxShadow='none'"
                />
            </div>
        </div>

        <!-- Password -->
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <label for="password" style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:0.07em;">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:12px;font-weight:600;color:#3b82f6;text-decoration:none;">Forgot password?</a>
                @endif
            </div>
            <div style="position:relative;">
                <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="width:100%;padding:13px 16px 13px 42px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-weight:500;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box;transition:all 0.2s;"
                    onfocus="this.style.borderColor='#3b82f6';this.style.background='#fff';this.style.boxShadow='0 0 0 4px rgba(59,130,246,0.08)'"
                    onblur="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb';this.style.boxShadow='none'"
                />
            </div>
        </div>

        <!-- Remember Me -->
        <div style="display:flex;align-items:center;gap:10px;">
            <input id="remember_me" type="checkbox" name="remember"
                style="width:17px;height:17px;border-radius:5px;border:1.5px solid #d1d5db;accent-color:#1d4ed8;cursor:pointer;flex-shrink:0;">
            <label for="remember_me" style="font-size:13px;font-weight:500;color:#6b7280;cursor:pointer;user-select:none;">Keep me logged in</label>
        </div>

        <!-- Submit -->
        <button type="submit"
            style="width:100%;padding:14px 24px;background:linear-gradient(135deg, #1d4ed8, #2563eb);color:white;font-weight:700;font-size:15px;border-radius:12px;border:none;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;letter-spacing:-0.01em;box-shadow:0 4px 14px rgba(29,78,216,0.3);margin-top:4px;"
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 25px rgba(29,78,216,0.4)'"
            onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(29,78,216,0.3)'">
            Sign In to Dashboard
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>
    </form>
</x-guest-layout>
