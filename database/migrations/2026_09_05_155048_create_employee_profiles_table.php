<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nic')->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('standard_working_hours', 8, 2)->default(8);
            $table->decimal('ot_rate_per_hour', 8, 2)->default(0);
            $table->integer('casual_leaves')->default(0);
            $table->timestamps();
        });

        // Data Migration Script: Move data from users table
        DB::statement('
            INSERT INTO employee_profiles (user_id, nic, dob, address, emergency_contact, basic_salary, standard_working_hours, ot_rate_per_hour, casual_leaves, created_at, updated_at)
            SELECT id, nic, dob, address, emergency_contact, basic_salary, standard_working_hours, ot_rate_per_hour, casual_leaves, NOW(), NOW()
            FROM users
            WHERE role != "customer"
        ');

        // Drop HR columns from Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nic', 'dob', 'address', 'emergency_contact',
                'basic_salary', 'standard_working_hours', 'ot_rate_per_hour', 'casual_leaves'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nic')->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0);
            $table->decimal('standard_working_hours', 8, 2)->default(8);
            $table->decimal('ot_rate_per_hour', 8, 2)->default(0);
            $table->integer('casual_leaves')->default(0);
        });

        Schema::dropIfExists('employee_profiles');
    }
};
