<?php

namespace App\Actions\SuperAdmin;

use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\Central\TenantPrintLayout;
use App\Models\Central\TenantSmsCredit;
use App\Models\Central\TenantSubscription;
use App\Services\Tenancy\TenantManager;
use PDO;

class DestroyCompanyAction
{
    public function execute(Tenant $company): array
    {
        TenantManager::resetToLandlord();

        $name = $company->name;
        $subdomain = $company->subdomain;

        // Optionally drop tenant physical DB if exists
        try {
            $sanitizedDb = preg_replace('/[^a-zA-Z0-9_]/', '', $company->database_name);
            $landlordDb = config('database.connections.landlord.database', 'laundry_zone_saas_central');
            if (!empty($sanitizedDb) && $sanitizedDb !== $landlordDb) {
                $host = $company->db_host ?: config('database.connections.mysql.host', '127.0.0.1');
                $port = $company->db_port ?: config('database.connections.mysql.port', '3306');
                $user = $company->db_username ?: config('database.connections.mysql.username', 'root');
                $pass = $company->db_password !== null ? $company->db_password : config('database.connections.mysql.password', '');
                $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("DROP DATABASE IF EXISTS `{$sanitizedDb}`;");
            }
        } catch (\Throwable $e) {
            // Ignore if DB already dropped or inaccessible
        }

        // Clean up Landlord relations
        TenantOwner::where('tenant_id', $company->id)->delete();
        TenantSubscription::where('tenant_id', $company->id)->delete();
        TenantSmsCredit::where('tenant_id', $company->id)->delete();
        if (class_exists(TenantPrintLayout::class)) {
            TenantPrintLayout::where('tenant_id', $company->id)->delete();
        }

        TenantManager::clearTenantCache($company);
        $company->delete();

        return [
            'name' => $name,
            'subdomain' => $subdomain,
        ];
    }
}
