<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Services\Tenancy\TenantManager;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Http\Request;
use PDO;

class DatabaseController extends Controller
{
    protected TenantProvisioner $provisioner;

    public function __construct(TenantProvisioner $provisioner)
    {
        $this->provisioner = $provisioner;
    }

    /**
     * Show all tenant databases and status.
     */
    public function index()
    {
        TenantManager::resetToLandlord();

        $tenants = Tenant::latest()->get();
        $dbData = [];

        foreach ($tenants as $tenant) {
            $stats = $this->provisioner->testConnection($tenant);
            $dbData[] = [
                'tenant' => $tenant,
                'status' => $stats['status'],
                'tables' => $stats['tables'],
                'size_mb' => $stats['size_mb'],
                'message' => $stats['message'],
            ];
        }

        return view('super_admin.databases.index', compact('dbData'));
    }

    /**
     * Run migrations on single tenant database.
     */
    public function migrate(Tenant $company)
    {
        TenantManager::resetToLandlord();

        try {
            $res = $this->provisioner->runTenantMigrations($company);
            TenantManager::resetToLandlord();

            if ($res['success']) {
                return back()->with('success', "Migrations executed successfully on database '{$company->database_name}'.");
            }
            return back()->with('warning', "Migration output: " . $res['output']);
        } catch (\Throwable $e) {
            TenantManager::resetToLandlord();
            return back()->withErrors(['error' => 'Migration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Run migrations on all active tenant databases.
     */
    public function migrateAll()
    {
        TenantManager::resetToLandlord();

        $results = $this->provisioner->migrateAllTenants();

        $successCount = collect($results)->where('success', true)->count();
        $totalCount = count($results);

        return back()->with('success', "Batch migration completed: {$successCount} of {$totalCount} tenant databases migrated successfully.");
    }

    /**
     * View tables of a specific tenant database.
     */
    public function tables(Tenant $company)
    {
        TenantManager::resetToLandlord();

        try {
            $host = $company->db_host ?: config('database.connections.mysql.host', '127.0.0.1');
            $port = $company->db_port ?: config('database.connections.mysql.port', '3306');
            $user = $company->db_username ?: config('database.connections.mysql.username', 'root');
            $pass = $company->db_password !== null ? $company->db_password : config('database.connections.mysql.password', '');

            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$company->database_name}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $stmt = $pdo->query("SELECT table_name, table_rows, ROUND((data_length + index_length) / 1024, 2) AS size_kb, update_time FROM information_schema.tables WHERE table_schema = '{$company->database_name}' ORDER BY table_name ASC");
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return view('super_admin.databases.tables', compact('company', 'tables'));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Could not inspect tables: ' . $e->getMessage()]);
        }
    }

    /**
     * Physically drop a tenant database and recreate an empty schema.
     */
    public function dropDatabase(Tenant $company)
    {
        TenantManager::resetToLandlord();

        try {
            $host = $company->db_host ?: config('database.connections.mysql.host', '127.0.0.1');
            $port = $company->db_port ?: config('database.connections.mysql.port', '3306');
            $user = $company->db_username ?: config('database.connections.mysql.username', 'root');
            $pass = $company->db_password !== null ? $company->db_password : config('database.connections.mysql.password', '');

            $sanitizedDb = preg_replace('/[^a-zA-Z0-9_]/', '', $company->database_name);

            // Safety guard: Never drop landlord database
            $landlordDb = config('database.connections.landlord.database', 'laundry_zone_saas_central');
            if ($sanitizedDb === $landlordDb || empty($sanitizedDb)) {
                return back()->withErrors(['error' => 'Safety Violation: Cannot drop central landlord database!']);
            }

            $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Physically drop the database
            $pdo->exec("DROP DATABASE IF EXISTS `{$sanitizedDb}`;");

            return back()->with('success', "Database '{$sanitizedDb}' for company '{$company->name}' was physically dropped successfully.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Could not drop database: ' . $e->getMessage()]);
        }
    }
}
