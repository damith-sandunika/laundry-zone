<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin', 'branch_admin', 'workshop_admin'])) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized access. Admins only.');
    }
}
