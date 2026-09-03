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
        Schema::table('service_categories', function (Blueprint $table) {
            $table->integer('order_index')->default(0)->after('is_active');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->integer('order_index')->default(0)->after('is_active');
            $table->boolean('is_visible_on_website')->default(true)->after('order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('order_index');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['order_index', 'is_visible_on_website']);
        });
    }
};
