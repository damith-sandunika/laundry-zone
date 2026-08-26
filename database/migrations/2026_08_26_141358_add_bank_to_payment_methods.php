<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash', 'card', 'credit', 'bank') NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_method ENUM('cash', 'card', 'bank') NOT NULL");
        DB::statement("ALTER TABLE package_payments MODIFY COLUMN payment_method ENUM('cash', 'card', 'bank') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash', 'card', 'credit') NULL DEFAULT 'cash'");
        DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_method ENUM('cash', 'card') NOT NULL");
        DB::statement("ALTER TABLE package_payments MODIFY COLUMN payment_method ENUM('cash', 'card') NOT NULL");
    }
};
