<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExcludeRiderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'rider') {
            $path = $request->path();
            
            $allowedPaths = [
                'rider',
                'profile',
                'logout',
                'livewire',
                'sanctum',
                '_ignition'
            ];

            $isAllowed = false;
            foreach ($allowedPaths as $allowed) {
                if (str_starts_with($path, $allowed)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                return redirect()->route('rider.dashboard');
            }
        }

        return $next($request);
    }
}
