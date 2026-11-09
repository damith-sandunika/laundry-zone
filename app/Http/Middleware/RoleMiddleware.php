<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Super admin has access to everything
        if ($user->role === 'super_admin' || $user->role === 'owner') {
            return $next($request);
        }

        // Dual role handling: accountant_and_hr_manager satisfies both 'accountant' and 'hr_manager'
        if ($user->role === 'accountant_and_hr_manager' && (in_array('accountant', $roles) || in_array('hr_manager', $roles))) {
            return $next($request);
        }

        if (! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

