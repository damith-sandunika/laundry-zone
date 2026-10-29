<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'super_admin', 'branch_admin', 'admin', 'staff', 'rider', 'workshop_admin') DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'branch_admin', 'admin', 'staff', 'rider', 'workshop_admin') DEFAULT 'staff'");
        }
    }
};
