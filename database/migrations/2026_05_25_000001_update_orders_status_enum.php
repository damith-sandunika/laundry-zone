<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Convert old statuses to new simplified ones
        DB::statement("UPDATE orders SET status = 'done' WHERE status IN ('washing', 'drying', 'ready')");

        // 2. Update the ENUM column to use new status values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received','done','delivered','cancelled') NOT NULL DEFAULT 'received'");
    }

    public function down(): void
    {
        // Restore old enum values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received','washing','drying','ready','delivered','cancelled') NOT NULL DEFAULT 'received'");
        // Convert 'done' back to 'ready'
        DB::statement("UPDATE orders SET status = 'ready' WHERE status = 'done'");
    }
};
