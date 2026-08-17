<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RiderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if (in_array($user->role, ['rider', 'super_admin'])) {
            return $next($request);
        }

        abort(403, 'Access denied. Riders only.');
    }
}
