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
            if (!$this->indexExists('orders', 'idx_orders_customer_created')) {
                $table->index(['customer_id', 'created_at'], 'idx_orders_customer_created');
            }
            if (!$this->indexExists('orders', 'idx_orders_payment_created')) {
                $table->index(['payment_status', 'created_at'], 'idx_orders_payment_created');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!$this->indexExists('order_items', 'idx_order_items_order_service')) {
                $table->index(['order_id', 'service_type_id'], 'idx_order_items_order_service');
            }
        });

        Schema::table('workshop_dispatches', function (Blueprint $table) {
            if (!$this->indexExists('workshop_dispatches', 'idx_ws_disp_order_status')) {
                $table->index(['order_id', 'status'], 'idx_ws_disp_order_status');
            }
            if (!$this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_status_simple')) {
                $table->index(['branch_id', 'status'], 'idx_ws_disp_branch_status_simple');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if ($this->indexExists('orders', 'idx_orders_customer_created')) {
                $table->dropIndex('idx_orders_customer_created');
            }
            if ($this->indexExists('orders', 'idx_orders_payment_created')) {
                $table->dropIndex('idx_orders_payment_created');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if ($this->indexExists('order_items', 'idx_order_items_order_service')) {
                $table->dropIndex('idx_order_items_order_service');
            }
        });

        Schema::table('workshop_dispatches', function (Blueprint $table) {
            if ($this->indexExists('workshop_dispatches', 'idx_ws_disp_order_status')) {
                $table->dropIndex('idx_ws_disp_order_status');
            }
            if ($this->indexExists('workshop_dispatches', 'idx_ws_disp_branch_status_simple')) {
                $table->dropIndex('idx_ws_disp_branch_status_simple');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
