<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role enum: add branch_admin, rider, workshop_admin
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'branch_admin', 'admin', 'staff', 'rider', 'workshop_admin') DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE users SET role = 'staff' WHERE role IN ('rider', 'workshop_admin', 'branch_admin')");
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'staff') DEFAULT 'staff'");
        }
    }
};
