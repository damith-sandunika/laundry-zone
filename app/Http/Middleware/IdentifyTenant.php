<?php

namespace App\Http\Middleware;

use App\Models\Central\PlatformSetting;
use App\Models\Central\Tenant;
use App\Services\Tenancy\CentralSchemaManager;
use App\Services\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $path = $request->path();
        $centralDomain = TenantManager::getCentralBaseDomain();

        // Always prioritize Super Admin platform routes or central domain
        if ($request->is('super-admin*') || str_starts_with($host, 'admin.') || $host === $centralDomain) {
            TenantManager::resetToLandlord();
            return $next($request);
        }

        // 2. Identify Tenant (with Redis / Memory Caching)
        $cacheKey = "tenant_resolution:{$host}";
        $tenantId = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () use ($host, $centralDomain) {
            // A. Check for custom domain match
            $found = Tenant::where('custom_domain', $host)->first();
            if ($found) {
                return $found->id;
            }

            // B. Check for subdomain match against dynamic central domain, localhost, .test, nip.io, or host segment
            $subdomain = null;
            if (str_ends_with($host, '.' . $centralDomain)) {
                $subdomain = substr($host, 0, -strlen('.' . $centralDomain));
            } elseif (str_ends_with($host, '.localhost')) {
                $subdomain = substr($host, 0, -strlen('.localhost'));
            } elseif (str_ends_with($host, '.test')) {
                $subdomain = substr($host, 0, -strlen('.test'));
                if (str_contains($subdomain, '.')) {
                    $subdomain = explode('.', $subdomain)[0];
                }
            } elseif (str_contains($host, '.nip.io')) {
                $parts = explode('.', $host);
                $subdomain = $parts[0] ?? null;
            } else {
                $parts = explode('.', $host);
                if (count($parts) > 1) {
                    $subdomain = $parts[0];
                }
            }

            if ($subdomain && !in_array($subdomain, ['www', 'admin', 'app', 'mail', 'api', 'central', 'super-admin'])) {
                $found = Tenant::where('subdomain', $subdomain)->first();
                return $found?->id;
            }

            return null;
        });

        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        // C. Development & Testing Fallback via query param or session
        if (!$tenant && ($host === 'localhost' || $host === '127.0.0.1' || env('APP_ENV') === 'local')) {
            $devSubdomain = $request->query('tenant') ?? session('active_tenant_subdomain');
            if ($devSubdomain) {
                $tenant = Tenant::where('subdomain', $devSubdomain)->first();
                if ($tenant) {
                    session(['active_tenant_subdomain' => $devSubdomain]);
                }
            }
        }

        // 3. Handle Tenant Resolution
        if ($tenant) {
            // Check if tenant is suspended
            if ($tenant->status === 'suspended') {
                return response()->view('super_admin.errors.suspended', [
                    'tenant' => $tenant,
                ], 403);
            }

            // Switch to Tenant Database
            TenantManager::setTenant($tenant);

            return $next($request);
        }

        // If local access on root domain without tenant param, redirect or allow central landing/login
        if ($host === 'localhost' || $host === '127.0.0.1') {
            TenantManager::resetToLandlord();
            return $next($request);
        }

        // Unknown tenant
        return response()->view('super_admin.errors.not_found', [
            'host' => $host,
            'centralDomain' => $centralDomain,
        ], 404);
    }

    /**
     * Terminable middleware: Clean up tenant connection after response is sent to avoid MySQL pool starvation.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            if (\Illuminate\Support\Facades\DB::connection()->getName() === 'tenant') {
                \Illuminate\Support\Facades\DB::disconnect('tenant');
            }
        } catch (\Throwable $e) {
            // Ignored on termination
        }
    }
}
