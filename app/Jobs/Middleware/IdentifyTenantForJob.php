<?php

namespace App\Jobs\Middleware;

use App\Models\Central\Tenant;
use App\Services\Tenancy\TenantManager;
use Closure;
use Illuminate\Support\Facades\Log;

class IdentifyTenantForJob
{
    public ?int $tenantId;

    public function __construct(?int $tenantId = null)
    {
        $this->tenantId = $tenantId ?? (TenantManager::hasTenant() ? TenantManager::getTenant()->id : null);
    }

    /**
     * Process the queued job with isolated tenant database context.
     */
    public function handle(object $job, Closure $next): mixed
    {
        if ($this->tenantId) {
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                TenantManager::setTenant($tenant);
            } else {
                Log::warning("IdentifyTenantForJob: Tenant ID {$this->tenantId} not found in central database.");
            }
        }

        try {
            return $next($job);
        } finally {
            // Always restore landlord connection so background daemon worker does not bleed state
            TenantManager::resetToLandlord();
        }
    }
}
