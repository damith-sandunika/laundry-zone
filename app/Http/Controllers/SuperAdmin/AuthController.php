<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show Super Admin Login Form.
     */
    public function showLogin()
    {
        TenantManager::resetToLandlord();

        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('super_admin.dashboard');
        }

        return view('super_admin.auth.login');
    }

    /**
     * Process Super Admin Login.
     */
    public function login(Request $request)
    {
        TenantManager::resetToLandlord();

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::guard('super_admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('super_admin.dashboard'))
                ->with('success', 'Welcome back, Platform Super Administrator.');
        }

        return back()->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'Invalid email or password for SaaS Super Admin.']);
    }

    /**
     * Process Super Admin Logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('super_admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super_admin.login')
            ->with('info', 'You have been successfully logged out from the Super Admin platform.');
    }
}
