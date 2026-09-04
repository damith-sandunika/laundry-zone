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
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'turnaround_type_id')) {
                $table->foreignId('turnaround_type_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'turnaround_extra_charge')) {
                $table->decimal('turnaround_extra_charge', 10, 2)->default(0)->after('unit_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'turnaround_type_id')) {
                $table->dropForeign(['turnaround_type_id']);
                $table->dropColumn('turnaround_type_id');
            }
            if (Schema::hasColumn('order_items', 'turnaround_extra_charge')) {
                $table->dropColumn('turnaround_extra_charge');
            }
        });
    }
};
