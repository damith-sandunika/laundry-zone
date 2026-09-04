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
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('order_sequence')->default(0)->after('is_delivery_enabled');
        });

        // Initialize sequence for existing branches to prevent overlapping with old numbers
        $maxId = \DB::table('orders')->max('id') ?? 0;
        \DB::table('branches')->update(['order_sequence' => $maxId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('order_sequence');
        });
    }
};
