<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE service_types MODIFY price_type ENUM('per_item', 'per_kg', 'per_sqft') DEFAULT 'per_item'");
        DB::statement("ALTER TABLE extras MODIFY price_type ENUM('per_piece', 'per_kg', 'per_sqft') DEFAULT 'per_piece'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE service_types MODIFY price_type ENUM('per_item', 'per_kg') DEFAULT 'per_item'");
        DB::statement("ALTER TABLE extras MODIFY price_type ENUM('per_piece', 'per_kg') DEFAULT 'per_piece'");
    }
};
