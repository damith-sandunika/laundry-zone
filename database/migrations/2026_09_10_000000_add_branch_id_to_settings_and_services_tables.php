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
        if (!Schema::hasColumn('service_categories', 'branch_id')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('service_types', 'branch_id')) {
            Schema::table('service_types', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('packages', 'branch_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('pickup_time_periods', 'branch_id')) {
            Schema::table('pickup_time_periods', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('payment_methods', 'branch_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            });
        }

        $firstBranchId = DB::table('branches')->value('id') ?? 1;

        DB::table('service_categories')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
        DB::table('service_types')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
        DB::table('packages')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
        DB::table('pickup_time_periods')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
        DB::table('payment_methods')->whereNull('branch_id')->update(['branch_id' => $firstBranchId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('service_categories', 'branch_id')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('service_types', 'branch_id')) {
            Schema::table('service_types', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('packages', 'branch_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('pickup_time_periods', 'branch_id')) {
            Schema::table('pickup_time_periods', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }

        if (Schema::hasColumn('payment_methods', 'branch_id')) {
            Schema::table('payment_methods', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
