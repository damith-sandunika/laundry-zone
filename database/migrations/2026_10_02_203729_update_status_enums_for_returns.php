<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received','processing','washing','drying','folding','done','dispatched_to_workshop','at_workshop','returned_from_workshop','out_for_delivery','delivered','cancelled','completed_at_workshop','returned_to_branch','edited', 'returned_from_delivery') COLLATE utf8mb3_unicode_ci DEFAULT 'received'");
        DB::statement("ALTER TABLE delivery_appointments MODIFY COLUMN status ENUM('pending','confirmed','rider_assigned','picked_up','at_branch','processing','ready','out_for_delivery','delivered','cancelled', 'returned_from_delivery', 'returned') COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Reverting not safe if data exists
    }
};
