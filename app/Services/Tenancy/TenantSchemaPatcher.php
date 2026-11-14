<?php

namespace App\Services\Tenancy;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class TenantSchemaPatcher
{
    public static function ensureTenantSchemaCompatibility(): void
    {
        try {
            $schema = Schema::connection('tenant');

            // 1. Table: branch_reopen_requests
            if (!$schema->hasTable('branch_reopen_requests')) {
                $schema->create('branch_reopen_requests', function (Blueprint $table) {
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
                $schema->create('salary_audit_logs', function (Blueprint $table) {
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
                    $schema->table('expenses', function (Blueprint $table) {
                        $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('expenses', 'deleted_by')) {
                    $schema->table('expenses', function (Blueprint $table) {
                        $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('expenses', 'deleted_at')) {
                    $schema->table('expenses', function (Blueprint $table) {
                        $table->softDeletes()->after('updated_at');
                    });
                }
            }

            // 4. Columns: salary_advances
            if ($schema->hasTable('salary_advances')) {
                if (!$schema->hasColumn('salary_advances', 'created_by')) {
                    $schema->table('salary_advances', function (Blueprint $table) {
                        $table->foreignId('created_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'updated_by')) {
                    $schema->table('salary_advances', function (Blueprint $table) {
                        $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'deleted_by')) {
                    $schema->table('salary_advances', function (Blueprint $table) {
                        $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
                    });
                }
                if (!$schema->hasColumn('salary_advances', 'deleted_at')) {
                    $schema->table('salary_advances', function (Blueprint $table) {
                        $table->softDeletes()->after('updated_at');
                    });
                }
            }

            // 5. Columns: customers (is_corporate, customer_type)
            if ($schema->hasTable('customers')) {
                if (!$schema->hasColumn('customers', 'is_corporate')) {
                    $schema->table('customers', function (Blueprint $table) {
                        $table->boolean('is_corporate')->default(false)->after('phone');
                    });
                }
                if (!$schema->hasColumn('customers', 'customer_type')) {
                    $schema->table('customers', function (Blueprint $table) {
                        $table->enum('customer_type', ['walkin', 'online', 'corporate'])->default('walkin')->after('is_corporate');
                    });
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Tenant schema auto-patch error: " . $e->getMessage());
        }
    }
}

