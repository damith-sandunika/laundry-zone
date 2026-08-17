<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('delivery_address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('map_address')->nullable()->after('longitude');
            $table->text('item_description')->nullable()->after('notes');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete()->after('branch_id');
            $table->timestamp('picked_up_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_appointments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['latitude', 'longitude', 'map_address', 'item_description', 'order_id', 'picked_up_at']);
        });
    }
};
