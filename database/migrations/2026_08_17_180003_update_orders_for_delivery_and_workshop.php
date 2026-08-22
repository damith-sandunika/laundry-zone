<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update orders status enum to include workshop & delivery statuses
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'received',
            'processing',
            'washing',
            'drying',
            'folding',
            'done',
            'dispatched_to_workshop',
            'at_workshop',
            'returned_from_workshop',
            'out_for_delivery',
            'delivered',
            'cancelled'
        ) DEFAULT 'received'");

        // Add delivery-related columns to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_appointment_id')->nullable()->constrained('delivery_appointments')->onDelete('set null')->after('branch_id');
            $table->boolean('is_delivery_order')->default(false)->after('delivery_appointment_id');
            $table->string('delivery_address')->nullable()->after('is_delivery_order');
            $table->timestamp('picked_up_at')->nullable()->after('delivered_at');
            $table->timestamp('dispatched_to_workshop_at')->nullable()->after('picked_up_at');
            $table->timestamp('returned_from_workshop_at')->nullable()->after('dispatched_to_workshop_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_appointment_id']);
            $table->dropColumn(['delivery_appointment_id', 'is_delivery_order', 'delivery_address', 'picked_up_at', 'dispatched_to_workshop_at', 'returned_from_workshop_at']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'received','processing','washing','drying','folding','done','delivered','cancelled'
        ) DEFAULT 'received'");
    }
};
