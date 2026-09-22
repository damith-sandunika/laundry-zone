<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['branch_id', 'status'], 'idx_branch_status');
            $table->index(['customer_id', 'created_at'], 'idx_customer_date');
            $table->index('status', 'idx_status');
            $table->index('payment_status', 'idx_payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_branch_status');
            $table->dropIndex('idx_customer_date');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_payment_status');
        });
    }
};
