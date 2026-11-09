<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;

class MigrateTenantDatabasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:migrate {tenant? : Specific tenant ID or subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations across all or specific tenant database(s)';

    /**
     * Execute the console command.
     */
    public function handle(TenantProvisioner $provisioner): int
    {
        $tenantIdentifier = $this->argument('tenant');

        if ($tenantIdentifier) {
            $tenant = Tenant::where('id', $tenantIdentifier)
                ->orWhere('subdomain', $tenantIdentifier)
                ->first();

            if (!$tenant) {
                $this->error("Tenant '{$tenantIdentifier}' not found.");
                return 1;
            }

            $this->info("Migrating tenant: {$tenant->name} ({$tenant->database_name})...");
            $res = $provisioner->runTenantMigrations($tenant);
            $this->line($res['output']);
            return $res['success'] ? 0 : 1;
        }

        $this->info("Starting batch migration for all active tenants...");
        $results = $provisioner->migrateAllTenants();

        $successCount = 0;
        foreach ($results as $subdomain => $res) {
            $name = $res['tenant'] ?? $subdomain;
            if ($res['success']) {
                $successCount++;
                $this->info("✔ {$name} migrated successfully.");
            } else {
                $msg = $res['message'] ?? ($res['output'] ?? 'Failed');
                $this->error("✖ {$name} migration failed: " . $msg);
            }
        }

        $this->info("Batch migration completed: {$successCount} of " . count($results) . " tenant databases up to date.");
        return 0;
    }
}

