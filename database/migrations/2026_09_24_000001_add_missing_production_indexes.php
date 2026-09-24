<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Composite index for the most common query pattern:
            // WHERE branch_id = ? AND status IN (...) ORDER BY created_at DESC
            if (!$this->indexExists('orders', 'idx_orders_branch_status_created')) {
                $table->index(['branch_id', 'status', 'created_at'], 'idx_orders_branch_status_created');
            }

            // Index for delivery order filtering
            if (!$this->indexExists('orders', 'idx_orders_is_delivery')) {
                $table->index('is_delivery_order', 'idx_orders_is_delivery');
            }
        });

        Schema::table('delivery_appointments', function (Blueprint $table) {
            // Heavily queried by status in delivery workflows
            if (!$this->indexExists('delivery_appointments', 'idx_delivery_appts_status')) {
                $table->index('status', 'idx_delivery_appts_status');
            }

            // Order-based lookup
            if (!$this->indexExists('delivery_appointments', 'idx_delivery_appts_order')) {
                $table->index('order_id', 'idx_delivery_appts_order');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_orders_branch_status_created');
            $table->dropIndexIfExists('idx_orders_is_delivery');
        });

        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_delivery_appts_status');
            $table->dropIndexIfExists('idx_delivery_appts_order');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
