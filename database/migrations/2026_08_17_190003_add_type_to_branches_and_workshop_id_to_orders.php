<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->enum('type', ['branch', 'workshop'])->default('branch')->after('name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('workshop_id')->nullable()->constrained('branches')->nullOnDelete()->after('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['workshop_id']);
            $table->dropColumn('workshop_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
