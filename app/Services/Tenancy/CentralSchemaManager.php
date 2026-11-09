<?php

namespace App\Services\Tenancy;

use App\Models\Central\PlatformSetting;
use App\Models\Central\SuperAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PDO;

class CentralSchemaManager
{
    /**
     * Ensure the Central Landlord database and its tables exist.
     */
    public static function ensureCentralDatabaseAndTables(): void
    {
        $dbName = config('database.connections.landlord.database', env('DB_CENTRAL_DATABASE', 'laundry_zone_saas_central'));
        $host = config('database.connections.landlord.host', '127.0.0.1');
        $port = config('database.connections.landlord.port', '3306');
        $user = config('database.connections.landlord.username', 'root');
        $pass = config('database.connections.landlord.password', '');

        try {
            // 1. Create database if not exists using direct PDO
            $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable $e) {
            // Log or ignore if cannot connect
            report($e);
            return;
        }

        // 2. Run DDL schemas on landlord connection
        try {
            $landlordSchema = Schema::connection('landlord');

            // super_admins table
            if (!$landlordSchema->hasTable('super_admins')) {
                $landlordSchema->create('super_admins', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('email')->unique();
                    $table->string('phone')->nullable();
                    $table->string('password');
                    $table->string('avatar_path')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->rememberToken();
                    $table->timestamps();
                });
            }

            // tenants table
            if (!$landlordSchema->hasTable('tenants')) {
                $landlordSchema->create('tenants', function ($table) {
                    $table->id();
                    $table->string('name');
                    $table->string('subdomain')->unique();
                    $table->string('custom_domain')->nullable()->unique();
                    $table->string('database_name')->unique();
                    $table->string('db_host')->nullable();
                    $table->string('db_port')->nullable();
                    $table->string('db_username')->nullable();
                    $table->string('db_password')->nullable();
                    $table->string('logo_path')->nullable();
                    $table->text('address')->nullable();
                    $table->string('phone')->nullable();
                    $table->string('email')->nullable();
                    $table->boolean('is_delivery_enabled')->default(false);
                    $table->string('plan')->default('Standard');
                    $table->decimal('plan_price', 10, 2)->default(15000.00);
                    $table->decimal('delivery_price', 10, 2)->default(5000.00);
                    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
                    $table->text('notes')->nullable();
                    $table->string('sms_gateway')->default('textware');
                    $table->string('sms_mask')->nullable();
                    $table->string('sms_api_url')->nullable();
                    $table->text('sms_api_key')->nullable();
                    $table->string('mail_from_name')->nullable();
                    $table->string('mail_from_address')->nullable();
                    $table->timestamp('trial_ends_at')->nullable();
                    $table->timestamps();
                });
            }

            if (!$landlordSchema->hasColumn('tenants', 'sms_balance')) {
                $landlordSchema->table('tenants', function ($table) {
                    $table->integer('sms_balance')->default(0)->after('sms_api_key');
                    $table->decimal('sms_rate', 8, 2)->default(2.00)->after('sms_balance');
                });
            }

            // tenant_owners table
            if (!$landlordSchema->hasTable('tenant_owners')) {
                $landlordSchema->create('tenant_owners', function ($table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                    $table->string('name');
                    $table->string('email');
                    $table->string('phone')->nullable();
                    $table->string('initial_password')->nullable();
                    $table->boolean('is_active')->default(true);
                    $table->timestamps();
                });
            }

            // tenant_subscriptions table
            if (!$landlordSchema->hasTable('tenant_subscriptions')) {
                $landlordSchema->create('tenant_subscriptions', function ($table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                    $table->string('invoice_number')->unique();
                    $table->string('plan_name')->default('Standard');
                    $table->decimal('base_amount', 10, 2)->default(15000.00);
                    $table->decimal('delivery_addon_amount', 10, 2)->default(0.00);
                    $table->decimal('total_amount', 10, 2)->default(15000.00);
                    $table->string('currency')->default('LKR');
                    $table->string('billing_cycle')->default('monthly');
                    $table->date('billing_period_start')->nullable();
                    $table->date('billing_period_end')->nullable();
                    $table->enum('status', ['paid', 'pending', 'overdue', 'cancelled'])->default('paid');
                    $table->string('payment_method')->nullable();
                    $table->string('payment_reference')->nullable();
                    $table->timestamp('paid_at')->nullable();
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            }

            // tenant_sms_credits table
            if (!$landlordSchema->hasTable('tenant_sms_credits')) {
                $landlordSchema->create('tenant_sms_credits', function ($table) {
                    $table->id();
                    $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                    $table->integer('sms_count');
                    $table->decimal('rate_per_sms', 8, 2)->default(2.00);
                    $table->decimal('amount_paid', 10, 2);
                    $table->string('payment_reference')->nullable();
                    $table->string('payment_method')->default('Bank Transfer');
                    $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
                    $table->text('notes')->nullable();
                    $table->timestamps();
                });
            }

            // platform_settings table
            if (!$landlordSchema->hasTable('platform_settings')) {
                $landlordSchema->create('platform_settings', function ($table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->longText('value')->nullable();
                    $table->timestamps();
                });
            }

            // Central cache table for tenant resolution and platform caching
            if (!$landlordSchema->hasTable('cache')) {
                $landlordSchema->create('cache', function ($table) {
                    $table->string('key')->primary();
                    $table->mediumText('value');
                    $table->integer('expiration');
                });
            }

            if (!$landlordSchema->hasTable('cache_locks')) {
                $landlordSchema->create('cache_locks', function ($table) {
                    $table->string('key')->primary();
                    $table->string('owner');
                    $table->integer('expiration');
                });
            }

            // Central sessions table
            if (!$landlordSchema->hasTable('sessions')) {
                $landlordSchema->create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }

            // Seed default Super Admin if none exists
            if (SuperAdmin::count() === 0) {
                SuperAdmin::create([
                    'name' => 'SaaS Super Admin',
                    'email' => 'superadmin@laundryzone.com',
                    'phone' => '0771234567',
                    'password' => Hash::make('admin123'),
                    'is_active' => true,
                ]);
            }

            // Seed initial platform settings if not set
            if (!PlatformSetting::get('platform_name')) {
                PlatformSetting::set('platform_name', 'Laundry Zone SaaS Platform');
                PlatformSetting::set('central_domain', env('CENTRAL_DOMAIN', 'laundryzone.dsrzone.com'));
                PlatformSetting::set('default_base_price', '15000.00');
                PlatformSetting::set('default_delivery_price', '5000.00');
                PlatformSetting::set('db_prefix', 'lz_tenant_');
                PlatformSetting::set('contact_email', 'support@laundryzone.dsrzone.com');
                PlatformSetting::set('contact_phone', '+94 11 234 5678');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
