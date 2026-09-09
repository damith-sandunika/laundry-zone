<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     * Expects header:  X-API-Key: <value from APP_API_KEY in .env>
     * Or query param:  ?api_key=<value>
     */
    public function handle(Request $request, Closure $next): Response
    {
        $validKey = config('app.api_key');

        // No key configured → block all access
        if (empty($validKey)) {
            return response()->json([
                'error' => 'API access not configured on server.',
            ], 503);
        }

        $providedKey = $request->header('X-API-Key')
            ?? $request->query('api_key');

        if (empty($providedKey) || !hash_equals($validKey, $providedKey)) {
            return response()->json([
                'error'   => 'Unauthorized. Invalid or missing API key.',
                'hint'    => 'Pass X-API-Key header or ?api_key= query param.',
            ], 401);
        }

        return $next($request);
    }
}
