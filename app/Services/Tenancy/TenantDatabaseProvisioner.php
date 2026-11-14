<?php

namespace App\Services\Tenancy;

use App\Models\Branch;
use App\Models\Central\Tenant;
use App\Models\Central\TenantPrintLayout;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use PDO;

class TenantDatabaseProvisioner
{
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

    public function runTenantMigrations(Tenant $tenant): array
    {
        @set_time_limit(300);
        TenantManager::setTenant($tenant);

        $exitCode = Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        return [
            'success' => $exitCode === 0,
            'output' => Artisan::output(),
        ];
    }

    public function seedTenantData(Tenant $tenant, array $ownerData): void
    {
        TenantManager::setTenant($tenant);

        $owner = User::firstOrNew(['email' => $ownerData['email']]);
        $owner->name = $ownerData['name'];
        $owner->phone = $ownerData['phone'] ?? null;
        $owner->password = Hash::make($ownerData['password']);
        $owner->role = 'super_admin';
        $owner->is_active = true;
        $owner->branch_id = null;
        $owner->can_manage_global_config = true;
        $owner->save();

        Branch::firstOrCreate(
            ['name' => $tenant->name . ' - Main Branch'],
            [
                'phone' => $tenant->phone ?? '0112345678',
                'address' => $tenant->address ?? 'Main Street',
                'is_active' => true,
                'is_delivery_enabled' => $tenant->is_delivery_enabled,
                'type' => 'branch',
            ]
        );

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
            'is_sms_enabled' => !empty($tenant->sms_api_key) ? 'true' : 'false',
            'is_email_enabled' => !empty($tenant->mail_from_address) ? 'true' : 'false',
            'sms_gateway' => $tenant->sms_gateway ?? 'textware',
            'sms_mask' => $tenant->sms_mask ?? $tenant->name,
            'sms_api_url' => $tenant->sms_api_url ?? '',
            'sms_api_key' => $tenant->sms_api_key ?? '',
            'mail_from_name' => $tenant->mail_from_name ?? $tenant->name,
            'mail_from_address' => $tenant->mail_from_address ?? ($tenant->email ?? ''),
        ];

        foreach ($settings as $k => $v) {
            Setting::set($k, $v);
        }
    }

    public function seedTenantPrintLayouts(Tenant $tenant): void
    {
        $cName = $tenant->name ?: 'Laundry Zone';
        $cPhone = $tenant->phone ?: '+94 11 234 5678';
        $cAddress = $tenant->address ?: 'No 45, High Level Road, Colombo';
        $cEmail = $tenant->email ?: 'contact@' . ($tenant->subdomain ?: 'laundryzone') . '.com';

        $bill = TenantPrintLayout::firstOrNew(['tenant_id' => $tenant->id, 'type' => 'bill']);
        $billDefaults = TenantPrintLayout::defaultAttributes('bill');
        $billDefaults['header_title'] = $cName;
        $billDefaults['custom_fields']['branch_phone'] = $cPhone;
        $billDefaults['custom_fields']['branch_address'] = $cAddress;
        $billDefaults['footer_text'] = "Thank you for choosing {$cName}.";
        $bill->fill($billDefaults);
        $bill->save();

        $receipt = TenantPrintLayout::firstOrNew(['tenant_id' => $tenant->id, 'type' => 'receipt']);
        $receiptDefaults = TenantPrintLayout::defaultAttributes('receipt');
        $receiptDefaults['header_title'] = $cName;
        $receiptDefaults['footer_text'] = "Quality checked by {$cName}.";
        $receipt->fill($receiptDefaults);
        $receipt->save();

        $invoice = TenantPrintLayout::firstOrNew(['tenant_id' => $tenant->id, 'type' => 'invoice']);
        $invDefaults = TenantPrintLayout::defaultAttributes('invoice');
        $invDefaults['header_title'] = $cName;
        $invDefaults['custom_fields']['contact_phone'] = $cPhone;
        $invDefaults['custom_fields']['contact_address'] = $cAddress;
        $invDefaults['custom_fields']['contact_email'] = $cEmail;
        $invDefaults['footer_text'] = "Thank you for choosing {$cName}! We appreciate your business.";
        $invoice->fill($invDefaults);
        $invoice->save();
    }
}

