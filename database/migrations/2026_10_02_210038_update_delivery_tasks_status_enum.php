<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE delivery_tasks MODIFY COLUMN status ENUM('assigned', 'accepted', 'on_way', 'arrived', 'completed', 'cancelled', 'returned', 'waiting_for_payment') COLLATE utf8mb3_unicode_ci DEFAULT 'assigned'");
        
        // Also update any empty strings back to 'returned' if they got corrupted
        DB::statement("UPDATE delivery_tasks SET status = 'returned' WHERE status = ''");
    }

    public function down(): void
    {
        // Reverting not safe
    }
};
