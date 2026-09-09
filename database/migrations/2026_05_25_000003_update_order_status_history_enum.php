<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE order_status_history SET status = 'done' WHERE status IN ('washing', 'drying', 'ready')");
        DB::statement("UPDATE order_status_history SET status = 'received' WHERE status = '' OR status IS NULL");
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE order_status_history MODIFY COLUMN status ENUM('received','done','delivered','cancelled') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE order_status_history MODIFY COLUMN status ENUM('received','washing','drying','ready','delivered','cancelled') NOT NULL");
        }
    }
};
