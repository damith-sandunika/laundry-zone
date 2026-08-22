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
            $table->string('receipt_path')->nullable()->after('payment_timing');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
    }
};
