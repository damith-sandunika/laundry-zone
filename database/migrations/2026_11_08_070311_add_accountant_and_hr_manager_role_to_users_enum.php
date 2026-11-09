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
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'branch_admin', 'admin', 'staff', 'rider', 'workshop_admin', 'hr_manager', 'accountant', 'accountant_and_hr_manager') DEFAULT 'staff'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE users SET role = 'accountant' WHERE role = 'accountant_and_hr_manager'");
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'branch_admin', 'admin', 'staff', 'rider', 'workshop_admin', 'hr_manager', 'accountant') DEFAULT 'staff'");
        }
    }
};
