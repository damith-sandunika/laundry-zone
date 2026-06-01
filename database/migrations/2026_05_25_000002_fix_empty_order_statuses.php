<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix orders that ended up with empty string status
        // (happened because 'done' wasn't in the old enum during previous migration)
        DB::statement("UPDATE orders SET status = 'received' WHERE status = '' OR status IS NULL");
    }

    public function down(): void
    {
        // Nothing to reverse
    }
};
