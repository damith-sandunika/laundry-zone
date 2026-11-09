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
            $table->decimal('previous_amount', 10, 2)->nullable()->after('amount');
            $table->text('edit_reason')->nullable()->after('reason');
            $table->text('delete_reason')->nullable()->after('edit_reason');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('previous_amount', 10, 2)->nullable()->after('amount');
            $table->text('edit_reason')->nullable()->after('description');
            $table->text('delete_reason')->nullable()->after('edit_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropColumn(['previous_amount', 'edit_reason', 'delete_reason']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['previous_amount', 'edit_reason', 'delete_reason']);
        });
    }
};
