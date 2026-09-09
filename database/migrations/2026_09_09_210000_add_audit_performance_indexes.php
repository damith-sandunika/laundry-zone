<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing performance indexes identified in production audit.
     * All indexes are additive — no data is modified.
     */
    public function up(): void
    {
        $this->addIndexSafe('orders', 'workshop_id');
        $this->addIndexSafe('delivery_appointments', 'branch_id');
        $this->addIndexSafe('order_status_history', 'order_id');
        $this->addIndexSafe('order_status_history', 'created_at');
        $this->addIndexSafe('whatsapp_logs', 'order_id');
        $this->addIndexSafe('sms_logs', 'order_id');
        $this->addIndexSafe('email_logs', 'order_id');
    }

    private function addIndexSafe(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        } catch (\Exception $e) {
            // Index already exists — safe to skip
            \Log::info("Index on {$table}.{$column} already exists, skipping.");
        }
    }

    public function down(): void
    {
        $this->dropIndexSafe('orders', 'workshop_id');
        $this->dropIndexSafe('delivery_appointments', 'branch_id');
        $this->dropIndexSafe('order_status_history', 'order_id');
        $this->dropIndexSafe('order_status_history', 'created_at');
        $this->dropIndexSafe('whatsapp_logs', 'order_id');
        $this->dropIndexSafe('sms_logs', 'order_id');
        $this->dropIndexSafe('email_logs', 'order_id');
    }

    private function dropIndexSafe(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropIndex([$column]);
            });
        } catch (\Exception $e) {
            \Log::info("Could not drop index on {$table}.{$column}: " . $e->getMessage());
        }
    }
};
