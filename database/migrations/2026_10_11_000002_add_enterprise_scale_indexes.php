<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!$this->indexExists('orders', 'idx_orders_deleted_at')) {
                $table->index('deleted_at', 'idx_orders_deleted_at');
            }
            if (!$this->indexExists('orders', 'idx_orders_branch_del_created')) {
                $table->index(['branch_id', 'deleted_at', 'created_at'], 'idx_orders_branch_del_created');
            }
        });

        Schema::table('workshop_dispatches', function (Blueprint $table) {
            if (!$this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_order')) {
                $table->index(['workshop_branch_id', 'order_id'], 'idx_ws_disp_branch_order');
            }
            if (!$this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_status')) {
                $table->index(['workshop_branch_id', 'status'], 'idx_ws_disp_branch_status');
            }
        });

        Schema::table('bank_deposits', function (Blueprint $table) {
            if (!$this->indexExists('bank_deposits', 'idx_bank_deposits_branch_deleted')) {
                $table->index(['branch_id', 'deleted_at'], 'idx_bank_deposits_branch_deleted');
            }
            if (!$this->indexExists('bank_deposits', 'idx_bank_deposits_branch_deposited')) {
                $table->index(['branch_id', 'deposited_at'], 'idx_bank_deposits_branch_deposited');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'idx_orders_deleted_at')) {
                $table->dropIndex('idx_orders_deleted_at');
            }
            if ($this->indexExists('orders', 'idx_orders_branch_del_created')) {
                $table->dropIndex('idx_orders_branch_del_created');
            }
        });

        Schema::table('workshop_dispatches', function (Blueprint $table) {
            if ($this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_order')) {
                $table->dropIndex('idx_ws_disp_branch_order');
            }
            if ($this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_status')) {
                $table->dropIndex('idx_ws_disp_branch_status');
            }
        });

        Schema::table('bank_deposits', function (Blueprint $table) {
            if ($this->indexExists('bank_deposits', 'idx_bank_deposits_branch_deleted')) {
                $table->dropIndex('idx_bank_deposits_branch_deleted');
            }
            if ($this->indexExists('bank_deposits', 'idx_bank_deposits_branch_deposited')) {
                $table->dropIndex('idx_bank_deposits_branch_deposited');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
