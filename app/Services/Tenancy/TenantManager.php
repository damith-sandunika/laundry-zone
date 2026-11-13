<?php

namespace App\Services\Tenancy;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantManager
{
    protected static ?Tenant $currentTenant = null;

    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant ?? (app()->bound('tenant') ? app('tenant') : null);
    }

    public static function current(): ?Tenant
    {
        return static::getTenant();
    }

    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    public static function setTenant(Tenant $tenant): void
    {
        static::$currentTenant = $tenant;

        $host = $tenant->db_host ?: config('database.connections.mysql.host', '127.0.0.1');
        $port = $tenant->db_port ?: config('database.connections.mysql.port', '3306');
        $username = $tenant->db_username ?: config('database.connections.mysql.username', 'root');
        $password = $tenant->db_password !== null ? $tenant->db_password : config('database.connections.mysql.password', '');

        Config::set('database.connections.tenant.driver', 'mysql');
        Config::set('database.connections.tenant.host', $host);
        Config::set('database.connections.tenant.port', $port);
        Config::set('database.connections.tenant.database', $tenant->database_name);
        Config::set('database.connections.tenant.username', $username);
        Config::set('database.connections.tenant.password', $password);

        Config::set('database.connections.mysql.database', $tenant->database_name);

        DB::purge('tenant');
        DB::purge('mysql');
        DB::setDefaultConnection('tenant');

        Config::set('app.name', $tenant->name);
        Config::set('app.delivery_enabled', false);

        if ($tenant->mail_from_address) {
            Config::set('mail.from.address', $tenant->mail_from_address);
        }
        Config::set('mail.from.name', $tenant->mail_from_name ?: $tenant->name);

        app()->instance('tenant', $tenant);

        Log::withContext([
            'tenant_id' => $tenant->id,
            'subdomain' => $tenant->subdomain,
            'db' => $tenant->database_name,
        ]);

        if (Schema::connection('tenant')->hasTable('branches')) {
            TenantSchemaPatcher::ensureTenantSchemaCompatibility();
        }
    }

    public static function clearTenantCache(Tenant $tenant): void
    {
        $centralDomain = env('CENTRAL_DOMAIN', 'laundryzone.dsrzone.com');
        Cache::forget("tenant_resolution:{$tenant->subdomain}.{$centralDomain}");
        Cache::forget("tenant_resolution:{$tenant->subdomain}.localhost");
        if ($tenant->custom_domain) {
            Cache::forget("tenant_resolution:{$tenant->custom_domain}");
        }
    }

    public static function resetToLandlord(): void
    {
        static::$currentTenant = null;

        DB::purge('tenant');
        DB::setDefaultConnection('landlord');

        if (app()->bound('tenant')) {
            app()->forgetInstance('tenant');
        }

        Log::withoutContext();
    }

    public static function getCentralBaseDomain(): string
    {
        return TenantUrlService::getCentralBaseDomain();
    }

    public static function getTenantUrl(Tenant $tenant, string $path = ''): string
    {
        return TenantUrlService::getTenantUrl($tenant, $path);
    }
}
