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
        Schema::table('branch_monthly_plans', function (Blueprint $table) {
            $table->string('default_in_time')->nullable()->after('month');
            $table->string('default_out_time')->nullable()->after('default_in_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_monthly_plans', function (Blueprint $table) {
            //
        });
    }
};
