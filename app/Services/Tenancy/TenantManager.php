<?php

namespace App\Services\Tenancy;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantManager
{
    protected static ?Tenant $currentTenant = null;

    /**
     * Get the active tenant.
     */
    public static function getTenant(): ?Tenant
    {
        return static::$currentTenant ?? (app()->bound('tenant') ? app('tenant') : null);
    }

    /**
     * Alias for getTenant().
     */
    public static function current(): ?Tenant
    {
        return static::getTenant();
    }

    /**
     * Check if a tenant is currently loaded.
     */
    public static function hasTenant(): bool
    {
        return static::$currentTenant !== null;
    }

    /**
     * Set the current active tenant and dynamically configure the database connection.
     */
    public static function setTenant(Tenant $tenant): void
    {
        static::$currentTenant = $tenant;

        // Configure tenant database connection parameters
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

        // Also update default mysql connection for backwards-compatible queries
        Config::set('database.connections.mysql.database', $tenant->database_name);

        DB::purge('tenant');
        DB::purge('mysql');

        // Set default database connection to tenant
        DB::setDefaultConnection('tenant');

        // Dynamically override application configurations for this tenant
        Config::set('app.name', $tenant->name);
        Config::set('app.delivery_enabled', false);

        // Per-Tenant Communication & Mail Configuration
        if ($tenant->mail_from_address) {
            Config::set('mail.from.address', $tenant->mail_from_address);
        }
        Config::set('mail.from.name', $tenant->mail_from_name ?: $tenant->name);

        // Bind tenant to service container
        app()->instance('tenant', $tenant);

        // Structured contextual logging for multi-tenant observability
        \Illuminate\Support\Facades\Log::withContext([
            'tenant_id' => $tenant->id,
            'subdomain' => $tenant->subdomain,
            'db' => $tenant->database_name,
        ]);

        // Auto-ensure critical schema updates only if tenant database has branches table
        if (\Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('branches')) {
            static::ensureTenantSchemaCompatibility();
        }
    }

    /**
     * Ensure critical tenant schema tables and columns exist automatically on tenant switch.
     */
    protected static function ensureTenantSchemaCompatibility(): void
    {
        try {
            $schema = \Illuminate\Support\Facades\Schema::connection('tenant');

            // 1. Table: branch_reopen_requests
            if (!$schema->hasTable('branch_reopen_requests')) {
                $schema->create('branch_reopen_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('branch_id')->constrained()->onDelete('cascade');
                    $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
                    $table->text('reason')->nullable();
                    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                    $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
                    $table->timestamp('reviewed_at')->nullable();
                    $table->text('admin_notes')->nullable();
                    $table->date('request_date');
                    $table->timestamps();

                    $table->index(['branch_id', 'request_date', 'status']);
                });
            }

            // 2. Table: salary_audit_logs
            if (!$schema->hasTable('salary_audit_logs')) {
                $schema->create('salary_audit_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                    $table->string('action');
                    $table->morphs('auditable');
                    $table->text('reason')->nullable();
                    $table->json('old_values')->nullable();
                    $table->json('new_values')->nullable();
                    $table->timestamps();
                });
            }

            // 3. Columns: expenses
            if ($schema->hasTable('expenses')) {
                if (!$schema->hasColumn('expenses', 'updated_by')) {
                    $schema->table('expenses', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('expenses', 'deleted_by')) {
                    $schema->table('expenses', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('expenses', 'deleted_at')) {
                    $schema->table('expenses', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->softDeletes()->after('updated_at');
                    });
                }
            }

            // 4. Columns: salary_advances
            if ($schema->hasTable('salary_advances')) {
                if (!$schema->hasColumn('salary_advances', 'created_by')) {
                    $schema->table('salary_advances', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('created_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'updated_by')) {
                    $schema->table('salary_advances', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'deleted_by')) {
                    $schema->table('salary_advances', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'deleted_at')) {
                    $schema->table('salary_advances', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->softDeletes()->after('updated_at');
                    });
                }
            }

            // 5. Columns: customers (is_corporate, customer_type)
            if ($schema->hasTable('customers')) {
                if (!$schema->hasColumn('customers', 'is_corporate')) {
                    $schema->table('customers', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->boolean('is_corporate')->default(false)->after('phone');
                    });
                }
                if (!$schema->hasColumn('customers', 'customer_type')) {
                    $schema->table('customers', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->enum('customer_type', ['walkin', 'online', 'corporate'])->default('walkin')->after('is_corporate');
                    });
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Tenant schema auto-patch error: " . $e->getMessage());
        }
    }

    /**
     * Invalidate tenant host caches when tenant configuration changes.
     */
    public static function clearTenantCache(Tenant $tenant): void
    {
        $centralDomain = env('CENTRAL_DOMAIN', 'laundryzone.dsrzone.com');
        \Illuminate\Support\Facades\Cache::forget("tenant_resolution:{$tenant->subdomain}.{$centralDomain}");
        \Illuminate\Support\Facades\Cache::forget("tenant_resolution:{$tenant->subdomain}.localhost");
        if ($tenant->custom_domain) {
            \Illuminate\Support\Facades\Cache::forget("tenant_resolution:{$tenant->custom_domain}");
        }
    }

    /**
     * Reset database connection back to Central Landlord.
     */
    public static function resetToLandlord(): void
    {
        static::$currentTenant = null;

        DB::purge('tenant');
        DB::setDefaultConnection('landlord');

        if (app()->bound('tenant')) {
            app()->forgetInstance('tenant');
        }

        \Illuminate\Support\Facades\Log::withoutContext();
    }

    /**
     * Resolve the current central base domain dynamically based on incoming request, platform settings, or configuration.
     */
    public static function getCentralBaseDomain(): string
    {
        if (app()->bound('request') && request()->hasHeader('host')) {
            $host = request()->getHost();

            // 1. If running on localhost or 127.0.0.1 or loopback
            if ($host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.localhost')) {
                return 'localhost';
            }

            // 2. If running on .test (Valet, Laragon, etc.)
            if (str_ends_with($host, '.test')) {
                $parts = explode('.', $host);
                if (count($parts) >= 2) {
                    return implode('.', array_slice($parts, -2));
                }
                return 'laundryzone.test';
            }

            // 3. Derive root domain from current host (strip leading subdomain if present)
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                return implode('.', array_slice($parts, -2));
            }

            return $host;
        }

        // Fallback when executed from pure CLI / queue workers without HTTP request
        return env('CENTRAL_DOMAIN') ?: \App\Models\Central\PlatformSetting::get('central_domain', 'localhost');
    }

    /**
     * Build the full dynamic URL for a given Tenant based on the current request context (scheme, host, port).
     */
    public static function getTenantUrl(Tenant $tenant, string $path = ''): string
    {
        $hasRequest = app()->bound('request') && request()->hasHeader('host');
        $scheme = ($hasRequest && request()->isSecure()) ? 'https://' : 'http://';
        $port = $hasRequest ? request()->getPort() : null;
        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';

        // If custom domain is set
        if (!empty($tenant->custom_domain)) {
            $url = $scheme . $tenant->custom_domain . $portSuffix;
        } else {
            $baseDomain = static::getCentralBaseDomain();
            $url = $scheme . $tenant->subdomain . '.' . $baseDomain . $portSuffix;
        }

        if ($path) {
            $url = rtrim($url, '/') . '/' . ltrim($path, '/');
        }

        return $url;
    }
}
