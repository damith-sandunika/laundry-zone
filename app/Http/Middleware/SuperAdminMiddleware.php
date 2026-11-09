<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request for SaaS Platform Super Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('super_admin')->check()) {
            return redirect()->route('super_admin.login')->with('warning', 'Please login to access the SaaS Admin platform.');
        }

        $superAdmin = Auth::guard('super_admin')->user();
        if (!$superAdmin->is_active) {
            Auth::guard('super_admin')->logout();
            return redirect()->route('super_admin.login')->withErrors(['email' => 'Your SaaS admin account is disabled.']);
        }

        return $next($request);
    }
}
