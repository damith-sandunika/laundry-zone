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
        Schema::table('salary_advances', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_advances', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('reason')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('salary_advances', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('salary_advances', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('salary_advances', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('expenses', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by', 'deleted_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['updated_by', 'deleted_by', 'deleted_at']);
        });
    }
};
