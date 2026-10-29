<?php

namespace App\Services\Tenancy;

use App\Models\Branch;
use App\Models\Central\PlatformSetting;
use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\Central\TenantSubscription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PDO;

class TenantProvisioner
{
    /**
     * Complete Tenant Onboarding & Database Auto-Provisioning.
     */
    public function provision(array $data, ?UploadedFile $logo = null): Tenant
    {
        // 1. Sanitize subdomain and database name
        $subdomain = Str::slug($data['subdomain']);
        $prefix = PlatformSetting::get('db_prefix', env('TENANT_DB_PREFIX', 'lz_tenant_'));
        $dbName = $data['database_name'] ?? ($prefix . str_replace('-', '_', $subdomain));
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        // 2. Handle logo upload
        $logoPath = null;
        if ($logo && $logo->isValid()) {
            $filename = 'tenant_' . $subdomain . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/tenants'), $filename);
            $logoPath = 'uploads/tenants/' . $filename;
        }

        $isDeliveryEnabled = !empty($data['is_delivery_enabled']);
        $plan = $data['plan'] ?? 'Standard';
        $planPrice = (float) ($data['plan_price'] ?? PlatformSetting::get('default_base_price', '15000.00'));
        $deliveryPrice = (float) ($data['delivery_price'] ?? PlatformSetting::get('default_delivery_price', '5000.00'));

        // 3. Create Tenant in Central Landlord DB
        $tenant = Tenant::create([
            'name' => $data['name'],
            'subdomain' => $subdomain,
            'custom_domain' => !empty($data['custom_domain']) ? trim(strtolower($data['custom_domain'])) : null,
            'database_name' => $dbName,
            'db_host' => $data['db_host'] ?? null,
            'db_port' => $data['db_port'] ?? null,
            'db_username' => $data['db_username'] ?? null,
            'db_password' => $data['db_password'] ?? null,
            'logo_path' => $logoPath,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'is_delivery_enabled' => $isDeliveryEnabled,
            'plan' => $plan,
            'plan_price' => $planPrice,
            'delivery_price' => $deliveryPrice,
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
            'trial_ends_at' => now()->addDays(14),
        ]);

        // 4. Create Owner Record in Central DB
        $ownerPassword = $data['owner_password'] ?? 'Owner@' . rand(1000, 9999);
        $owner = TenantOwner::create([
            'tenant_id' => $tenant->id,
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'phone' => $data['owner_phone'] ?? $data['phone'] ?? null,
            'initial_password' => $ownerPassword,
            'is_active' => true,
        ]);

        // 5. Generate Initial Subscription Invoice
        $deliveryFee = $isDeliveryEnabled ? $deliveryPrice : 0;
        $totalAmount = $planPrice + $deliveryFee;

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'invoice_number' => 'INV-' . strtoupper(Str::random(4)) . '-' . date('Ymd'),
            'plan_name' => $plan,
            'base_amount' => $planPrice,
            'delivery_addon_amount' => $deliveryFee,
            'total_amount' => $totalAmount,
            'currency' => 'LKR',
            'billing_cycle' => 'monthly',
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'status' => 'paid',
            'payment_method' => 'Bank Transfer / Setup',
            'paid_at' => now(),
            'notes' => 'Initial subscription invoice with ' . ($isDeliveryEnabled ? 'Delivery Module Included' : 'Standard Module'),
        ]);

        // 6. Physically Create MySQL Database
        $this->createDatabase($dbName, $tenant->db_host, $tenant->db_port, $tenant->db_username, $tenant->db_password);

        // 7. Run Tenant Migrations
        $this->runTenantMigrations($tenant);

        // 8. Seed Tenant Owner and Default Data
        $this->seedTenantData($tenant, [
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'phone' => $data['owner_phone'] ?? $data['phone'] ?? null,
            'password' => $ownerPassword,
        ]);

        // Reset connection back to landlord
        TenantManager::resetToLandlord();

        return $tenant;
    }

    /**
     * Execute DDL CREATE DATABASE IF NOT EXISTS.
     */
    public function createDatabase(string $dbName, ?string $host = null, ?string $port = null, ?string $user = null, ?string $pass = null): void
    {
        $sanitizedDb = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        $host = $host ?: config('database.connections.mysql.host', '127.0.0.1');
        $port = $port ?: config('database.connections.mysql.port', '3306');
        $user = $user ?: config('database.connections.mysql.username', 'root');
        $pass = $pass !== null ? $pass : config('database.connections.mysql.password', '');

        $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$sanitizedDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }

    /**
     * Run all tenant migrations against the tenant database.
     */
    public function runTenantMigrations(Tenant $tenant): array
    {
        TenantManager::setTenant($tenant);

        $exitCode = Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        $output = Artisan::output();

        return [
            'success' => $exitCode === 0,
            'output' => $output,
        ];
    }

    /**
     * Seed initial tenant data: Owner User (role: owner), Branch, Settings, and Communication templates.
     */
    public function seedTenantData(Tenant $tenant, array $ownerData): void
    {
        TenantManager::setTenant($tenant);

        // 1. Create or update Company Owner user (role = 'owner')
        $owner = User::firstOrNew(['email' => $ownerData['email']]);
        $owner->name = $ownerData['name'];
        $owner->phone = $ownerData['phone'] ?? null;
        $owner->password = Hash::make($ownerData['password']);
        $owner->role = 'owner'; // Former super_admin is now Owner!
        $owner->is_active = true;
        $owner->branch_id = null; // Owner has global access across all branches
        $owner->can_manage_global_config = true;
        $owner->save();

        // 2. Create Default Branch
        $branch = Branch::firstOrCreate(
            ['name' => $tenant->name . ' - Main Branch'],
            [
                'phone' => $tenant->phone ?? '0112345678',
                'address' => $tenant->address ?? 'Main Street',
                'is_active' => true,
                'is_delivery_enabled' => $tenant->is_delivery_enabled,
                'type' => 'branch',
            ]
        );

        // 3. Populate default settings in tenant database
        $settings = [
            'company_name' => $tenant->name,
            'company_phone' => $tenant->phone ?? '',
            'company_address' => $tenant->address ?? '',
            'company_email' => $tenant->email ?? '',
            'company_logo' => $tenant->logo_path ?? '',
            'delivery_enabled' => $tenant->is_delivery_enabled ? '1' : '0',
            'currency' => 'LKR',
            'currency_symbol' => 'Rs.',
            'tax_percentage' => '0',
            'allow_branch_admins_delete_orders' => '0',
            'centralized_management' => '1',
        ];

        foreach ($settings as $k => $v) {
            Setting::set($k, $v);
        }
    }

    /**
     * Test tenant database connection and return stats.
     */
    public function testConnection(Tenant $tenant): array
    {
        try {
            $host = $tenant->db_host ?: config('database.connections.mysql.host', '127.0.0.1');
            $port = $tenant->db_port ?: config('database.connections.mysql.port', '3306');
            $user = $tenant->db_username ?: config('database.connections.mysql.username', 'root');
            $pass = $tenant->db_password !== null ? $tenant->db_password : config('database.connections.mysql.password', '');

            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$tenant->database_name}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $stmt = $pdo->query("SELECT count(*) AS table_count FROM information_schema.tables WHERE table_schema = '{$tenant->database_name}'");
            $tableCount = (int) $stmt->fetchColumn();

            $sizeStmt = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = '{$tenant->database_name}'");
            $sizeMb = (float) $sizeStmt->fetchColumn();

            return [
                'status' => 'online',
                'tables' => $tableCount,
                'size_mb' => $sizeMb,
                'message' => 'Connection established successfully.',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'offline',
                'tables' => 0,
                'size_mb' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Run migrations on all registered active tenants.
     */
    public function migrateAllTenants(): array
    {
        $tenants = Tenant::where('status', 'active')->get();
        $results = [];

        foreach ($tenants as $tenant) {
            try {
                $res = $this->runTenantMigrations($tenant);
                $results[$tenant->subdomain] = [
                    'tenant' => $tenant->name,
                    'success' => $res['success'],
                    'message' => 'Migrated successfully.',
                ];
            } catch (\Throwable $e) {
                $results[$tenant->subdomain] = [
                    'tenant' => $tenant->name,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        TenantManager::resetToLandlord();

        return $results;
    }
}
