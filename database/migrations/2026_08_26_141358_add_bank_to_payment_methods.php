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
        if (DB::getDriverName() !== 'sqlite') {
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash', 'card', 'credit', 'bank') NULL DEFAULT 'cash'"); }
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_method ENUM('cash', 'card', 'bank') NOT NULL"); }
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE package_payments MODIFY COLUMN payment_method ENUM('cash', 'card', 'bank') NOT NULL"); }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash', 'card', 'credit') NULL DEFAULT 'cash'"); }
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE payment_transactions MODIFY COLUMN payment_method ENUM('cash', 'card') NOT NULL"); }
            if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE package_payments MODIFY COLUMN payment_method ENUM('cash', 'card') NOT NULL"); }
        }
    }
};

