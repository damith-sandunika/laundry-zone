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
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            if (DB::getDriverName() !== 'sqlite') { \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('received','processing','washing','drying','folding','done','dispatched_to_workshop','at_workshop','returned_from_workshop','out_for_delivery','delivered','cancelled','completed_at_workshop','returned_to_branch','edited') DEFAULT 'received'"); }
            if (DB::getDriverName() !== 'sqlite') { \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash','card','credit','bank','bank_transfer','pending') DEFAULT 'cash'"); }
        }
        
        \Illuminate\Support\Facades\DB::statement("UPDATE orders SET status = 'received' WHERE status = '' OR status IS NULL");
        \Illuminate\Support\Facades\DB::statement("UPDATE orders SET payment_method = 'pending' WHERE payment_method = '' OR payment_method IS NULL");
    }

    public function down(): void
    {
        // Too risky to down since we added values
    }
};


