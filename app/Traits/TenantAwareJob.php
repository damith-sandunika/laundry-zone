<?php

namespace App\Traits;

use App\Jobs\Middleware\IdentifyTenantForJob;
use App\Services\Tenancy\TenantManager;

trait TenantAwareJob
{
    /**
     * The ID of the tenant that owns this queued job.
     */
    public ?int $tenantId = null;

    /**
     * Automatically capture the tenant context during job instantiation.
     */
    public function captureTenantContext(): self
    {
        if (TenantManager::hasTenant()) {
            $this->tenantId = TenantManager::getTenant()->id;
        }

        return $this;
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            new IdentifyTenantForJob($this->tenantId ?? (TenantManager::hasTenant() ? TenantManager::getTenant()->id : null)),
        ];
    }
}
