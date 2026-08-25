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
            $table->string('payment_method')->nullable()->after('status');
            $table->enum('payment_timing', ['pay_now', 'pay_on_delivery'])->nullable()->after('payment_method');
            $table->enum('delivery_preference', ['deliver_to_address', 'pickup_from_branch'])->nullable()->after('payment_timing');
            $table->decimal('delivery_lat', 10, 8)->nullable()->after('delivery_preference');
            $table->decimal('delivery_lng', 11, 8)->nullable()->after('delivery_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_timing', 'delivery_preference', 'delivery_lat', 'delivery_lng']);
        });
    }
};
