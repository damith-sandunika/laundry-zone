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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('standard_working_hours', 5, 2)->default(8.0)->after('basic_salary');
            $table->decimal('ot_rate_per_hour', 10, 2)->default(0)->after('standard_working_hours');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('ot_minutes')->default(0)->after('clock_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['standard_working_hours', 'ot_rate_per_hour']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('ot_minutes');
        });
    }
};
