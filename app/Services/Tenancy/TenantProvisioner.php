<?php

namespace App\Services\Tenancy;

use App\Models\Central\PlatformSetting;
use App\Models\Central\Tenant;
use App\Models\Central\TenantOwner;
use App\Models\Central\TenantSubscription;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PDO;

class TenantProvisioner
{
    protected TenantDatabaseProvisioner $dbProvisioner;

    public function __construct(?TenantDatabaseProvisioner $dbProvisioner = null)
    {
        $this->dbProvisioner = $dbProvisioner ?? new TenantDatabaseProvisioner();
    }

    public function provision(array $data, ?UploadedFile $logo = null): Tenant
    {
        $subdomain = Str::slug($data['subdomain']);
        $prefix = PlatformSetting::get('db_prefix', env('TENANT_DB_PREFIX', 'lz_tenant_'));
        $dbName = $data['database_name'] ?? ($prefix . str_replace('-', '_', $subdomain));
        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

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
            'sms_gateway' => $data['sms_gateway'] ?? 'textware',
            'sms_mask' => $data['sms_mask'] ?? $data['name'],
            'sms_api_url' => $data['sms_api_url'] ?? null,
            'sms_api_key' => $data['sms_api_key'] ?? null,
            'mail_from_name' => $data['mail_from_name'] ?? $data['name'],
            'mail_from_address' => $data['mail_from_address'] ?? ($data['email'] ?? null),
            'trial_ends_at' => now()->addDays(14),
        ]);

        $ownerPassword = $data['owner_password'] ?? 'Owner@' . rand(1000, 9999);
        TenantOwner::create([
            'tenant_id' => $tenant->id,
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'phone' => $data['owner_phone'] ?? $data['phone'] ?? null,
            'initial_password' => $ownerPassword,
            'is_active' => true,
        ]);

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

        $this->dbProvisioner->createDatabase($dbName, $tenant->db_host, $tenant->db_port, $tenant->db_username, $tenant->db_password);
        $this->dbProvisioner->runTenantMigrations($tenant);
        $this->dbProvisioner->seedTenantData($tenant, [
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'phone' => $data['owner_phone'] ?? $data['phone'] ?? null,
            'password' => $ownerPassword,
        ]);
        $this->dbProvisioner->seedTenantPrintLayouts($tenant);

        TenantManager::resetToLandlord();

        return $tenant;
    }

    public function createDatabase(string $dbName, ?string $host = null, ?string $port = null, ?string $user = null, ?string $pass = null): void
    {
        $this->dbProvisioner->createDatabase($dbName, $host, $port, $user, $pass);
    }

    public function runTenantMigrations(Tenant $tenant): array
    {
        return $this->dbProvisioner->runTenantMigrations($tenant);
    }

    public function seedTenantData(Tenant $tenant, array $ownerData): void
    {
        $this->dbProvisioner->seedTenantData($tenant, $ownerData);
    }

    public function seedTenantPrintLayouts(Tenant $tenant): void
    {
        $this->dbProvisioner->seedTenantPrintLayouts($tenant);
    }

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
