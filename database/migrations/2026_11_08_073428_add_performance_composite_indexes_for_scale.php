<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->indexExists('orders', 'idx_orders_customer_payment_status')) {
                $table->index(['customer_id', 'payment_status'], 'idx_orders_customer_payment_status');
            }
        });

        Schema::table('register_sessions', function (Blueprint $table) {
            if (!$this->indexExists('register_sessions', 'idx_reg_sessions_branch_status')) {
                $table->index(['branch_id', 'status'], 'idx_reg_sessions_branch_status');
            }
        });

        Schema::table('branch_reopen_requests', function (Blueprint $table) {
            if (!$this->indexExists('branch_reopen_requests', 'idx_reopen_branch_date_status')) {
                $table->index(['branch_id', 'request_date', 'status'], 'idx_reopen_branch_date_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'idx_orders_customer_payment_status')) {
                $table->dropIndex('idx_orders_customer_payment_status');
            }
        });

        Schema::table('register_sessions', function (Blueprint $table) {
            if ($this->indexExists('register_sessions', 'idx_reg_sessions_branch_status')) {
                $table->dropIndex('idx_reg_sessions_branch_status');
            }
        });

        Schema::table('branch_reopen_requests', function (Blueprint $table) {
            if ($this->indexExists('branch_reopen_requests', 'idx_reopen_branch_date_status')) {
                $table->dropIndex('idx_reopen_branch_date_status');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
