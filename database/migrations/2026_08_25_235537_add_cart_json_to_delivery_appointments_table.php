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
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->json('cart_json')->nullable()->after('item_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropColumn('cart_json');
        });
    }
};
